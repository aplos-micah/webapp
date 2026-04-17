<?php

class Config
{
    public static function instance(): string
    {
        return getenv('APP_ENV') ?: 'Test';
    }

    public static function db(): array
    {
        return [
            'driver'   => getenv('DB_DRIVER')   ?: 'mysql',
            'host'     => getenv('DB_HOST')      ?: 'localhost',
            'port'     => getenv('DB_PORT')      ?: '3306',
            'dbname'   => getenv('DB_NAME')      ?: '',
            'username' => getenv('DB_USERNAME')  ?: '',
            'password' => getenv('DB_PASSWORD')  ?: '',
            'charset'  => getenv('DB_CHARSET')   ?: 'utf8mb4',
        ];
    }
}
