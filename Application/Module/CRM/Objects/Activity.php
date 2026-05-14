<?php

class Activity
{
    private DB $db;

    private const FIELDS = [
        'activity_type_id', 'account_id', 'contact_id', 'opportunity_id',
        'activity_date', 'duration_minutes', 'outcome', 'notes', 'cost', 'owner_id',
    ];

    const SORTABLE = ['activity_date', 'cost', 'outcome'];

    const OUTCOMES = [
        'Positive', 'Neutral', 'Negative',
        'Completed', 'No Response', 'Follow-up Required', 'Cancelled',
    ];

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
            "SELECT COUNT(*) AS total FROM crm_activities a {$where}",
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(
        int    $limit   = 20,
        int    $offset  = 0,
        string $search  = '',
        string $sort    = 'activity_date',
        string $dir     = 'desc',
        array  $filters = []
    ): array {
        $col = in_array($sort, self::SORTABLE, true) ? "a.{$sort}" : 'a.activity_date';
        $dir = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';
        [$where, $params] = $this->buildSearch($search);

        // Entity ID filters (account_id, contact_id, opportunity_id)
        $entityClauses = [];
        $entityParams  = [];
        foreach (['account_id', 'contact_id', 'opportunity_id'] as $f) {
            if (!empty($filters[$f])) {
                $entityClauses[] = "a.{$f} = ?";
                $entityParams[]  = (int) $filters[$f];
            }
        }
        if ($entityClauses) {
            $entitySql = implode(' AND ', $entityClauses);
            $where     = $where ? "{$where} AND {$entitySql}" : "WHERE {$entitySql}";
            $params    = array_merge($params, $entityParams);
        }

        return $this->db->query(
            "SELECT a.id, a.activity_date, a.outcome, a.cost, a.duration_minutes,
                    a.account_id, a.contact_id, a.opportunity_id,
                    t.name AS type_name,
                    acc.name AS account_name,
                    CONCAT(c.first_name, ' ', c.last_name) AS contact_name,
                    opp.opportunity_name,
                    u.name AS owner_name
               FROM crm_activities a
               JOIN crm_activity_types t  ON t.id = a.activity_type_id
               LEFT JOIN accounts    acc  ON acc.id = a.account_id
               LEFT JOIN contacts    c    ON c.id   = a.contact_id
               LEFT JOIN opportunities opp ON opp.id = a.opportunity_id
               LEFT JOIN users        u   ON u.id   = a.owner_id
              {$where}
              ORDER BY {$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT a.id, a.activity_type_id, a.account_id, a.contact_id, a.opportunity_id,
                    a.activity_date, a.duration_minutes, a.outcome, a.notes, a.cost,
                    a.owner_id, a.created_at, a.updated_at,
                    t.name AS type_name, t.average_cost AS type_avg_cost,
                    acc.name AS account_name,
                    CONCAT(c.first_name, ' ', c.last_name) AS contact_name,
                    opp.opportunity_name,
                    u.name AS owner_name
               FROM crm_activities a
               JOIN crm_activity_types t  ON t.id = a.activity_type_id
               LEFT JOIN accounts    acc  ON acc.id = a.account_id
               LEFT JOIN contacts    c    ON c.id   = a.contact_id
               LEFT JOIN opportunities opp ON opp.id = a.opportunity_id
               LEFT JOIN users        u   ON u.id   = a.owner_id
              WHERE a.id = ?
              LIMIT 1",
            [$id]
        );
    }

    public function findByEntity(string $field, int $id, int $limit = 5): array
    {
        $allowed = ['account_id', 'contact_id', 'opportunity_id'];
        if (!in_array($field, $allowed, true)) {
            return [];
        }
        return $this->db->query(
            "SELECT a.id, a.activity_date, a.outcome, a.cost, a.duration_minutes,
                    t.name AS type_name, u.name AS owner_name
               FROM crm_activities a
               JOIN crm_activity_types t ON t.id = a.activity_type_id
               LEFT JOIN users          u ON u.id = a.owner_id
              WHERE a.{$field} = ?
              ORDER BY a.activity_date DESC
              LIMIT ?",
            [$id, $limit]
        );
    }

    // =========================================================================
    // Dashboard aggregates
    // =========================================================================

    public function countByWeekThisQuarter(): array
    {
        [$start, $end] = $this->currentQuarterRange();

        $rows = $this->db->query(
            "SELECT YEARWEEK(activity_date, 1) AS yw,
                    MIN(activity_date)          AS week_start,
                    COUNT(*)                    AS cnt
               FROM crm_activities
              WHERE activity_date BETWEEN ? AND ?
              GROUP BY YEARWEEK(activity_date, 1)
              ORDER BY yw ASC",
            [$start, $end]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'week_label' => date('M j', strtotime($row['week_start'])),
                'count'      => (int) $row['cnt'],
            ];
        }
        return $result;
    }

    public function averageCostThisQuarter(): float
    {
        [$start, $end] = $this->currentQuarterRange();
        $row = $this->db->queryOne(
            'SELECT AVG(cost) AS avg_cost
               FROM crm_activities
              WHERE activity_date BETWEEN ? AND ?
                AND cost IS NOT NULL',
            [$start, $end]
        );
        return round((float) ($row['avg_cost'] ?? 0), 2);
    }

    public function totalThisQuarter(): array
    {
        [$start, $end] = $this->currentQuarterRange();
        $row = $this->db->queryOne(
            'SELECT COUNT(*) AS cnt, COALESCE(SUM(cost), 0) AS total_cost
               FROM crm_activities
              WHERE activity_date BETWEEN ? AND ?',
            [$start, $end]
        );
        return [
            'count'      => (int) ($row['cnt']        ?? 0),
            'total_cost' => (float) ($row['total_cost'] ?? 0),
        ];
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    public function create(array $data): array
    {
        if (empty($data['account_id']) && empty($data['contact_id']) && empty($data['opportunity_id'])) {
            return ['ok' => false, 'id' => null, 'error' => 'At least one of Account, Contact, or Opportunity is required.'];
        }
        if (empty($data['activity_type_id'])) {
            return ['ok' => false, 'id' => null, 'error' => 'Activity type is required.'];
        }
        if (empty($data['activity_date'])) {
            return ['ok' => false, 'id' => null, 'error' => 'Activity date is required.'];
        }

        $values = $this->buildValues($data);
        $cols   = implode(', ', array_keys($values));
        $phs    = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO crm_activities ({$cols}) VALUES ({$phs})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        if (empty($data['account_id']) && empty($data['contact_id']) && empty($data['opportunity_id'])) {
            return ['ok' => false, 'error' => 'At least one of Account, Contact, or Opportunity is required.'];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE crm_activities SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM crm_activities WHERE id = ?', [$id]);
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function currentQuarterRange(): array
    {
        $month   = (int) date('n');
        $year    = (int) date('Y');
        $quarter = (int) ceil($month / 3);
        $startMonth = (($quarter - 1) * 3) + 1;
        $endMonth   = $startMonth + 2;
        $start = sprintf('%04d-%02d-01', $year, $startMonth);
        $end   = date('Y-m-t', mktime(0, 0, 0, $endMonth, 1, $year));
        return [$start, $end];
    }

    private function buildSearch(string $search): array
    {
        if ($search === '') {
            return ['', []];
        }
        $like = '%' . $search . '%';
        return [
            'WHERE (t.name LIKE ? OR a.notes LIKE ? OR a.outcome LIKE ?)',
            [$like, $like, $like],
        ];
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
