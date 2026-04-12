<?php

class Config
{
    private static $db = [
        'driver'   => 'mysql',       // 'mysql' or 'sqlsrv'
        'host'     => 'localhost',
        'port'     => '3306',        // null = driver default (3306 MySQL, 1433 MSSQL)
        'dbname'   => 'accountaplosuite_crm',
        'username' => 'accountaplosuite_crm',
        'password' => 'todruz-4qytxo-hapheZ',
        'charset'  => 'utf8mb4',     // MySQL only
    ];

    public static function db(): array
    {
        return self::$db;
    }
}
