<?php

/**
 * Contact
 *
 * Data access layer for the contacts table.
 */
class Contact
{
    private $db;

    /** Writable fields for create / update */
    private const FIELDS = [
        'first_name', 'last_name', 'job_title', 'account_id',
        'linkedin_url', 'email', 'work_phone', 'mobile_phone', 'mailing_address',
        'communication_preference', 'lifecycle_stage', 'lead_source', 'owner_id',
        'status', 'last_contact_at', 'last_activity', 'lead_score',
        'interaction_history', 'industry', 'buying_role', 'renewal_date',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    /** Columns that may be used for sorting */
    const SORTABLE = ['first_name', 'last_name', 'job_title', 'email', 'status', 'lifecycle_stage'];

    /**
     * Return the total number of contacts, optionally filtered by search term.
     */
    public function count(string $search = ''): int
    {
        [$where, $params] = $this->buildSearch($search);
        $row = $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM contacts c LEFT JOIN accounts a ON a.id = c.account_id {$where}",
            $params
        );
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Return contacts with optional search, sort, and pagination.
     */
    public function findAll(int $limit = 20, int $offset = 0, string $search = '', string $sort = 'last_name', string $dir = 'asc'): array
    {
        $col = in_array($sort, self::SORTABLE, true) ? $sort : 'last_name';
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        [$where, $params] = $this->buildSearch($search);

        return $this->db->query(
            "SELECT c.id, c.first_name, c.last_name, c.job_title,
                    c.account_id, a.name AS account_name,
                    c.email, c.status, c.lifecycle_stage, c.owner_id,
                    c.created_at, c.updated_at
               FROM contacts c
               LEFT JOIN accounts a ON a.id = c.account_id
              {$where}
              ORDER BY c.{$col} {$dir}
              LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );
    }

    /**
     * Return a single contact by ID, or null if not found.
     */
    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, first_name, last_name, job_title, company, account_id,
                    linkedin_url, email, work_phone, mobile_phone, mailing_address,
                    communication_preference, lifecycle_stage, lead_source, owner_id,
                    status, last_contact_at, last_activity, lead_score,
                    interaction_history, industry, buying_role, renewal_date,
                    created_at, updated_at
               FROM contacts
              WHERE id = ?
              LIMIT 1',
            [$id]
        );
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    /**
     * Insert a new contact.
     * Returns ['ok' => bool, 'id' => int|null, 'error' => string|null].
     */
    public function create(array $data): array
    {
        $firstName = trim($data['first_name'] ?? '');
        $lastName  = trim($data['last_name']  ?? '');
        if ($firstName === '' || $lastName === '') {
            return ['ok' => false, 'id' => null, 'error' => 'First name and last name are required.'];
        }

        $values = $this->buildValues($data);
        $cols   = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $id = $this->db->insert(
            "INSERT INTO contacts ({$cols}) VALUES ({$placeholders})",
            array_values($values)
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    /**
     * Update an existing contact.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function update(int $id, array $data): array
    {
        $firstName = trim($data['first_name'] ?? '');
        $lastName  = trim($data['last_name']  ?? '');
        if ($firstName === '' || $lastName === '') {
            return ['ok' => false, 'error' => 'First name and last name are required.'];
        }

        $values = $this->buildValues($data);
        $set    = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($values)));

        $this->db->execute(
            "UPDATE contacts SET {$set} WHERE id = ?",
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
        $like  = '%' . $search . '%';
        $where = 'WHERE (c.first_name LIKE ? OR c.last_name LIKE ?
                      OR c.email LIKE ? OR c.job_title LIKE ? OR a.name LIKE ?)';
        return [$where, [$like, $like, $like, $like, $like]];
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
            if ($raw === '') {
                $raw = null;
            }
            $values[$field] = $raw;
        }
        return $values;
    }
}
