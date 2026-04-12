<?php

/**
 * Opportunity
 *
 * Data access layer for the opportunities table.
 */
class Opportunity
{
    private DB $db;

    /** Writable fields for create / update */
    private const FIELDS = [
        'opportunity_name', 'opportunity_type', 'lead_source',
        'account_id', 'contact_id', 'owner_id',
        'amount', 'probability', 'forecast_category', 'close_date',
        'stage', 'loss_reason',
        'budget_confirmed', 'decision_timeline',
        'stakeholders_identified', 'competitor',
        'plan_type', 'billing_term',
        'description',
        'bill_to_location_id',
    ];

    /** Columns that may be used for sorting */
    const SORTABLE = ['opportunity_name', 'stage', 'amount', 'close_date', 'forecast_category', 'probability'];

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
            "SELECT COUNT(*) AS total
               FROM opportunities o
               LEFT JOIN accounts a ON a.id = o.account_id
              {$where}",
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(int $limit = 20, int $offset = 0, string $search = '', string $sort = 'opportunity_name', string $dir = 'asc'): array
    {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'opportunity_name';
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        [$where, $params] = $this->buildSearch($search);

        return $this->db->query(
            "SELECT o.id, o.opportunity_name, o.stage, o.amount,
                    o.close_date, o.probability, o.forecast_category,
                    o.account_id, a.name AS account_name,
                    o.owner_id, o.created_at
               FROM opportunities o
               LEFT JOIN accounts a ON a.id = o.account_id
              {$where}
              ORDER BY o.{$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT o.id, o.opportunity_name, o.opportunity_type, o.lead_source,
                    o.account_id, o.contact_id, o.owner_id,
                    o.amount, o.probability, o.forecast_category, o.close_date,
                    o.stage, o.loss_reason,
                    o.budget_confirmed, o.decision_timeline,
                    o.stakeholders_identified, o.competitor,
                    o.plan_type, o.billing_term,
                    o.description,
                    o.bill_to_location_id,
                    o.created_at, o.updated_at,
                    a.name AS account_name,
                    CONCAT(c.first_name, ' ', c.last_name) AS contact_name,
                    bl.location_name AS bill_to_location_name,
                    bl.street_address_1 AS bill_to_street,
                    bl.city AS bill_to_city,
                    bl.state_province AS bill_to_state,
                    bl.zip_postal_code AS bill_to_zip
               FROM opportunities o
               LEFT JOIN accounts a  ON a.id  = o.account_id
               LEFT JOIN contacts c  ON c.id  = o.contact_id
               LEFT JOIN locations bl ON bl.id = o.bill_to_location_id
              WHERE o.id = ?
              LIMIT 1",
            [$id]
        );
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    public function create(array $data): array
    {
        $name = trim($data['opportunity_name'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'id' => null, 'error' => 'Opportunity name is required.'];
        }

        $values       = $this->buildValues($data);
        $cols         = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO opportunities ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        $name = trim($data['opportunity_name'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'error' => 'Opportunity name is required.'];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE opportunities SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function buildSearch(string $search): array
    {
        if ($search === '') {
            return ['', []];
        }
        $like  = '%' . $search . '%';
        $where = 'WHERE (o.opportunity_name LIKE ? OR o.stage LIKE ?
                      OR o.forecast_category LIKE ? OR a.name LIKE ?)';
        return [$where, [$like, $like, $like, $like]];
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
