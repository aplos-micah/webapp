<?php

require_once __DIR__ . '/Objects/Env.php';
Env::load(__DIR__ . '/../configuration/.env');

require_once __DIR__ . '/Objects/Logger.php';
require_once __DIR__ . '/Mail/Mailer.php';
require_once __DIR__ . '/Mail/PhpMailer.php';
require_once __DIR__ . '/Mail/NullMailer.php';
require_once __DIR__ . '/Mail/MailerFactory.php';
require_once __DIR__ . '/Objects/Response.php';
require_once __DIR__ . '/Objects/Validator.php';
require_once __DIR__ . '/Objects/Sanitization.php';
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/Objects/DB.php';
require_once __DIR__ . '/Objects/Container.php';
require_once __DIR__ . '/router.php';

Sanitization::sanitizeAll();
Router::dispatch();
