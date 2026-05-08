<?php

require_once __DIR__ . '/Objects/Project.php';
require_once __DIR__ . '/Widgets/ProjectSummary.php';

class ProjectsContainer
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
            'project'         => new Project($db),
            'project_summary' => new ProjectSummary($db),
            default           => throw new RuntimeException("ProjectsContainer: unknown service '{$id}'"),
        };
    }
}
