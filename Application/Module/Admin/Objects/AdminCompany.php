<?php

/**
 * AdminCompany
 *
 * Data access layer for admin-facing company management: listing, searching,
 * paginating, and editing companies. User-facing company operations remain on Company.
 */
class AdminCompany
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /** Columns that may be used for sorting */
    const SORTABLE = ['name', 'email', 'city', 'created_at'];

    public function count(string $search = ''): int
    {
        if ($search !== '') {
            $like = '%' . $search . '%';
            $row  = $this->db->queryOne(
                'SELECT COUNT(*) AS total FROM company
                  WHERE name LIKE ? OR email LIKE ? OR city LIKE ? OR website LIKE ?',
                [$like, $like, $like, $like]
            );
        } else {
            $row = $this->db->queryOne('SELECT COUNT(*) AS total FROM company');
        }
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(int $limit, int $offset, string $search = '', string $sort = 'name', string $dir = 'asc'): array
    {
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'name';
        $dir  = $dir === 'desc' ? 'DESC' : 'ASC';

        $memberCount = '(SELECT COUNT(*) FROM users WHERE users.company_id = company.id) AS member_count';

        if ($search !== '') {
            $like = '%' . $search . '%';
            return $this->db->query(
                "SELECT id, name, phone, email, city, website, created_at, {$memberCount}
                   FROM company
                  WHERE name LIKE ? OR email LIKE ? OR city LIKE ? OR website LIKE ?
                  ORDER BY {$sort} {$dir}
                  LIMIT ? OFFSET ?",
                [$like, $like, $like, $like, $limit, $offset]
            );
        }

        return $this->db->query(
            "SELECT id, name, phone, email, city, website, created_at, {$memberCount}
               FROM company
              ORDER BY {$sort} {$dir}
              LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Update a company's fields as an admin.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function updateByAdmin(int $id, array $data): array
    {
        $name    = trim($data['name']    ?? '');
        $phone   = trim($data['phone']   ?? '');
        $email   = trim($data['email']   ?? '');
        $address = trim($data['address'] ?? '');
        $city    = trim($data['city']    ?? '');
        $state   = trim($data['state']   ?? '');
        $zip     = trim($data['zip']     ?? '');
        $website = trim($data['website'] ?? '');

        if ($err = Validator::required($name, 'Company name')) {
            return ['ok' => false, 'error' => $err];
        }

        if ($email !== '' && ($err = Validator::email($email, 'Email'))) {
            return ['ok' => false, 'error' => $err];
        }

        $this->db->execute(
            'UPDATE company
                SET name = ?, phone = ?, email = ?, address = ?, city = ?, state = ?, zip = ?, website = ?, updated_at = ?
              WHERE id = ?',
            [
                $name,
                $phone   ?: null,
                $email   ?: null,
                $address ?: null,
                $city    ?: null,
                $state   ?: null,
                $zip     ?: null,
                $website ?: null,
                date('Y-m-d H:i:s'),
                $id,
            ]
        );

        return ['ok' => true, 'error' => null];
    }
}
