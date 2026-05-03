<?php

class MigrationRunner
{
    private DB     $db;
    private string $platformVersion;

    public function __construct(DB $db)
    {
        $this->db              = $db;
        $this->platformVersion = getenv('PLATFORM_VERSION') ?: '0.0.0';
    }

    // =========================================================================
    // Setup
    // =========================================================================

    public function ensureTable(): void
    {
        // Create with full schema for fresh installs
        $this->db->runRaw("
            CREATE TABLE IF NOT EXISTS migrations (
                id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                module           VARCHAR(100) NOT NULL DEFAULT 'platform',
                platform_version VARCHAR(20)  NOT NULL DEFAULT '1.0.0',
                filename         VARCHAR(255) NOT NULL,
                applied_at       DATETIME     NOT NULL,
                applied_by       VARCHAR(100) NULL,
                UNIQUE KEY uq_module_filename (module, filename)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Upgrade path: add missing columns for existing installs that have the old schema
        $cols = array_column(
            $this->db->query("SHOW COLUMNS FROM migrations"),
            'Field'
        );

        if (!in_array('module', $cols, true)) {
            $this->db->runRaw("ALTER TABLE migrations ADD COLUMN module VARCHAR(100) NOT NULL DEFAULT 'platform' AFTER id");
        }
        if (!in_array('platform_version', $cols, true)) {
            $this->db->runRaw("ALTER TABLE migrations ADD COLUMN platform_version VARCHAR(20) NOT NULL DEFAULT '1.0.0' AFTER module");
        }

        // Upgrade unique key from filename-only to (module, filename)
        $keys = array_column(
            $this->db->query("SHOW INDEX FROM migrations WHERE Key_name = 'uq_module_filename'"),
            'Key_name'
        );
        if (empty($keys)) {
            // Drop old single-column unique index if present, add composite one
            try { $this->db->runRaw("ALTER TABLE migrations DROP INDEX filename"); } catch (Throwable) {}
            $this->db->runRaw("ALTER TABLE migrations ADD UNIQUE KEY uq_module_filename (module, filename)");
        }
    }

    // =========================================================================
    // Discovery
    // =========================================================================

    /**
     * Discovers all migration sources: platform + every module with SQL/InterimUpdates/.
     * Returns ['platform' => '/abs/path', 'crm' => '/abs/path', ...]
     * Keys are lowercase; display labels use ucfirst.
     */
    private function discoverSources(): array
    {
        $sources = [];

        $platformDir = dirname(__DIR__, 3) . '/sql/interimUpdates';
        if (is_dir($platformDir)) {
            $sources['platform'] = $platformDir;
        }

        $modulesDir = dirname(__DIR__, 2);
        foreach (scandir($modulesDir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $dir = $modulesDir . '/' . $entry . '/SQL/InterimUpdates';
            if (is_dir($dir)) {
                $sources[strtolower($entry)] = $dir;
            }
        }

        return $sources;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    /**
     * Returns all pending migrations grouped by module.
     * ['platform' => ['file.sql', ...], 'crm' => [...]]
     */
    public function getPendingAll(): array
    {
        $appliedByModule = [];
        foreach ($this->db->query('SELECT module, filename FROM migrations') as $row) {
            $appliedByModule[$row['module']][] = $row['filename'];
        }

        $pending = [];
        foreach ($this->discoverSources() as $module => $dir) {
            $files   = $this->scanDir($dir);
            $applied = $appliedByModule[$module] ?? [];
            $remain  = array_values(array_filter($files, fn($f) => !in_array($f, $applied, true)));
            if (!empty($remain)) {
                $pending[$module] = $remain;
            }
        }
        return $pending;
    }

    /**
     * Returns all applied migrations grouped by module, each entry newest-first.
     * ['platform' => [['module','platform_version','filename','applied_at','applied_by'], ...]]
     */
    public function getAppliedAll(): array
    {
        $rows = $this->db->query(
            'SELECT module, platform_version, filename, applied_at, applied_by
               FROM migrations
              ORDER BY applied_at DESC, filename DESC'
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['module']][] = $row;
        }
        return $grouped;
    }

    /** Total pending count across all modules. */
    public function pendingCount(): int
    {
        return array_sum(array_map('count', $this->getPendingAll()));
    }

    // =========================================================================
    // Actions
    // =========================================================================

    /** Run all pending migrations across all modules. */
    public function runAll(string $appliedBy = 'admin'): array
    {
        $results = [];
        foreach ($this->getPendingAll() as $module => $files) {
            foreach ($files as $filename) {
                $result    = $this->runFile($module, $filename, $appliedBy);
                $results[] = $result;
                if (!$result['ok']) {
                    return $results;
                }
            }
        }
        return $results;
    }

    /** Run all pending migrations for a single module. */
    public function runModule(string $module, string $appliedBy = 'admin'): array
    {
        $sources = $this->discoverSources();
        if (!isset($sources[$module])) {
            return [['module' => $module, 'file' => '', 'ok' => false, 'error' => 'Unknown module.']];
        }
        $results = [];
        foreach ($this->getPendingAll()[$module] ?? [] as $filename) {
            $result    = $this->runFile($module, $filename, $appliedBy);
            $results[] = $result;
            if (!$result['ok']) {
                return $results;
            }
        }
        return $results;
    }

    /** Run a single migration file for a specific module. */
    public function runFile(string $module, string $filename, string $appliedBy = 'admin'): array
    {
        $module   = strtolower($module);
        $filename = basename($filename);

        $sources = $this->discoverSources();
        if (!isset($sources[$module])) {
            return $this->result($module, $filename, false, 'Unknown module.');
        }

        if (!preg_match('/^\d{8}_[\w\-]+\.sql$/', $filename)) {
            return $this->result($module, $filename, false, 'Invalid filename format.');
        }

        $path = $sources[$module] . '/' . $filename;
        if (!file_exists($path)) {
            return $this->result($module, $filename, false, 'File not found.');
        }

        $sql        = file_get_contents($path);
        $statements = $this->splitStatements($sql);

        if (empty($statements)) {
            $this->record($module, $filename, $appliedBy);
            return $this->result($module, $filename, true);
        }

        try {
            foreach ($statements as $stmt) {
                $this->db->runRaw($stmt);
            }
            $this->record($module, $filename, $appliedBy);
            return $this->result($module, $filename, true);
        } catch (Throwable $e) {
            Logger::getInstance()->error('Migration failed', [
                'module'  => $module,
                'file'    => $filename,
                'message' => $e->getMessage(),
            ]);
            return $this->result($module, $filename, false, $e->getMessage());
        }
    }

    /** Mark a file as applied without executing its SQL. */
    public function markApplied(string $module, string $filename, string $appliedBy = 'manual'): bool
    {
        $module   = strtolower($module);
        $filename = basename($filename);

        $sources = $this->discoverSources();
        if (!isset($sources[$module])) {
            return false;
        }
        if (!preg_match('/^\d{8}_[\w\-]+\.sql$/', $filename)) {
            return false;
        }
        $pending = $this->getPendingAll()[$module] ?? [];
        if (!in_array($filename, $pending, true)) {
            return false;
        }
        $this->record($module, $filename, $appliedBy);
        return true;
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function scanDir(string $dir): array
    {
        $files = [];
        foreach (scandir($dir) as $file) {
            if (preg_match('/^\d{8}_[\w\-]+\.sql$/', $file)) {
                $files[] = $file;
            }
        }
        sort($files);
        return $files;
    }

    private function record(string $module, string $filename, string $appliedBy): void
    {
        $this->db->execute(
            'INSERT IGNORE INTO migrations (module, platform_version, filename, applied_at, applied_by)
             VALUES (?, ?, ?, ?, ?)',
            [$module, $this->platformVersion, $filename, date('Y-m-d H:i:s'), $appliedBy]
        );
    }

    private function splitStatements(string $sql): array
    {
        $sql = preg_replace('/--[^\n]*/', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        $statements = [];
        foreach (explode(';', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || stripos($stmt, 'DELIMITER') === 0) {
                continue;
            }
            $statements[] = $stmt;
        }
        return $statements;
    }

    private function result(string $module, string $file, bool $ok, ?string $error = null): array
    {
        return ['module' => $module, 'file' => $file, 'ok' => $ok, 'error' => $error];
    }
}
