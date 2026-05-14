<?php

class ActivityType
{
    private DB $db;

    private const FIELDS = ['name', 'description', 'average_cost', 'is_active'];

    const SORTABLE = ['name', 'average_cost', 'is_active'];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    public function count(string $search = ''): int
    {
        [$where, $params] = $this->buildSearch($search);
        $row = $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM crm_activity_types {$where}",
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(
        int    $limit  = 100,
        int    $offset = 0,
        string $search = '',
        string $sort   = 'name',
        string $dir    = 'asc',
        bool   $activeOnly = false
    ): array {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'name';
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        [$where, $params] = $this->buildSearch($search);

        if ($activeOnly) {
            $where  = $where ? "{$where} AND is_active = 1" : 'WHERE is_active = 1';
        }

        return $this->db->query(
            "SELECT id, name, description, average_cost, is_active, created_at
               FROM crm_activity_types
              {$where}
              ORDER BY {$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, description, average_cost, is_active, created_at, updated_at
               FROM crm_activity_types
              WHERE id = ?
              LIMIT 1',
            [$id]
        );
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
        $phs    = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO crm_activity_types ({$cols}) VALUES ({$phs})",
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

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE crm_activity_types SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    public function toggleActive(int $id): void
    {
        $this->db->execute(
            'UPDATE crm_activity_types SET is_active = 1 - is_active WHERE id = ?',
            [$id]
        );
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function buildSearch(string $search): array
    {
        if ($search === '') {
            return ['', []];
        }
        return ['WHERE name LIKE ?', ['%' . $search . '%']];
    }

    private function buildValues(array $data): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $raw = $data[$field];
            $values[$field] = ($raw === '') ? null : $raw;
        }
        return $values;
    }
}
