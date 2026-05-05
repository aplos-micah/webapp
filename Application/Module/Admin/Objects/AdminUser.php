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

    /** Return a single user row by ID, or null if not found. */
    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, email, user_type, is_active, phone, job_title,
                    timezone, email_verified_at, company_id, created_at, updated_at
               FROM users WHERE id = ? LIMIT 1',
            [$id]
        ) ?: null;
    }

    /**
     * Return all user_module_access rows for a user, keyed by module name.
     * ['crm' => ['tier', 'granted_at', 'granted_by', 'granted_by_name'], ...]
     */
    public function getModuleAccess(int $userId): array
    {
        $rows = $this->db->query(
            'SELECT uma.module, uma.tier, uma.granted_at, uma.granted_by,
                    u.name AS granted_by_name
               FROM user_module_access uma
               LEFT JOIN users u ON u.id = uma.granted_by
              WHERE uma.user_id = ?
              ORDER BY uma.module ASC',
            [$userId]
        );
        $access = [];
        foreach ($rows as $row) {
            $access[$row['module']] = $row;
        }
        return $access;
    }

    /**
     * Upsert or remove a module access row for a user.
     * $tier = 'none' removes the row; otherwise inserts/updates.
     */
    public function setModuleAccess(int $userId, string $module, string $tier, int $grantedBy): void
    {
        $module = strtolower(trim($module));
        if ($tier === 'none' || $tier === '') {
            $this->db->execute(
                'DELETE FROM user_module_access WHERE user_id = ? AND module = ?',
                [$userId, $module]
            );
            return;
        }
        $tier = in_array($tier, User::MODULE_TIER_VALUES, true) ? $tier : 'Free';
        $this->db->execute(
            'INSERT INTO user_module_access (user_id, module, tier, granted_by)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE tier = VALUES(tier), granted_by = VALUES(granted_by)',
            [$userId, $module, $tier, $grantedBy]
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
