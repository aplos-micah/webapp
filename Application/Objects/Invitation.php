<?php

class Invitation
{
    private $db;

    private const TTL_DAYS = 7;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * Create an invitation. Returns ['ok', 'token', 'error'].
     * Plain token is returned for emailing; only SHA-256 hash is stored.
     */
    public function invite(int $companyId, int $invitedBy, string $inviterEmail, string $invitedEmail): array
    {
        $invitedEmail = strtolower(trim($invitedEmail));

        if (!filter_var($invitedEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Please enter a valid email address.');
        }

        if (!$this->sameDomain($inviterEmail, $invitedEmail)) {
            $domain = substr($inviterEmail, strpos($inviterEmail, '@'));
            return $this->fail("Invitations are restricted to {$domain} email addresses.");
        }

        $existing = $this->db->queryOne(
            'SELECT id FROM company_invitations
              WHERE company_id = ? AND invited_email = ? AND accepted_at IS NULL AND expires_at > NOW()
              LIMIT 1',
            [$companyId, $invitedEmail]
        );
        if ($existing) {
            return $this->fail('A pending invitation has already been sent to that address.');
        }

        $user = $this->db->queryOne(
            'SELECT company_id FROM users WHERE email = ? LIMIT 1',
            [$invitedEmail]
        );
        if ($user && $user['company_id']) {
            return $this->fail('That user is already associated with a company.');
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash  = hash('sha256', $plainToken);
        $expiresAt  = date('Y-m-d H:i:s', strtotime('+' . self::TTL_DAYS . ' days'));

        $this->db->insert(
            'INSERT INTO company_invitations (company_id, invited_by, invited_email, token_hash, expires_at)
             VALUES (?, ?, ?, ?, ?)',
            [$companyId, $invitedBy, $invitedEmail, $tokenHash, $expiresAt]
        );

        return ['ok' => true, 'token' => $plainToken, 'error' => null];
    }

    /** Look up a valid (not expired, not accepted) invitation by plain token. */
    public function findByToken(string $plainToken): ?array
    {
        $tokenHash = hash('sha256', $plainToken);
        return $this->db->queryOne(
            'SELECT i.*, u.name AS inviter_name, c.name AS company_name
               FROM company_invitations i
               JOIN users   u ON u.id = i.invited_by
               JOIN company c ON c.id = i.company_id
              WHERE i.token_hash = ? LIMIT 1',
            [$tokenHash]
        );
    }

    /** Mark an invitation as accepted. */
    public function accept(int $id): void
    {
        $this->db->execute(
            'UPDATE company_invitations SET accepted_at = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), $id]
        );
    }

    /** Return all invitations for a company, newest first, with inviter name. */
    public function getByCompany(int $companyId): array
    {
        return $this->db->query(
            'SELECT i.id, i.invited_email, i.expires_at, i.accepted_at, i.created_at,
                    u.name AS inviter_name
               FROM company_invitations i
               JOIN users u ON u.id = i.invited_by
              WHERE i.company_id = ?
              ORDER BY i.created_at DESC',
            [$companyId]
        );
    }

    private function sameDomain(string $a, string $b): bool
    {
        return substr($a, strpos($a, '@')) === substr($b, strpos($b, '@'));
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'token' => null, 'error' => $message];
    }
}
