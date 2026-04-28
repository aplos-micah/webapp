<?php

/**
 * GET /api
 *
 * API discovery — dynamically scans Application/Module/ for modules that have
 * an Api/ subfolder and returns the endpoints accessible to the authenticated
 * user based on each module's access configuration.
 *
 * Each endpoint may provide a meta.php alongside its controller.php to
 * self-describe its method, description, and accepted parameters.
 */

$modulesRoot = dirname(__DIR__) . '/Module';
$apis        = [];

foreach (scandir($modulesRoot) as $moduleName) {
    if ($moduleName === '.' || $moduleName === '..') continue;

    $moduleDir = $modulesRoot . '/' . $moduleName;
    if (!is_dir($moduleDir)) continue;

    // Every valid module must have a module.php defining its access rules
    $moduleConfigFile = $moduleDir . '/module.php';
    if (!file_exists($moduleConfigFile)) continue;

    $config = require $moduleConfigFile;

    // requiresUserType — must match session user_type exactly
    if (!empty($config['requiresUserType'])) {
        if (($_SESSION['user_type'] ?? '') !== $config['requiresUserType']) continue;
    }

    // requiresModule* — e.g. requiresModuleCRM → $_SESSION['module_crm']
    foreach ($config as $key => $val) {
        if (str_starts_with($key, 'requiresModule') && $val === true) {
            $sessionKey = 'module_' . strtolower(substr($key, 14));
            if (empty($_SESSION[$sessionKey])) continue 2;
        }
    }

    // Scan the module's Api/ subfolder for endpoint directories
    $apiDir = $moduleDir . '/Api';
    if (!is_dir($apiDir)) continue;

    foreach (scandir($apiDir) as $endpointName) {
        if ($endpointName === '.' || $endpointName === '..') continue;

        $endpointDir = $apiDir . '/' . $endpointName;
        if (!is_dir($endpointDir)) continue;
        if (!file_exists($endpointDir . '/controller.php')) continue;

        $meta = file_exists($endpointDir . '/meta.php')
            ? require $endpointDir . '/meta.php'
            : [];

        $entry = [
            'module'   => $moduleName,
            'endpoint' => '/api/' . strtolower($endpointName),
            'method'   => $meta['method'] ?? 'GET',
        ];

        if (!empty($meta['description'])) {
            $entry['description'] = $meta['description'];
        }

        if (!empty($meta['params'])) {
            $entry['params'] = $meta['params'];
        }

        $apis[] = $entry;
    }
}

$count = count($apis);

return Response::json([
    'ok'   => true,
    'data' => [
        'apis' => $apis,
    ],
    'meta' => [
        'total'   => $count,
        'message' => $count === 0
            ? 'No Available APIs'
            : $count . ' endpoint' . ($count !== 1 ? 's' : '') . ' available',
    ],
]);
