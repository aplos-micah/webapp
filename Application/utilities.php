<?php

require_once __DIR__ . '/Env.php';
Env::load(__DIR__ . '/../configuration/.env');

require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Mail/Mailer.php';
require_once __DIR__ . '/Mail/PhpMailer.php';
require_once __DIR__ . '/Mail/NullMailer.php';
require_once __DIR__ . '/Mail/MailerFactory.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Sanitization.php';
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/Container.php';
require_once __DIR__ . '/router.php';

Sanitization::sanitizeAll();
Router::dispatch();
