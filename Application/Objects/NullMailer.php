<?php

class NullMailer implements Mailer
{
    public function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool {
        Logger::getInstance()->info('Email suppressed (NullMailer)', [
            'to'      => $toName ? "{$toName} <{$to}>" : $to,
            'subject' => $subject,
        ]);
        return true;
    }
}
