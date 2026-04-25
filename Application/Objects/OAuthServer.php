<?php

class OAuthServer
{
    private const CODE_TTL_MINUTES  = 10;
    private const TOKEN_TTL_HOURS   = 1;

    private $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * Look up a registered client and validate that $redirectUri is allowed.
     * Returns the client row, or null if the client is unknown or the URI is not
     * in the registered allow-list.
     */
    public function findClient(string $clientId, string $redirectUri): ?array
    {
        $client = $this->db->queryOne(
            'SELECT * FROM oauth_clients WHERE client_id = ? LIMIT 1',
            [$clientId]
        );
        if (!$client) {
            return null;
        }
        $allowed = json_decode($client['redirect_uris'], true) ?? [];
        if (!in_array($redirectUri, $allowed, true)) {
            return null;
        }
        return $client;
    }

    /**
     * Generate a random authorization code, store its SHA-256 hash, and return
     * the plain code for inclusion in the redirect to the client.
     */
    public function createCode(
        string $clientId,
        int    $userId,
        string $redirectUri,
        string $codeChallenge
    ): string {
        $plain     = bin2hex(random_bytes(32));
        $hash      = hash('sha256', $plain);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::CODE_TTL_MINUTES . ' minutes'));

        $this->db->insert(
            'INSERT INTO oauth_codes (code_hash, client_id, user_id, redirect_uri, code_challenge, expires_at)
             VALUES (?, ?, ?, ?, ?, ?)',
            [$hash, $clientId, $userId, $redirectUri, $codeChallenge, $expiresAt]
        );

        return $plain;
    }

    /**
     * Exchange an authorization code for an access token.
     *
     * Validates: code exists, unused, not expired, client_id matches,
     * redirect_uri matches, and PKCE code_verifier produces the stored challenge.
     *
     * Returns ['access_token', 'token_type', 'expires_in'] on success, or null on failure.
     */
    public function exchangeCode(
        string $code,
        string $codeVerifier,
        string $clientId,
        string $redirectUri
    ): ?array {
        $codeHash = hash('sha256', $code);

        $row = $this->db->queryOne(
            'SELECT * FROM oauth_codes WHERE code_hash = ? LIMIT 1',
            [$codeHash]
        );

        if (!$row) {
            Logger::getInstance()->warning('OAuth exchangeCode: code not found', ['code_hash' => substr($codeHash, 0, 8)]);
            return null;
        }
        if ($row['used_at'] !== null) {
            Logger::getInstance()->warning('OAuth exchangeCode: code already used');
            return null;
        }
        if (strtotime($row['expires_at']) < time()) {
            Logger::getInstance()->warning('OAuth exchangeCode: code expired', ['expires_at' => $row['expires_at']]);
            return null;
        }
        if ($row['client_id'] !== $clientId) {
            Logger::getInstance()->warning('OAuth exchangeCode: client_id mismatch', ['stored' => $row['client_id'], 'provided' => $clientId]);
            return null;
        }
        if ($row['redirect_uri'] !== $redirectUri) {
            Logger::getInstance()->warning('OAuth exchangeCode: redirect_uri mismatch', ['stored' => $row['redirect_uri'], 'provided' => $redirectUri]);
            return null;
        }

        // PKCE: base64url(SHA256(code_verifier)) must equal stored code_challenge
        $expected = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
        if (!hash_equals($expected, $row['code_challenge'])) {
            Logger::getInstance()->warning('OAuth exchangeCode: PKCE mismatch', [
                'expected'  => $expected,
                'stored'    => $row['code_challenge'],
                'verifier_len' => strlen($codeVerifier),
            ]);
            return null;
        }

        // Mark the code as used (replay prevention)
        $this->db->execute(
            'UPDATE oauth_codes SET used_at = ? WHERE code_hash = ?',
            [date('Y-m-d H:i:s'), $codeHash]
        );

        // Issue access token
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash  = hash('sha256', $plainToken);
        $expiresAt  = date('Y-m-d H:i:s', strtotime('+' . self::TOKEN_TTL_HOURS . ' hours'));

        $this->db->insert(
            'INSERT INTO oauth_tokens (token_hash, client_id, user_id, expires_at)
             VALUES (?, ?, ?, ?)',
            [$tokenHash, $clientId, (int) $row['user_id'], $expiresAt]
        );

        return [
            'access_token' => $plainToken,
            'token_type'   => 'Bearer',
            'expires_in'   => self::TOKEN_TTL_HOURS * 3600,
        ];
    }

    /**
     * Validate an incoming Bearer token. Returns the associated user row on
     * success, or null if the token is unknown or expired.
     */
    public function validateToken(string $bearerToken): ?array
    {
        $hash = hash('sha256', $bearerToken);

        $token = $this->db->queryOne(
            'SELECT t.user_id, t.expires_at, u.id, u.name, u.email, u.user_type, u.Module_CRM
               FROM oauth_tokens t
               JOIN users u ON u.id = t.user_id
              WHERE t.token_hash = ? LIMIT 1',
            [$hash]
        );

        if (!$token || strtotime($token['expires_at']) < time()) {
            return null;
        }

        return $token;
    }
}
