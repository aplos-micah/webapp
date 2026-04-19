<?php

/**
 * User
 *
 * Handles registration, authentication, and password reset.
 *
 * Password storage: bcrypt via password_hash (PASSWORD_BCRYPT, cost 12).
 * Reset tokens:    32-byte random token returned to the caller (for emailing);
 *                  only the SHA-256 hash of the token is stored — the plain
 *                  token is never written to the database.
 */
class User
{
    private $db;

    /** bcrypt cost factor — increase as hardware improves */
    private const HASH_COST = 12;

    /** How long (minutes) a password-reset token stays valid */
    private const RESET_TTL_MINUTES = 60;

    /** Valid user types */
    const TYPES = ['admin', 'manager', 'user', 'free'];

    /** Valid CRM module access levels */
    const MODULE_CRM_VALUES = ['Free', 'User', 'Manager'];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Registration
    // =========================================================================

    /**
     * Create a new user account.
     *
     * Returns an array with:
     *   ok      bool        — true on success
     *   user_id int|null    — new user's ID on success
     *   error   string|null — human-readable reason on failure
     */
    public function register(string $name, string $email, string $password, string $userType = 'free', string $moduleCrm = 'Free'): array
    {
        $name      = trim($name);
        $email     = strtolower(trim($email));
        $password  = trim($password);
        $userType  = in_array($userType, self::TYPES, true) ? $userType : 'user';
        $moduleCrm = in_array($moduleCrm, self::MODULE_CRM_VALUES, true) ? $moduleCrm : 'Free';

        if ($err = Validator::required($name, 'Name')
                 ?? Validator::required($email, 'Email')
                 ?? Validator::required($password, 'Password')) {
            return $this->fail($err);
        }

        if ($err = Validator::email($email, 'email')) {
            return $this->fail($err);
        }

        if ($err = Validator::minLength($password, 8, 'Password')) {
            return $this->fail($err);
        }

        if ($this->emailExists($email)) {
            return $this->fail('An account with that email address already exists.');
        }

        $hash   = password_hash($password, PASSWORD_BCRYPT, ['cost' => self::HASH_COST]);
        $userId = $this->db->insert(
            'INSERT INTO users (name, email, password_hash, user_type, Module_CRM) VALUES (?, ?, ?, ?, ?)',
            [$name, $email, $hash, $userType, $moduleCrm]
        );

        return ['ok' => true, 'user_id' => (int) $userId, 'error' => null];
    }

    // =========================================================================
    // Authentication
    // =========================================================================

    /**
     * Verify credentials and return the user row (without the password hash).
     * Returns null if the email is not found, the account is inactive, or the
     * password does not match.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $email = strtolower(trim($email));

        // Alias the column to bypass the DB class password-stripping guard
        // so we can call password_verify — the alias is never returned.
        $row = $this->db->queryOne(
            'SELECT id, name, email, user_type, Module_CRM, is_active, created_at,
                    password_hash AS _auth
               FROM users
              WHERE email = ?
              LIMIT 1',
            [$email]
        );

        if (!$row || !$row['is_active']) {
            return null;
        }

        if (!password_verify($password, $row['_auth'])) {
            return null;
        }

        // Rehash transparently if the cost factor has changed
        if (password_needs_rehash($row['_auth'], PASSWORD_BCRYPT, ['cost' => self::HASH_COST])) {
            $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => self::HASH_COST]);
            $this->db->execute(
                'UPDATE users SET password_hash = ? WHERE id = ?',
                [$newHash, $row['id']]
            );
        }

        unset($row['_auth']);
        return $row;
    }

    // =========================================================================
    // Password reset
    // =========================================================================

    /**
     * Generate a password-reset token for the given email.
     *
     * Returns the plain (unhashed) token that should be emailed to the user,
     * or null if no active account exists for that address.
     *
     * Only the SHA-256 hash of the token is persisted.
     */
    public function requestPasswordReset(string $email): ?string
    {
        $email = strtolower(trim($email));

        $user = $this->db->queryOne(
            'SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1',
            [$email]
        );

        if (!$user) {
            return null;
        }

        // Invalidate any existing unused tokens for this user
        $this->db->execute(
            'DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL',
            [$user['id']]
        );

        $plainToken = bin2hex(random_bytes(32));          // 64 hex chars
        $tokenHash  = hash('sha256', $plainToken);
        $expiresAt  = date('Y-m-d H:i:s', strtotime('+' . self::RESET_TTL_MINUTES . ' minutes'));

        $this->db->insert(
            'INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)',
            [$user['id'], $tokenHash, $expiresAt]
        );

        return $plainToken;
    }

