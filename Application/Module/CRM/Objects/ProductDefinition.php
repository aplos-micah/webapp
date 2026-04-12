<?php

/**
 * ProductDefinition
 *
 * Data access layer for the product_definitions table.
 */
class ProductDefinition
{
    private DB $db;

    /** Writable fields for create / update */
    private const FIELDS = [
        'product_name', 'sku', 'product_description', 'product_family',
        'product_type', 'is_active', 'lifecycle_status',
        'list_price', 'currency', 'unit_cost', 'unit_of_measure',
        'pricing_model', 'tax_category',
        'subscription_term_months', 'weight', 'dimensions', 'material',
        'usage_metrics', 'competitive_notes', 'owner_id',
    ];

    /** Columns that may be used for sorting */
    const SORTABLE = ['product_name', 'sku', 'product_family', 'product_type', 'list_price', 'lifecycle_status'];

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
            "SELECT COUNT(*) AS total FROM product_definitions {$where}",
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(int $limit = 20, int $offset = 0, string $search = '', string $sort = 'product_name', string $dir = 'asc'): array
    {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'product_name';
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        [$where, $params] = $this->buildSearch($search);

        return $this->db->query(
            "SELECT id, product_name, sku, product_family, product_type,
                    list_price, currency, lifecycle_status, is_active, created_at
               FROM product_definitions
              {$where}
              ORDER BY {$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, product_name, sku, product_description, product_family,
                    product_type, is_active, lifecycle_status,
                    list_price, currency, unit_cost, unit_of_measure,
                    pricing_model, tax_category,
                    subscription_term_months, weight, dimensions, material,
                    usage_metrics, competitive_notes, owner_id,
                    created_at, updated_at
               FROM product_definitions
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
        $name = trim($data['product_name'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'id' => null, 'error' => 'Product name is required.'];
        }

        $values       = $this->buildValues($data);
        $cols         = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO product_definitions ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function update(int $id, array $data): array
    {
        $name = trim($data['product_name'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'error' => 'Product name is required.'];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE product_definitions SET {$set} WHERE id = ?",
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
        $where = 'WHERE (product_name LIKE ? OR sku LIKE ? OR product_family LIKE ?
                      OR product_type LIKE ? OR lifecycle_status LIKE ?)';
        return [$where, [$like, $like, $like, $like, $like]];
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
