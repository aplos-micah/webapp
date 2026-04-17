<?php

class Runner
{
    private DB     $db;
    private string $migrationsDir;

    public function __construct(DB $db)
    {
        $this->db            = $db;
        $this->migrationsDir = __DIR__ . '/../sql/interimUpdates';
    }

    // -------------------------------------------------------------------------
    // Setup
    // -------------------------------------------------------------------------

    public function ensureTable(): void
    {
        $this->db->runRaw("
            CREATE TABLE IF NOT EXISTS migrations (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                filename   VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME     NOT NULL,
                applied_by VARCHAR(100) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function getApplied(): array
    {
        return $this->db->query(
            'SELECT filename, applied_at, applied_by FROM migrations ORDER BY filename ASC'
        );
    }

    public function getPending(): array
    {
        $applied  = array_column($this->getApplied(), 'filename');
        $all      = $this->scanFiles();
        return array_values(array_filter($all, fn($f) => !in_array($f, $applied, true)));
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function runAll(string $appliedBy = 'CLI'): array
    {
        $results = [];
        foreach ($this->getPending() as $filename) {
            $results[] = $this->runFile($filename, $appliedBy);
            if (!$results[array_key_last($results)]['ok']) {
                break;
            }
        }
        return $results;
    }

    public function runFile(string $filename, string $appliedBy = 'CLI'): array
    {
        $filename = basename($filename);

        if (!preg_match('/^\d{8}_[\w\-]+\.sql$/', $filename)) {
            return $this->result($filename, false, 'Invalid filename format.');
        }

        $path = $this->migrationsDir . '/' . $filename;
        if (!file_exists($path)) {
            return $this->result($filename, false, 'File not found.');
        }

        $sql        = file_get_contents($path);
        $statements = $this->splitStatements($sql);

        if (empty($statements)) {
            $this->record($filename, $appliedBy);
            return $this->result($filename, true);
        }

        try {
            foreach ($statements as $stmt) {
                $this->db->runRaw($stmt);
            }
            $this->record($filename, $appliedBy);
            return $this->result($filename, true);
        } catch (Throwable $e) {
            Logger::getInstance()->error('Migration failed', [
                'file'    => $filename,
                'message' => $e->getMessage(),
            ]);
            return $this->result($filename, false, $e->getMessage());
        }
    }

    public function markApplied(string $filename, string $appliedBy = 'manual'): bool
    {
        $filename = basename($filename);
        if (!preg_match('/^\d{8}_[\w\-]+\.sql$/', $filename)) {
            return false;
        }
        $pending = $this->getPending();
        if (!in_array($filename, $pending, true)) {
            return false;
        }
        $this->record($filename, $appliedBy);
        return true;
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function scanFiles(): array
    {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }
        $files = [];
        foreach (scandir($this->migrationsDir) as $file) {
            if (preg_match('/^\d{8}_[\w\-]+\.sql$/', $file)) {
                $files[] = $file;
            }
        }
        sort($files);
        return $files;
    }

    private function record(string $filename, string $appliedBy): void
    {
        $this->db->execute(
            'INSERT IGNORE INTO migrations (filename, applied_at, applied_by) VALUES (?, ?, ?)',
            [$filename, date('Y-m-d H:i:s'), $appliedBy]
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

    private function result(string $file, bool $ok, ?string $error = null): array
    {
        return ['file' => $file, 'ok' => $ok, 'error' => $error];
    }
}
