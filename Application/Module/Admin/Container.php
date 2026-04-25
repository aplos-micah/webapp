<?php

/**
 * AdminContainer
 *
 * Service registry for the Admin module. One instance of each Admin Object
 * per request. Controllers call AdminContainer::get('name').
 *
 * Adding a new service: add a case to make() and a require_once below.
 */

require_once __DIR__ . '/Objects/AdminUser.php';
require_once __DIR__ . '/Objects/AdminCompany.php';
require_once __DIR__ . '/Objects/AdminOAuth.php';

class AdminContainer
{
    private static array $services = [];

    public static function get(string $id): object
    {
        return self::$services[$id] ??= self::make($id);
    }

    private static function make(string $id): object
    {
        $db = Container::db();
        return match ($id) {
            'admin_user'    => new AdminUser($db),
            'admin_company' => new AdminCompany($db),
            'admin_oauth'   => new AdminOAuth($db),
            default         => throw new RuntimeException("AdminContainer: unknown service '{$id}'"),
        };
    }
}
