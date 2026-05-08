<?php

class Project
{
    private DB $db;

    private const FIELDS = [
        'name', 'description', 'status', 'phase', 'priority',
        'owner_id', 'start_date', 'due_date', 'completed_date',
        'budget', 'notes',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Constants
    // =========================================================================

    const SORTABLE  = ['name', 'status', 'phase', 'priority', 'owner_id', 'start_date', 'due_date', 'created_at'];
    const STATUSES  = ['Draft', 'Active', 'On Hold', 'Completed', 'Cancelled'];
    const PHASES    = ['Initiation', 'Planning', 'Execution', 'Monitoring', 'Closure'];
    const PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];

    // =========================================================================
    // Queries
    // =========================================================================

    public function count(
        string $search   = '',
        string $status   = '',
        string $phase    = '',
        string $priority = ''
    ): int {
        [$where, $params] = $this->buildSearch($search, $status, $phase, $priority);
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM projects p {$where}", $params);
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(
        int    $limit    = 20,
        int    $offset   = 0,
        string $search   = '',
        string $sort     = 'created_at',
        string $dir      = 'desc',
        string $status   = '',
        string $phase    = '',
        string $priority = ''
    ): array {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
        $dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
        [$where, $params] = $this->buildSearch($search, $status, $phase, $priority);

        return $this->db->query(
            "SELECT p.id, p.name, p.status, p.phase, p.priority,
                    p.start_date, p.due_date, p.created_at,
                    u.name AS owner_name
               FROM projects p
               LEFT JOIN users u ON u.id = p.owner_id
              {$where}
              ORDER BY p.{$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT p.id, p.name, p.description, p.status, p.phase, p.priority,
                    p.owner_id, p.start_date, p.due_date, p.completed_date,
                    p.budget, p.notes, p.created_at, p.updated_at,
                    u.name AS owner_name
               FROM projects p
               LEFT JOIN users u ON u.id = p.owner_id
              WHERE p.id = ?
              LIMIT 1',
            [$id]
        ) ?: null;
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    public function create(array $data): array
    {
        $name = trim($data['name'] ?? '');
        if ($err = Validator::required($name, 'Name')) {
            return ['ok' => false, 'id' => null, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $cols   = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO projects ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        $name = trim($data['name'] ?? '');
        if ($err = Validator::required($name, 'Name')) {
            return ['ok' => false, 'error' => $err];
        }

        // Auto-set completed_date on transition to Completed
        $newStatus = $data['status'] ?? '';
        $existing  = $this->db->queryOne(
            'SELECT status, completed_date FROM projects WHERE id = ? LIMIT 1',
            [$id]
        );

        if ($existing) {
            $now = date('Y-m-d');
            if ($newStatus === 'Completed' && empty($existing['completed_date'])) {
                $data['completed_date'] = $now;
            }
            // Clear completed_date if re-opened
            if (in_array($newStatus, ['Draft', 'Active', 'On Hold'], true)) {
                $data['completed_date'] = null;
            }
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE projects SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function search(string $q): array
    {
        if ($q === '') return [];
        $like = '%' . $q . '%';
        $rows = $this->db->query(
            "SELECT id, name FROM projects
              WHERE name LIKE ? OR description LIKE ?
              ORDER BY created_at DESC LIMIT 10",
            [$like, $like]
        );
        return array_map(fn($r) => [
            'id'   => $r['id'],
            'name' => $r['name'],
        ], $rows);
    }

    public function getAssignableUsers(): array
    {
        return $this->db->query(
            'SELECT id, name FROM users WHERE is_active = 1 ORDER BY name ASC'
        );
    }

    // =========================================================================
    // Dashboard
    // =========================================================================

    public function getDashboardStats(): array
    {
        $totals = $this->db->queryOne(
            "SELECT
                SUM(status = 'Active')                        AS active,
                SUM(status = 'On Hold')                       AS on_hold,
                SUM(status NOT IN ('Completed','Cancelled'))  AS open_total,
                SUM(
                    status NOT IN ('Completed','Cancelled')
                    AND due_date IS NOT NULL
                    AND due_date < CURDATE()
                )                                             AS overdue
               FROM projects"
        );

        $byStatus = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM projects
              GROUP BY status
              ORDER BY FIELD(status,'Draft','Active','On Hold','Completed','Cancelled')"
        );

        $byPhase = $this->db->query(
            "SELECT phase, COUNT(*) AS cnt FROM projects
              WHERE status NOT IN ('Completed','Cancelled')
              GROUP BY phase
              ORDER BY FIELD(phase,'Initiation','Planning','Execution','Monitoring','Closure')"
        );

        $byPriority = $this->db->query(
            "SELECT priority, COUNT(*) AS cnt FROM projects
              WHERE status NOT IN ('Completed','Cancelled')
              GROUP BY priority
              ORDER BY FIELD(priority,'Critical','High','Medium','Low')"
        );

        return [
            'active'     => (int) ($totals['active']     ?? 0),
            'on_hold'    => (int) ($totals['on_hold']     ?? 0),
            'open_total' => (int) ($totals['open_total']  ?? 0),
            'overdue'    => (int) ($totals['overdue']     ?? 0),
            'by_status'  => $byStatus,
            'by_phase'   => $byPhase,
            'by_priority'=> $byPriority,
        ];
    }

    public function getMyProjects(int $userId, int $limit = 8): array
    {
        if ($userId < 1) return [];
        return $this->db->query(
            "SELECT id, name, status, phase, priority, due_date
               FROM projects
              WHERE owner_id = ? AND status NOT IN ('Completed','Cancelled')
              ORDER BY FIELD(priority,'Critical','High','Medium','Low'), due_date ASC
              LIMIT ?",
            [$userId, $limit]
        );
    }

    public function getOverdue(int $limit = 20): array
    {
        return $this->db->query(
            "SELECT p.id, p.name, p.status, p.phase, p.priority, p.due_date,
                    u.name AS owner_name
               FROM projects p
               LEFT JOIN users u ON u.id = p.owner_id
              WHERE p.status NOT IN ('Completed','Cancelled')
                AND p.due_date IS NOT NULL
                AND p.due_date < CURDATE()
              ORDER BY p.due_date ASC
              LIMIT ?",
            [$limit]
        );
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function buildSearch(
        string $search,
        string $status,
        string $phase,
        string $priority
    ): array {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $like         = '%' . $search . '%';
            $conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $params       = array_merge($params, [$like, $like]);
        }
        if ($status !== '') {
            $conditions[] = 'p.status = ?';
            $params[]     = $status;
        }
        if ($phase !== '') {
            $conditions[] = 'p.phase = ?';
            $params[]     = $phase;
        }
        if ($priority !== '') {
            $conditions[] = 'p.priority = ?';
            $params[]     = $priority;
        }

        $where = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }

    private function buildValues(array $data): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $raw = $data[$field];
            if ($raw === '') {
                $raw = null;
            }
            $values[$field] = $raw;
        }
        return $values;
    }
}
