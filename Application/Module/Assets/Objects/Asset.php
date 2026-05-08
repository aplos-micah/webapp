<?php

class Asset
{
    private DB $db;

    private const FIELDS = [
        'name', 'type', 'category', 'status', 'location',
        'assigned_to', 'manufacturer', 'model', 'serial_number',
        'purchase_date', 'warranty_expires', 'cost', 'notes', 'owner_id',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Constants
    // =========================================================================

    const SORTABLE   = ['asset_tag', 'name', 'type', 'status', 'assigned_to', 'warranty_expires', 'created_at'];
    const TYPES      = ['Hardware', 'Software', 'Network', 'Mobile', 'License', 'Other'];
    const STATUSES   = ['Active', 'In Stock', 'In Repair', 'Retired', 'Lost/Stolen'];
    const CATEGORIES = [
        'Server', 'Desktop', 'Laptop', 'Mobile Device', 'Network Device',
        'Printer', 'Monitor', 'Software License', 'Other',
    ];

    // =========================================================================
    // Queries
    // =========================================================================

    public function count(string $search = '', string $status = '', string $type = ''): int
    {
        [$where, $params] = $this->buildSearch($search, $status, $type);
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM assets a {$where}", $params);
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(
        int    $limit  = 20,
        int    $offset = 0,
        string $search = '',
        string $sort   = 'created_at',
        string $dir    = 'desc',
        string $status = '',
        string $type   = ''
    ): array {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'created_at';
        $dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
        [$where, $params] = $this->buildSearch($search, $status, $type);

        return $this->db->query(
            "SELECT a.id, a.asset_tag, a.name, a.type, a.status,
                    a.location, a.warranty_expires, a.created_at,
                    u.name AS assigned_name
               FROM assets a
               LEFT JOIN users u ON u.id = a.assigned_to
              {$where}
              ORDER BY a.{$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT a.id, a.asset_tag, a.name, a.type, a.category, a.status,
                    a.location, a.assigned_to, a.manufacturer, a.model,
                    a.serial_number, a.purchase_date, a.warranty_expires,
                    a.cost, a.notes, a.owner_id, a.created_at, a.updated_at,
                    u.name  AS assigned_name,
                    o.name  AS owner_name
               FROM assets a
               LEFT JOIN users u ON u.id = a.assigned_to
               LEFT JOIN users o ON o.id = a.owner_id
              WHERE a.id = ?
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
            "INSERT INTO assets ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        // Assign asset tag after insert using the auto-increment ID
        $this->db->execute(
            "UPDATE assets SET asset_tag = CONCAT('ASSET-', LPAD(id, 6, '0')) WHERE id = ?",
            [(int) $id]
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        $name = trim($data['name'] ?? '');
        if ($err = Validator::required($name, 'Name')) {
            return ['ok' => false, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE assets SET {$set} WHERE id = ?",
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
            "SELECT id, asset_tag, name FROM assets
              WHERE name LIKE ? OR asset_tag LIKE ? OR serial_number LIKE ?
              ORDER BY created_at DESC LIMIT 10",
            [$like, $like, $like]
        );
        return array_map(fn($r) => [
            'id'   => $r['id'],
            'name' => $r['asset_tag'] . ': ' . $r['name'],
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
                SUM(status = 'Active')                    AS active,
                SUM(status = 'In Repair')                 AS in_repair,
                SUM(assigned_to IS NULL AND status NOT IN ('Retired','Lost/Stolen')) AS unassigned,
                SUM(warranty_expires IS NOT NULL
                    AND warranty_expires <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    AND warranty_expires >= CURDATE()
                    AND status NOT IN ('Retired','Lost/Stolen'))  AS expiring_soon
               FROM assets"
        );

        $byStatus = $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM assets
              GROUP BY status
              ORDER BY FIELD(status,'Active','In Stock','In Repair','Retired','Lost/Stolen')"
        );

        $byType = $this->db->query(
            "SELECT type, COUNT(*) AS cnt FROM assets
              GROUP BY type
              ORDER BY FIELD(type,'Hardware','Software','Network','Mobile','License','Other')"
        );

        return [
            'active'        => (int) ($totals['active']        ?? 0),
            'in_repair'     => (int) ($totals['in_repair']      ?? 0),
            'unassigned'    => (int) ($totals['unassigned']     ?? 0),
            'expiring_soon' => (int) ($totals['expiring_soon']  ?? 0),
            'by_status'     => $byStatus,
            'by_type'       => $byType,
        ];
    }

    public function getExpiringWarranties(int $daysAhead = 30, int $limit = 20): array
    {
        return $this->db->query(
            "SELECT a.id, a.asset_tag, a.name, a.type, a.warranty_expires,
                    u.name AS assigned_name
               FROM assets a
               LEFT JOIN users u ON u.id = a.assigned_to
              WHERE a.warranty_expires IS NOT NULL
                AND a.warranty_expires <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND a.status NOT IN ('Retired','Lost/Stolen')
              ORDER BY a.warranty_expires ASC
              LIMIT ?",
            [$daysAhead, $limit]
        );
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function buildSearch(string $search, string $status, string $type): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $like         = '%' . $search . '%';
            $conditions[] = '(a.name LIKE ? OR a.asset_tag LIKE ? OR a.serial_number LIKE ? OR a.model LIKE ?)';
            $params       = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($status !== '') {
            $conditions[] = 'a.status = ?';
            $params[]     = $status;
        }
        if ($type !== '') {
            $conditions[] = 'a.type = ?';
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
