<?php

require_once __DIR__ . '/Objects/Article.php';

class KBContainer
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
            'article' => new Article($db),
            default   => throw new RuntimeException("KBContainer: unknown service '{$id}'"),
        };
    }
}
