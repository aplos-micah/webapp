<?php

class PhpMailer implements Mailer
{
    public function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool {
        $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@localhost';
        $fromName    = getenv('MAIL_FROM_NAME')    ?: 'AplosCRM';

        if ($textBody === '') {
            $textBody = strip_tags($htmlBody);
        }

        $toHeader   = $toName  ? $this->encodeHeader($toName)   . ' <' . $to . '>'          : $to;
        $fromHeader = $fromName ? $this->encodeHeader($fromName) . ' <' . $fromAddress . '>' : $fromAddress;
        $boundary   = bin2hex(random_bytes(8));

        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'From: '     . $fromHeader,
            'Reply-To: ' . $fromHeader,
            'X-Mailer: AplosCRM',
        ]);

        $body = "--{$boundary}\r\n"
              . "Content-Type: text/plain; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
              . quoted_printable_encode($textBody) . "\r\n\r\n"
              . "--{$boundary}\r\n"
              . "Content-Type: text/html; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
              . quoted_printable_encode($htmlBody) . "\r\n\r\n"
              . "--{$boundary}--";

        $result = @mail($toHeader, $this->encodeHeader($subject), $body, $headers);

        if (!$result) {
            Logger::getInstance()->warning('PhpMailer: mail() returned false', [
                'to'      => $to,
                'subject' => $subject,
            ]);
        }

        return $result;
    }

    private function encodeHeader(string $value): string
    {
        if (mb_detect_encoding($value, 'ASCII', true) !== false) {
            return $value;
        }
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
