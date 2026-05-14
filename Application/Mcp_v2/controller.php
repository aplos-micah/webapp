<?php

/**
 * POST /api/mcp_v2
 *
 * MCP v2 — Streamable HTTP endpoint (protocol 2024-11-05).
 * Dynamically discovers tools from Module/{Name}/Api/{Endpoint}/mcp.php,
 * filtered by the authenticated user's module entitlements.
 *
 * Auth: static API key (MCP_API_KEY env var) or OAuth bearer token.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');

// ── Auth ──────────────────────────────────────────────────────────────────────

$rawHeader = $_SERVER['HTTP_AUTHORIZATION']
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '')
    ?? '';

if (isset($_SERVER['HTTP_X_MCP_KEY'])) {
    $rawHeader = 'Bearer ' . $_SERVER['HTTP_X_MCP_KEY'];
}

$plainCredential = str_starts_with($rawHeader, 'Bearer ') ? substr($rawHeader, 7) : '';

$configuredKey = getenv('MCP_API_KEY');
$apiKeyPresent = $configuredKey !== false && $configuredKey !== '';

$authorized = false;
$tokenUser  = null;

if ($apiKeyPresent && hash_equals($configuredKey, $plainCredential)) {
    $authorized = true;
} elseif ($plainCredential !== '') {
    $tokenUser  = Container::get('oauth')->validateToken($plainCredential);
    $authorized = $tokenUser !== null;
}

if ($apiKeyPresent && !$authorized) {
    return Response::json(
        ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32001, 'message' => 'Unauthorized']],
        401
    );
}

// ── Parse request ─────────────────────────────────────────────────────────────

$body = file_get_contents('php://input');
$msg  = json_decode($body ?: '', true);

if ($msg === null) {
    return Response::json(
        ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32700, 'message' => 'Parse error']],
        400
    );
}

$id     = $msg['id']     ?? null;
$method = $msg['method'] ?? '';
$params = $msg['params'] ?? [];

if ($id === null) {
    http_response_code(202);
    exit;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function mcpv2_ok(mixed $id, mixed $result): Response
{
    return Response::json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
}

function mcpv2_err(mixed $id, int $code, string $message): Response
{
    return Response::json(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]]);
}

function tool_text_v2(string $text): array
{
    return ['content' => [['type' => 'text', 'text' => $text]]];
}

function json_pretty_v2(mixed $data): string
{
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// ── Container resolver — picks the module container or falls back to core ─────

function mcpv2_container(string $module, string $service): object
{
    $class = $module . 'Container';
    if (class_exists($class)) {
        return $class::get($service);
    }
    return Container::get($service);
}

// ── Tool handlers ─────────────────────────────────────────────────────────────

function mcpv2_handle_list(string $module, string $service, array $args): array
{
    $search = trim($args['search'] ?? '');
    $limit  = min(100, max(1, (int) ($args['limit']  ?? 20)));
    $offset = max(0,          (int) ($args['offset'] ?? 0));

    $obj   = mcpv2_container($module, $service);
    $total = $obj->count($search);
    $data  = $obj->findAll($limit, $offset, $search);

    return tool_text_v2(json_pretty_v2([
        'ok'   => true,
        'data' => $data,
        'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset],
    ]));
}

function mcpv2_handle_list_filtered(string $module, string $service, array $args): array
{
    $search  = trim($args['search'] ?? '');
    $limit   = min(100, max(1, (int) ($args['limit']  ?? 20)));
    $offset  = max(0,          (int) ($args['offset'] ?? 0));
    $filters = array_diff_key($args, array_flip(['search', 'limit', 'offset']));

    $obj  = mcpv2_container($module, $service);
    $data = $obj->findAll($limit, $offset, $search, 'activity_date', 'desc', $filters);

    return tool_text_v2(json_pretty_v2([
        'ok'   => true,
        'data' => $data,
        'meta' => ['total' => count($data), 'limit' => $limit, 'offset' => $offset],
    ]));
}

function mcpv2_handle_get(string $module, string $service, array $args, string $label): array
{
    $id = (int) ($args['id'] ?? 0);
    if ($id <= 0) {
        return tool_text_v2(json_pretty_v2(['ok' => false, 'error' => 'id must be a positive integer']));
    }

    $record = mcpv2_container($module, $service)->findById($id);

    return $record
        ? tool_text_v2(json_pretty_v2(['ok' => true, 'data' => $record]))
        : tool_text_v2(json_pretty_v2(['ok' => false, 'error' => "{$label} not found."]));
}

function mcpv2_handle_get_with_items(string $module, string $service, string $relService, string $relMethod, string $relKey, array $args, string $label): array
{
    $id = (int) ($args['id'] ?? 0);
    if ($id <= 0) {
        return tool_text_v2(json_pretty_v2(['ok' => false, 'error' => 'id must be a positive integer']));
    }

    $record = mcpv2_container($module, $service)->findById($id);
    if (!$record) {
        return tool_text_v2(json_pretty_v2(['ok' => false, 'error' => "{$label} not found."]));
    }

    $record[$relKey] = mcpv2_container($module, $relService)->{$relMethod}($id);

    return tool_text_v2(json_pretty_v2(['ok' => true, 'data' => $record]));
}

// ── Dynamic module scanner — builds tool list and dispatch map ────────────────

$modulesRoot = dirname(__DIR__) . '/Module';
$allTools    = [];
$toolMap     = [];

foreach (scandir($modulesRoot) as $moduleName) {
    if ($moduleName === '.' || $moduleName === '..') continue;

    $moduleDir = $modulesRoot . '/' . $moduleName;
    if (!is_dir($moduleDir)) continue;

    $moduleConfigFile = $moduleDir . '/module.php';
    if (!file_exists($moduleConfigFile)) continue;

    $config = require $moduleConfigFile;

    // requiresUserType check
    if (!empty($config['requiresUserType'])) {
        $sessionType = $tokenUser['user_type'] ?? ($_SESSION['user_type'] ?? '');
        if ($sessionType !== $config['requiresUserType']) continue;
    }

    // requiresModule* check
    foreach ($config as $key => $val) {
        if (str_starts_with($key, 'requiresModule') && $val === true) {
            $modulePart   = strtolower(substr($key, 14)); // e.g. 'crm'
            $sessionKey   = 'module_' . $modulePart;      // e.g. 'module_crm'
            $sessionValue = ($tokenUser['module_tiers'][$modulePart] ?? null)
                         ?? ($_SESSION[$sessionKey] ?? '');
            if (empty($sessionValue)) continue 2;
        }
    }

    // Scan Api/ subfolder for mcp.php files
    $apiDir = $moduleDir . '/Api';
    if (!is_dir($apiDir)) continue;

    foreach (scandir($apiDir) as $endpointName) {
        if ($endpointName === '.' || $endpointName === '..') continue;

        $endpointDir = $apiDir . '/' . $endpointName;
        if (!is_dir($endpointDir)) continue;

        $mcpFile = $endpointDir . '/mcp.php';
        if (!file_exists($mcpFile)) continue;

        $tools = require $mcpFile;

        foreach ($tools as $tool) {
            $allTools[] = [
                'name'        => $tool['name'],
                'description' => $tool['description'],
                'inputSchema' => $tool['inputSchema'],
            ];
            $toolMap[$tool['name']] = array_merge($tool, ['_module' => $moduleName]);
        }
    }
}

function mcpv2_handle_update(string $module, string $service, array $args, string $label): array
{
    $id = (int) ($args['id'] ?? 0);
    if ($id <= 0) {
        return tool_text_v2(json_pretty_v2(['ok' => false, 'error' => 'id must be a positive integer']));
    }

    unset($args['id']);

    $result = mcpv2_container($module, $service)->update($id, $args);

    return tool_text_v2(json_pretty_v2($result['ok']
        ? ['ok' => true]
        : ['ok' => false, 'error' => $result['error']]
    ));
}

function mcpv2_handle_create(string $module, string $service, array $args, ?array $tokenUser, array $tool = []): array
{
    unset($args['id']);

    if ($tokenUser && empty($args['owner_id'])) {
        $args['owner_id'] = $tokenUser['id'] ?? null;
    }

    $result = mcpv2_container($module, $service)->create($args);

    if (!$result['ok']) {
        return tool_text_v2(json_pretty_v2(['ok' => false, 'error' => $result['error']]));
    }

    $text = json_pretty_v2(['ok' => true, 'id' => $result['id']]);

    if (!empty($tool['follow_up'])) {
        $text .= "\n\n" . $tool['follow_up'];
    }

    return tool_text_v2($text);
}

// ── Tool dispatcher ───────────────────────────────────────────────────────────

function mcpv2_call(string $name, array $args, array $toolMap, ?array $tokenUser = null): array
{
    $tool = $toolMap[$name] ?? null;
    if ($tool === null) {
        throw new InvalidArgumentException("Unknown tool: {$name}");
    }

    $module = $tool['_module'] ?? '';
    $label  = $tool['label']   ?? ucfirst($tool['service'] ?? '');

    // Per-tool module tier check (e.g. Manager-only write operations)
    if (!empty($tool['requiresModuleTier'])) {
        $moduleLower  = strtolower($module);
        $sessionTier  = $tokenUser['module_tiers'][$moduleLower] ?? ($_SESSION['module_' . $moduleLower] ?? '');
        $sessionType  = $tokenUser['user_type'] ?? ($_SESSION['user_type'] ?? '');
        if ($sessionTier !== $tool['requiresModuleTier'] && $sessionType !== 'admin') {
            return tool_text_v2(json_pretty_v2([
                'ok'    => false,
                'error' => $tool['requiresModuleTier'] . ' module access is required for this operation.',
            ]));
        }
    }

    $result = match ($tool['handler']) {
        'list' => mcpv2_handle_list(
            $module,
            $tool['service'],
            $args
        ),
        'get' => mcpv2_handle_get(
            $module,
            $tool['service'],
            $args,
            $label
        ),
        'get_with_items' => mcpv2_handle_get_with_items(
            $module,
            $tool['service'],
            $tool['rel_service'],
            $tool['rel_method'],
            $tool['rel_key'],
            $args,
            $label
        ),
        'update' => mcpv2_handle_update(
            $module,
            $tool['service'],
            $args,
            $label
        ),
        'create' => mcpv2_handle_create(
            $module,
            $tool['service'],
            $args,
            $tokenUser,
            $tool
        ),
        'list_filtered' => mcpv2_handle_list_filtered(
            $module,
            $tool['service'],
            $args
        ),
        default => throw new InvalidArgumentException("Unknown handler type: {$tool['handler']}"),
    };

    // Post-get side effect (e.g. increment view count)
    if ($tool['handler'] === 'get' && !empty($tool['after_get'])) {
        $afterId = (int) ($args['id'] ?? 0);
        if ($afterId > 0) {
            try {
                $afterArgs = array_merge([$afterId], $tool['after_get_args'] ?? []);
                mcpv2_container($module, $tool['service'])->{$tool['after_get']}(...$afterArgs);
            } catch (\Throwable $afterEx) {
                Logger::getInstance()->warning('after_get side effect failed', [
                    'tool'    => $tool['name'] ?? '',
                    'method'  => $tool['after_get'],
                    'args'    => $afterArgs ?? [],
                    'error'   => $afterEx->getMessage(),
                ]);
            }
        }
    }

    return $result;
}

// ── Dispatch ──────────────────────────────────────────────────────────────────

switch ($method) {
    case 'initialize':
        return mcpv2_ok($id, [
            'protocolVersion' => '2024-11-05',
            'capabilities'    => ['tools' => new stdClass()],
            'serverInfo'      => ['name' => 'aplos-crm', 'version' => '2.0.0'],
        ]);

    case 'ping':
        return mcpv2_ok($id, new stdClass());

    case 'tools/list':
        return mcpv2_ok($id, ['tools' => $allTools]);

    case 'tools/call':
        $toolName = $params['name']      ?? '';
        $toolArgs = $params['arguments'] ?? [];
        try {
            return mcpv2_ok($id, mcpv2_call($toolName, $toolArgs, $toolMap, $tokenUser));
        } catch (InvalidArgumentException $e) {
            return mcpv2_err($id, -32601, $e->getMessage());
        } catch (Throwable $e) {
            return mcpv2_err($id, -32603, 'Internal error: ' . $e->getMessage());
        }

    default:
        return mcpv2_err($id, -32601, "Method not found: {$method}");
}
