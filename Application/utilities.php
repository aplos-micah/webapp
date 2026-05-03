<?php

require_once __DIR__ . '/Objects/Env.php';
Env::load(__DIR__ . '/../configuration/.env');

require_once __DIR__ . '/Objects/Logger.php';
require_once __DIR__ . '/Objects/Mailer.php';
require_once __DIR__ . '/Objects/PhpMailer.php';
require_once __DIR__ . '/Objects/NullMailer.php';
require_once __DIR__ . '/Objects/MailerFactory.php';
require_once __DIR__ . '/Objects/Response.php';
require_once __DIR__ . '/Objects/Validator.php';
require_once __DIR__ . '/Objects/Sanitization.php';
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/Objects/DB.php';
require_once __DIR__ . '/Objects/Container.php';
require_once __DIR__ . '/Module/Admin/Container.php';
require_once __DIR__ . '/Module/CRM/Container.php';
require_once __DIR__ . '/router.php';

Sanitization::sanitizeAll();
Router::dispatch();
