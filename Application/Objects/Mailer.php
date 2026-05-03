<?php

interface Mailer
{
    /**
     * @param string $to       Recipient email address
     * @param string $toName   Recipient display name (may be empty)
     * @param string $subject  Email subject line
     * @param string $htmlBody HTML message body
     * @param string $textBody Plain-text fallback — auto-derived from HTML if omitted
     */
    public function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool;
}
