<?php

class MailerFactory
{
    public static function make(): Mailer
    {
        return getenv('MAIL_DRIVER') === 'php' ? new PhpMailer() : new NullMailer();
    }
}
