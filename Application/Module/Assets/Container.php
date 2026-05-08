<?php

require_once __DIR__ . '/Objects/Asset.php';
require_once __DIR__ . '/Widgets/AssetSummary.php';

class AssetsContainer
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
            'asset'         => new Asset($db),
            'asset_summary' => new AssetSummary($db),
            default         => throw new RuntimeException("AssetsContainer: unknown service '{$id}'"),
        };
    }
}
