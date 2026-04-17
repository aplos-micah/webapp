<?php

/**
 * Account
 *
 * Data access layer for the accounts table.
 */
class Account
{
    private $db;

    /** Writable fields for create / update */
    private const FIELDS = [
        'name', 'account_number', 'site', 'parent_id', 'industry', 'type',
        'billing_address', 'shipping_address', 'annual_revenue', 'employee_count',
        'ownership', 'website', 'owner_id', 'status', 'last_activity_at', 'description',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    /** Columns that may be used for sorting */
    const SORTABLE = ['name', 'account_number', 'type', 'industry', 'status', 'website'];

    /**
     * Return the total number of accounts, optionally filtered by search term.
     */
    public function count(string $search = ''): int
    {
        [$where, $params] = $this->buildSearch($search);
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM accounts {$where}", $params);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Return accounts with optional search, sort, and pagination.
     */
    public function findAll(int $limit = 20, int $offset = 0, string $search = '', string $sort = 'name', string $dir = 'asc'): array
    {
        $col    = in_array($sort, self::SORTABLE, true) ? $sort : 'name';
        $dir    = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        [$where, $params] = $this->buildSearch($search);

        return $this->db->query(
            "SELECT id, name, account_number, type, industry, status, owner_id,
                    website, created_at, updated_at
               FROM accounts
              {$where}
              ORDER BY {$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    /**
     * Return a single account by ID, or null if not found.
     */
    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, account_number, site, parent_id, industry, type,
                    billing_address, shipping_address, annual_revenue, employee_count,
                    ownership, website, owner_id, status, last_activity_at, description,
                    created_at, updated_at
               FROM accounts
              WHERE id = ?
              LIMIT 1',
            [$id]
        );
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    /**
     * Insert a new account.
     * Returns ['ok' => bool, 'id' => int|null, 'error' => string|null].
     */
    public function create(array $data): array
    {
        $name = trim($data['name'] ?? '');
        if ($err = Validator::required($name, 'Account name')) {
            return ['ok' => false, 'id' => null, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $cols   = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO accounts ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    /**
     * Update an existing account.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function update(int $id, array $data): array
    {
        $name = trim($data['name'] ?? '');
        if ($err = Validator::required($name, 'Account name')) {
            return ['ok' => false, 'error' => $err];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE accounts SET {$set} WHERE id = ?",
            [...array_values($values), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    // =========================================================================
    // Internals
    // =========================================================================

    /**
     * Build a WHERE clause and params array for a full-text search across visible columns.
     * Returns ['', []] when search is empty.
     */
    private function buildSearch(string $search): array
    {
        if ($search === '') {
            return ['', []];
        }
        $like   = '%' . $search . '%';
        $where  = 'WHERE (name LIKE ? OR account_number LIKE ? OR type LIKE ?
                       OR industry LIKE ? OR status LIKE ? OR website LIKE ?)';
        return [$where, [$like, $like, $like, $like, $like, $like]];
    }

    /**
     * Build a sanitised column => value map from raw input, limited to known fields.
     */
    private function buildValues(array $data): array
    {
        $values = [];
        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $raw = $data[$field];
            // Coerce empty strings to null for nullable numeric / date fields
            if ($raw === '') {
                $raw = null;
            }
            $values[$field] = $raw;
        }
        return $values;
    }
}
