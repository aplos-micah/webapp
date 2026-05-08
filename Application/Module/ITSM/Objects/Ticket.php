<?php

/**
 * Ticket
 *
 * Data access layer for the itsm_tickets table.
 * Follows ITIL principles: incidents, service requests, problems, and changes.
 */
class Ticket
{
    private DB $db;

    private const FIELDS = [
        'title', 'description', 'type', 'priority', 'status', 'category',
        'assigned_to', 'reported_by_name', 'reported_by_email',
        'owner_id', 'resolution', 'resolved_at', 'closed_at',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Constants
    // =========================================================================

    const SORTABLE   = ['ticket_number', 'title', 'status', 'priority', 'type', 'created_at', 'updated_at'];
    const TYPES      = ['Incident', 'Service Request', 'Problem', 'Change'];
    const PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];
    const STATUSES   = ['New', 'In Progress', 'Pending', 'Resolved', 'Closed'];
    const CATEGORIES = ['Hardware', 'Software', 'Network', 'Access', 'Email', 'Security', 'Other'];

    // =========================================================================
    // Queries
    // =========================================================================

    public function count(string $search = '', string $status = '', string $priority = '', string $type = ''): int
    {
        [$where, $params] = $this->buildSearch($search, $status, $priority, $type);
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM itsm_tickets t {$where}", $params);
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(
        int    $limit    = 20,
        int    $offset   = 0,
        string $search   = '',
        string $sort     = 'created_at',
        string $dir      = 'desc',
        string $status   = '',
        string $priority = '',
        string $type     = ''
    ): array {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
        $dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
        [$where, $params] = $this->buildSearch($search, $status, $priority, $type);

        return $this->db->query(
            "SELECT t.id, t.ticket_number, t.title, t.type, t.priority, t.status,
                    t.category, t.assigned_to, t.created_at, t.updated_at,
                    u.name AS assigned_name
               FROM itsm_tickets t
               LEFT JOIN users u ON u.id = t.assigned_to
              {$where}
              ORDER BY t.{$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT t.id, t.ticket_number, t.title, t.description, t.type, t.priority,
                    t.status, t.category, t.assigned_to, t.reported_by_name,
                    t.reported_by_email, t.owner_id, t.resolution,
                    t.resolved_at, t.closed_at, t.created_at, t.updated_at,
                    u.name AS assigned_name,
                    o.name AS owner_name
               FROM itsm_tickets t
               LEFT JOIN users u ON u.id = t.assigned_to
               LEFT JOIN users o ON o.id = t.owner_id
              WHERE t.id = ?
              LIMIT 1',
            [$id]
        ) ?: null;
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    public function create(array $data): array
    {
        $title = trim($data['title'] ?? '');
        if ($err = Validator::required($title, 'Title')) {
            return ['ok' => false, 'id' => null, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $cols   = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO itsm_tickets ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        // Assign ticket number after insert so we can use the auto-increment ID
        $this->db->execute(
            "UPDATE itsm_tickets SET ticket_number = CONCAT('TKT-', LPAD(id, 6, '0')) WHERE id = ?",
            [(int) $id]
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        $title = trim($data['title'] ?? '');
        if ($err = Validator::required($title, 'Title')) {
            return ['ok' => false, 'error' => $err];
        }

        // Auto-set resolved_at and closed_at on status transitions
        $newStatus = $data['status'] ?? '';
        $existing  = $this->db->queryOne(
            'SELECT status, resolved_at, closed_at FROM itsm_tickets WHERE id = ? LIMIT 1',
            [$id]
        );

        if ($existing) {
            $now = date('Y-m-d H:i:s');
            if ($newStatus === 'Resolved' && $existing['resolved_at'] === null) {
                $data['resolved_at'] = $now;
            }
            if ($newStatus === 'Closed' && $existing['closed_at'] === null) {
                $data['closed_at'] = $now;
            }
            // Clear resolved/closed if re-opened
            if (in_array($newStatus, ['New', 'In Progress', 'Pending'], true)) {
                $data['resolved_at'] = null;
                $data['closed_at']   = null;
            }
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE itsm_tickets SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /** Typeahead: search by title or ticket number. */
    public function search(string $q): array
    {
        if ($q === '') return [];
        $like = '%' . $q . '%';
        $rows = $this->db->query(
            "SELECT id, ticket_number, title FROM itsm_tickets
              WHERE title LIKE ? OR ticket_number LIKE ?
              ORDER BY created_at DESC LIMIT 10",
            [$like, $like]
        );
        return array_map(fn($r) => [
            'id'   => $r['id'],
            'name' => $r['ticket_number'] . ': ' . $r['title'],
        ], $rows);
    }

    /** Return active users available for ticket assignment. */
    public function getAssignableUsers(): array
    {
        return $this->db->query(
            'SELECT id, name FROM users WHERE is_active = 1 ORDER BY name ASC'
        );
    }

    // =========================================================================
    // Dashboard
    // =========================================================================

    /**
     * Return all stat counts and breakdowns needed for the dashboard.
     * Single-trip summary for the top stat cards and three breakdown panels.
     */
    public function getDashboardStats(): array
    {
        $open = $this->db->queryOne(
            "SELECT COUNT(*) AS total,
                    SUM(priority IN ('Critical','High')) AS critical_high,
                    SUM(assigned_to IS NULL)             AS unassigned
               FROM itsm_tickets WHERE status NOT IN ('Resolved','Closed')"
        );

        $resolvedToday = (int) ($this->db->queryOne(
            "SELECT COUNT(*) AS cnt FROM itsm_tickets
              WHERE status = 'Resolved' AND DATE(resolved_at) = CURDATE()"
        )['cnt'] ?? 0);

        $byStatus = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM itsm_tickets
              WHERE status NOT IN ('Resolved','Closed')
              GROUP BY status
              ORDER BY FIELD(status,'New','In Progress','Pending')"
        );

        $byPriority = $this->db->query(
            "SELECT priority, COUNT(*) AS cnt FROM itsm_tickets
              WHERE status NOT IN ('Resolved','Closed')
              GROUP BY priority
              ORDER BY FIELD(priority,'Critical','High','Medium','Low')"
        );

        $byType = $this->db->query(
            "SELECT type, COUNT(*) AS cnt FROM itsm_tickets
              WHERE status NOT IN ('Resolved','Closed')
              GROUP BY type
              ORDER BY FIELD(type,'Incident','Service Request','Problem','Change')"
        );

        return [
            'open_total'     => (int) ($open['total']         ?? 0),
            'critical_high'  => (int) ($open['critical_high'] ?? 0),
            'unassigned'     => (int) ($open['unassigned']    ?? 0),
            'resolved_today' => $resolvedToday,
            'by_status'      => $byStatus,
            'by_priority'    => $byPriority,
            'by_type'        => $byType,
        ];
    }

    /** Open tickets assigned to a specific user, sorted by priority then age. */
    public function getMyTickets(int $userId, int $limit = 8): array
    {
        if ($userId < 1) return [];
        return $this->db->query(
            "SELECT id, ticket_number, title, priority, status, created_at
               FROM itsm_tickets
              WHERE assigned_to = ? AND status NOT IN ('Resolved','Closed')
              ORDER BY FIELD(priority,'Critical','High','Medium','Low'), created_at ASC
              LIMIT ?",
            [$userId, $limit]
        );
    }

    /** Most recently created or updated tickets across all users. */
    public function getRecentActivity(int $limit = 8): array
    {
        return $this->db->query(
            "SELECT t.id, t.ticket_number, t.title, t.priority, t.status, t.updated_at,
                    u.name AS assigned_name
               FROM itsm_tickets t
               LEFT JOIN users u ON u.id = t.assigned_to
              ORDER BY t.updated_at DESC
              LIMIT ?",
            [$limit]
        );
    }

    /** Open tickets with no assignee, sorted by priority then age. */
    public function getUnassigned(int $limit = 20): array
    {
        return $this->db->query(
            "SELECT id, ticket_number, title, type, priority, status, created_at
               FROM itsm_tickets
              WHERE assigned_to IS NULL AND status NOT IN ('Resolved','Closed')
              ORDER BY FIELD(priority,'Critical','High','Medium','Low'), created_at ASC
              LIMIT ?",
            [$limit]
        );
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function buildSearch(string $search, string $status, string $priority, string $type): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $like         = '%' . $search . '%';
            $conditions[] = '(t.title LIKE ? OR t.ticket_number LIKE ? OR t.description LIKE ?)';
            $params       = array_merge($params, [$like, $like, $like]);
        }
        if ($status !== '') {
            $conditions[] = 't.status = ?';
            $params[]     = $status;
        }
        if ($priority !== '') {
            $conditions[] = 't.priority = ?';
            $params[]     = $priority;
        }
        if ($type !== '') {
            $conditions[] = 't.type = ?';
            $params[]     = $type;
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
