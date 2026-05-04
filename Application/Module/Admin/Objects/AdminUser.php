<?php

/**
 * AdminUser
 *
 * Data access layer for admin-facing user management: listing, searching,
 * paginating, and editing users. Auth and profile operations remain on User.
 */
class AdminUser
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /** Columns that may be used for sorting */
    const SORTABLE = ['name', 'email', 'user_type', 'is_active', 'created_at'];

    public function count(string $search = ''): int
    {
        if ($search !== '') {
            $like = '%' . $search . '%';
            $row  = $this->db->queryOne(
                'SELECT COUNT(*) AS total FROM users
                  WHERE name LIKE ? OR email LIKE ?',
                [$like, $like]
            );
        } else {
            $row = $this->db->queryOne('SELECT COUNT(*) AS total FROM users');
        }
        return (int) ($row['total'] ?? 0);
    }

    public function findAll(int $limit, int $offset, string $search = '', string $sort = 'name', string $dir = 'asc'): array
    {
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'name';
        $dir  = $dir === 'desc' ? 'DESC' : 'ASC';

        if ($search !== '') {
            $like = '%' . $search . '%';
            return $this->db->query(
                "SELECT u.id, u.name, u.email, u.user_type, u.is_active, u.created_at,
                        uma.tier AS crm_tier
                   FROM users u
                   LEFT JOIN user_module_access uma ON uma.user_id = u.id AND uma.module = 'crm'
                  WHERE u.name LIKE ? OR u.email LIKE ?
                  ORDER BY u.{$sort} {$dir}
                  LIMIT ? OFFSET ?",
                [$like, $like, $limit, $offset]
            );
        }

        return $this->db->query(
            "SELECT u.id, u.name, u.email, u.user_type, u.is_active, u.created_at,
                    uma.tier AS crm_tier
               FROM users u
               LEFT JOIN user_module_access uma ON uma.user_id = u.id AND uma.module = 'crm'
              ORDER BY u.{$sort} {$dir}
              LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Update a user's core fields as an admin.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function updateByAdmin(int $id, array $data): array
    {
        $name     = trim($data['name']      ?? '');
        $email    = strtolower(trim($data['email'] ?? ''));
        $userType = in_array($data['user_type'] ?? '', User::TYPES, true) ? $data['user_type'] : 'user';
        $crmTier  = in_array($data['crm_tier']  ?? '', User::MODULE_TIER_VALUES, true) ? $data['crm_tier'] : 'Free';
        $isActive = (int) ($data['is_active'] ?? 0) === 1 ? 1 : 0;

        if ($err = Validator::required($name, 'Name')) {
            return ['ok' => false, 'error' => $err];
        }
        if ($err = Validator::email($email, 'email')) {
            return ['ok' => false, 'error' => $err];
        }

        $conflict = $this->db->queryOne(
            'SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1',
            [$email, $id]
        );
        if ($conflict) {
            return ['ok' => false, 'error' => 'That email address is already in use.'];
        }

        $this->db->execute(
            'UPDATE users
                SET name = ?, email = ?, user_type = ?, is_active = ?, updated_at = ?
              WHERE id = ?',
            [$name, $email, $userType, $isActive, date('Y-m-d H:i:s'), $id]
        );

        $this->db->execute(
            'INSERT INTO user_module_access (user_id, module, tier, granted_by)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE tier = VALUES(tier), granted_by = VALUES(granted_by)',
            [$id, 'crm', $crmTier, $_SESSION['user_id'] ?? null]
        );

        return ['ok' => true, 'error' => null];
    }
}
