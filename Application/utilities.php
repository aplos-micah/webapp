<?php

require_once __DIR__ . '/Objects/Env.php';
Env::load(__DIR__ . '/../configuration/.env');

require_once __DIR__ . '/Objects/Logger.php';
require_once __DIR__ . '/Objects/Mailer.php';
require_once __DIR__ . '/Objects/PhpMailer.php';
require_once __DIR__ . '/Objects/NullMailer.php';
require_once __DIR__ . '/Objects/MailerFactory.php';
require_once __DIR__ . '/Objects/Response.php';
require_once __DIR__ . '/Objects/Request.php';
require_once __DIR__ . '/Objects/Validator.php';
require_once __DIR__ . '/Objects/Sanitization.php';
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/Objects/DB.php';
require_once __DIR__ . '/Objects/Container.php';

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/UI/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

foreach (scandir(__DIR__ . '/Module') as $_mod) {
    $_containerFile = __DIR__ . '/Module/' . $_mod . '/Container.php';
    if (is_file($_containerFile)) {
        require_once $_containerFile;
    }
}
unset($_mod, $_containerFile);

require_once __DIR__ . '/router.php';

Sanitization::sanitizeAll();
Router::dispatch();