    /**
     * Consume a reset token and update the user's password.
     *
     * Returns true on success, false if the token is invalid, expired, or
     * already used.
     */
    public function resetPassword(string $plainToken, string $newPassword): bool
    {
        if (Validator::minLength($newPassword, 8, 'Password') !== null) {
            return false;
        }

        $tokenHash = hash('sha256', $plainToken);
        $now       = date('Y-m-d H:i:s');

        $reset = $this->db->queryOne(
            'SELECT id, user_id
               FROM password_resets
              WHERE token_hash = ?
                AND expires_at > ?
                AND used_at IS NULL
              LIMIT 1',
            [$tokenHash, $now]
        );

        if (!$reset) {
            return false;
        }

        $newHash = password_hash(trim($newPassword), PASSWORD_BCRYPT, ['cost' => self::HASH_COST]);

        $this->db->transaction(function (DB $db) use ($reset, $newHash, $now): void {
            $db->execute(
                'UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?',
                [$newHash, $now, $reset['user_id']]
            );
            $db->execute(
                'UPDATE password_resets SET used_at = ? WHERE id = ?',
                [$now, $reset['id']]
            );
        });

        return true;
    }

    // =========================================================================
    // Profile management
    // =========================================================================

    /**
     * Update a user's profile fields.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function updateProfile(int $id, array $data): array
    {
        $name     = trim($data['name']      ?? '');
        $phone    = trim($data['phone']     ?? '');
        $jobTitle = trim($data['job_title'] ?? '');
        $timezone = trim($data['timezone']  ?? 'America/Chicago');

        if ($err = Validator::required($name, 'Full name')) {
            return ['ok' => false, 'error' => $err];
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'America/Chicago';
        }

        $this->db->execute(
            'UPDATE users
                SET name = ?, phone = ?, job_title = ?, timezone = ?, updated_at = ?
              WHERE id = ?',
            [$name, $phone ?: null, $jobTitle ?: null, $timezone, date('Y-m-d H:i:s'), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    /**
     * Change a user's password after verifying their current one.
     * Returns ['ok' => bool, 'error' => string|null].
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): array
    {
        if ($err = Validator::minLength($newPassword, 8, 'New password')) {
            return ['ok' => false, 'error' => $err];
        }

        $row = $this->db->queryOne(
            'SELECT password_hash AS _auth FROM users WHERE id = ? LIMIT 1',
            [$id]
        );

        if (!$row || !password_verify($currentPassword, $row['_auth'])) {
            return ['ok' => false, 'error' => 'Your current password is incorrect.'];
        }

        $newHash = password_hash(trim($newPassword), PASSWORD_BCRYPT, ['cost' => self::HASH_COST]);

        $this->db->execute(
            'UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?',
            [$newHash, date('Y-m-d H:i:s'), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    // =========================================================================
    // Lookup helpers
    // =========================================================================

    // =========================================================================
    // Admin listing
    // =========================================================================

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
                "SELECT id, name, email, user_type, Module_CRM, is_active, created_at
                   FROM users
                  WHERE name LIKE ? OR email LIKE ?
                  ORDER BY {$sort} {$dir}
                  LIMIT ? OFFSET ?",
                [$like, $like, $limit, $offset]
            );
        }

        return $this->db->query(
            "SELECT id, name, email, user_type, Module_CRM, is_active, created_at
               FROM users
              ORDER BY {$sort} {$dir}
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
        $name      = trim($data['name']       ?? '');
        $email     = strtolower(trim($data['email'] ?? ''));
        $userType  = in_array($data['user_type']  ?? '', self::TYPES,             true) ? $data['user_type']  : 'user';
        $moduleCrm = in_array($data['Module_CRM'] ?? '', self::MODULE_CRM_VALUES, true) ? $data['Module_CRM'] : 'Free';
        $isActive  = (int) ($data['is_active'] ?? 0) === 1 ? 1 : 0;

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
                SET name = ?, email = ?, user_type = ?, Module_CRM = ?, is_active = ?, updated_at = ?
              WHERE id = ?',
            [$name, $email, $userType, $moduleCrm, $isActive, date('Y-m-d H:i:s'), $id]
        );

        return ['ok' => true, 'error' => null];
    }

    /** Return a user row by ID (no password fields), or null if not found. */
    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, email, user_type, Module_CRM, module_crm_settings,
                    phone, job_title, timezone, is_active, company_id, created_at, updated_at
               FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    /** Return a user row by email (no password fields), or null if not found. */
    public function findByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            'SELECT id, name, email, user_type, Module_CRM, module_crm_settings,
                    phone, job_title, timezone, is_active, created_at, updated_at
               FROM users WHERE email = ? LIMIT 1',
            [strtolower(trim($email))]
        );
    }

    /**
     * Persist a JSON-encoded CRM settings blob for the given user.
     * Merges with any existing settings so unrelated keys are preserved.
     */
    public function saveCrmSettings(int $id, array $settings): void
    {
        $row      = $this->findById($id);
        $existing = json_decode($row['module_crm_settings'] ?? '{}', true) ?: [];
        $merged   = array_merge($existing, $settings);

        $this->db->execute(
            'UPDATE users SET module_crm_settings = ? WHERE id = ?',
            [json_encode($merged, JSON_UNESCAPED_UNICODE), $id]
        );
    }

    public function setCompany(int $userId, int $companyId): void
    {
        $this->db->execute(
            'UPDATE users SET company_id = ? WHERE id = ?',
            [$companyId, $userId]
        );
    }

    // =========================================================================
    // Internals
    // =========================================================================

    private function emailExists(string $email): bool
    {
        $row = $this->db->queryOne(
            'SELECT id FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
        return $row !== null;
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'user_id' => null, 'error' => $message];
    }
}
