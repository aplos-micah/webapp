<?php

class AdminOAuth
{
    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /** Active registered clients. */
    public function getActiveClients(): array
    {
        return $this->db->query(
            'SELECT id, client_id, name, redirect_uris, is_active, created_at
               FROM oauth_clients
              WHERE is_active = 1
              ORDER BY name ASC'
        );
    }

    /** Disabled registered clients. */
    public function getDisabledClients(): array
    {
        return $this->db->query(
            'SELECT id, client_id, name, redirect_uris, is_active, created_at
               FROM oauth_clients
              WHERE is_active = 0
              ORDER BY name ASC'
        );
    }

    public function countActiveClients(): int
    {
        $row = $this->db->queryOne('SELECT COUNT(*) AS n FROM oauth_clients WHERE is_active = 1');
        return (int) ($row['n'] ?? 0);
    }

    public function countDisabledClients(): int
    {
        $row = $this->db->queryOne('SELECT COUNT(*) AS n FROM oauth_clients WHERE is_active = 0');
        return (int) ($row['n'] ?? 0);
    }

    /** Disable a client — prevents new tokens from being issued. */
    public function disableClient(int $id): bool
    {
        return $this->db->execute(
            'UPDATE oauth_clients SET is_active = 0 WHERE id = ?',
            [$id]
        ) > 0;
    }

    /** Re-enable a previously disabled client. */
    public function enableClient(int $id): bool
    {
        return $this->db->execute(
            'UPDATE oauth_clients SET is_active = 1 WHERE id = ?',
            [$id]
        ) > 0;
    }

    /** Permanently delete a client — only allowed when already disabled. */
    public function deleteClient(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM oauth_clients WHERE id = ? AND is_active = 0',
            [$id]
        ) > 0;
    }

    /** Tokens that have not yet expired, paginated. */
    public function getActiveTokens(int $limit = 25, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT t.id, t.client_id, t.expires_at, t.created_at,
                    u.name AS user_name, u.email AS user_email
               FROM oauth_tokens t
               JOIN users u ON u.id = t.user_id
              WHERE t.expires_at > NOW()
              ORDER BY t.created_at DESC
              LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }

    /** Tokens that have expired but not yet been purged, paginated. */
    public function getExpiredTokens(int $limit = 25, int $offset = 0): array
    {
        return $this->db->query(
            'SELECT t.id, t.client_id, t.expires_at, t.created_at,
                    u.name AS user_name, u.email AS user_email
               FROM oauth_tokens t
               JOIN users u ON u.id = t.user_id
              WHERE t.expires_at <= NOW()
              ORDER BY t.expires_at DESC
              LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }

    /** Update the expiry date of a token. */
    public function updateTokenExpiry(int $id, string $expiresAt): bool
    {
        return $this->db->execute(
            'UPDATE oauth_tokens SET expires_at = ? WHERE id = ?',
            [$expiresAt, $id]
        ) > 0;
    }

    /** Recent authorization codes (last 50) with user info. */
    public function getRecentCodes(): array
    {
        return $this->db->query(
            'SELECT c.id, c.client_id, c.redirect_uri, c.expires_at,
                    c.used_at, c.created_at,
                    u.name AS user_name, u.email AS user_email
               FROM oauth_codes c
               JOIN users u ON u.id = c.user_id
              ORDER BY c.created_at DESC
              LIMIT 50'
        );
    }

    /** Check whether a client_id is already registered. */
    public function clientExists(string $clientId): bool
    {
        $row = $this->db->queryOne(
            'SELECT id FROM oauth_clients WHERE client_id = ? LIMIT 1',
            [$clientId]
        );
        return $row !== null;
    }

    /** Register a new OAuth client. */
    public function createClient(string $clientId, string $name, string $redirectUrisJson): bool
    {
        return $this->db->execute(
            'INSERT INTO oauth_clients (client_id, name, redirect_uris) VALUES (?, ?, ?)',
            [$clientId, $name, $redirectUrisJson]
        ) > 0;
    }

    /** Revoke a single token by ID. */
    public function revokeToken(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM oauth_tokens WHERE id = ?',
            [$id]
        ) > 0;
    }

    /** Revoke all tokens for a specific client. */
    public function revokeClientTokens(string $clientId): int
    {
        return $this->db->execute(
            'DELETE FROM oauth_tokens WHERE client_id = ?',
            [$clientId]
        );
    }

    public function countActiveTokens(): int
    {
        $row = $this->db->queryOne(
            'SELECT COUNT(*) AS n FROM oauth_tokens WHERE expires_at > NOW()'
        );
        return (int) ($row['n'] ?? 0);
    }

    public function countExpiredTokens(): int
    {
        $row = $this->db->queryOne(
            'SELECT COUNT(*) AS n FROM oauth_tokens WHERE expires_at <= NOW()'
        );
        return (int) ($row['n'] ?? 0);
    }

    public function purgeExpiredTokens(): int
    {
        return $this->db->execute(
            'DELETE FROM oauth_tokens WHERE expires_at <= NOW()'
        );
    }

    public function purgeUsedCodes(): int
    {
        return $this->db->execute(
            'DELETE FROM oauth_codes WHERE used_at IS NOT NULL OR expires_at <= NOW()'
        );
    }

    /** Delete a single authorization code by ID. */
    public function deleteCode(int $id): bool
    {
        return $this->db->execute(
            'DELETE FROM oauth_codes WHERE id = ?',
            [$id]
        ) > 0;
    }
}
