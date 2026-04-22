<?php

/**
 * POST /mcp
 *
 * MCP Streamable HTTP endpoint (protocol 2024-11-05).
 * Accepts JSON-RPC 2.0 requests, returns application/json responses.
 *
 * Auth: set MCP_API_KEY in .env to require Authorization: Bearer <key>.
 *       Leave MCP_API_KEY empty to disable auth (not recommended in production).
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');

// ── Auth ──────────────────────────────────────────────────────────────────────

$configuredKey = getenv('MCP_API_KEY');
if ($configuredKey !== false && $configuredKey !== '') {
    // nginx often strips Authorization — also accept X-Mcp-Key as a fallback
    $provided = $_SERVER['HTTP_X_MCP_KEY']
        ?? $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '')
        ?? '';

    // X-Mcp-Key sends the raw key; Authorization sends "Bearer <key>"
    if (isset($_SERVER['HTTP_X_MCP_KEY'])) {
        $provided = "Bearer {$provided}";
    }

    if (!hash_equals("Bearer {$configuredKey}", $provided)) {
        return Response::json(
            ['jsonrpc' => '2.0', 'id' => null, 'error' => ['code' => -32001, 'message' => 'Unauthorized']],
            401
        );
    }
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

// Notifications (no id) require no JSON-RPC response
if ($id === null) {
    http_response_code(202);
    exit;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function mcp_ok(mixed $id, mixed $result): Response
{
    return Response::json(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
}

function mcp_err(mixed $id, int $code, string $message): Response
{
    return Response::json(['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]]);
}

function tool_text(string $text): array
{
    return ['content' => [['type' => 'text', 'text' => $text]]];
}

function json_pretty(mixed $data): string
{
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// ── Tool definitions ──────────────────────────────────────────────────────────

function mcp_tools(): array
{
    $pagination = [
        'search' => ['type' => 'string',  'description' => 'Text search filter'],
        'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
        'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
    ];

    return [
        [
            'name'        => 'list_accounts',
            'description' => 'Search and list CRM accounts. Filterable by name, account number, type, industry, status, or website.',
            'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        ],
        [
            'name'        => 'get_account',
            'description' => 'Get a single CRM account by its numeric ID.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => ['id' => ['type' => 'integer', 'description' => 'Account ID']],
                'required'   => ['id'],
            ],
        ],
        [
            'name'        => 'list_contacts',
            'description' => 'Search and list CRM contacts. Filterable by first name, last name, email, job title, or account name.',
            'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        ],
        [
            'name'        => 'get_contact',
            'description' => 'Get a single CRM contact by its numeric ID.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => ['id' => ['type' => 'integer', 'description' => 'Contact ID']],
                'required'   => ['id'],
            ],
        ],
        [
            'name'        => 'list_opportunities',
            'description' => 'Search and list CRM opportunities. Filterable by opportunity name, stage, forecast category, or account name.',
            'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        ],
        [
            'name'        => 'get_opportunity',
            'description' => 'Get a single CRM opportunity by ID, including its line items.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => ['id' => ['type' => 'integer', 'description' => 'Opportunity ID']],
                'required'   => ['id'],
            ],
        ],
        [
            'name'        => 'list_products',
            'description' => 'Search and list product definitions. Filterable by product name, SKU, product family, type, or lifecycle status.',
            'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        ],
        [
            'name'        => 'get_product',
            'description' => 'Get a single product definition by its numeric ID.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => ['id' => ['type' => 'integer', 'description' => 'Product ID']],
                'required'   => ['id'],
            ],
        ],
        [
            'name'        => 'read_logs',
            'description' => 'Read recent application log entries from storage/logs/app.log.',
            'inputSchema' => [
                'type'       => 'object',
                'properties' => [
                    'level' => ['type' => 'string', 'description' => 'Filter by level: ERROR, WARNING, or INFO'],
                    'limit' => ['type' => 'integer', 'description' => 'Number of entries to return (default 50, max 500)'],
                ],
            ],
        ],
    ];
}

// ── Tool handlers ─────────────────────────────────────────────────────────────

function mcp_handle_list(string $service, array $args): array
{
    $search = trim($args['search'] ?? '');
    $limit  = min(100, max(1, (int) ($args['limit']  ?? 20)));
    $offset = max(0,          (int) ($args['offset'] ?? 0));

    $obj   = CRMContainer::get($service);
    $total = $obj->count($search);
    $data  = $obj->findAll($limit, $offset, $search);

    return tool_text(json_pretty([
        'ok'   => true,
        'data' => $data,
        'meta' => ['total' => $total, 'limit' => $limit, 'offset' => $offset],
    ]));
}

function mcp_handle_get(string $service, array $args, string $label): array
{
    $id = (int) ($args['id'] ?? 0);
    if ($id <= 0) {
        return tool_text(json_pretty(['ok' => false, 'error' => 'id must be a positive integer']));
    }

    $record = CRMContainer::get($service)->findById($id);

    return $record
        ? tool_text(json_pretty(['ok' => true, 'data' => $record]))
        : tool_text(json_pretty(['ok' => false, 'error' => "{$label} not found."]));
}

function mcp_handle_get_opportunity(array $args): array
{
    $id = (int) ($args['id'] ?? 0);
    if ($id <= 0) {
        return tool_text(json_pretty(['ok' => false, 'error' => 'id must be a positive integer']));
    }

    $record = CRMContainer::get('opportunity')->findById($id);
    if (!$record) {
        return tool_text(json_pretty(['ok' => false, 'error' => 'Opportunity not found.']));
    }

    $record['line_items'] = CRMContainer::get('line_item')->findByOpportunity($id);

    return tool_text(json_pretty(['ok' => true, 'data' => $record]));
}

function mcp_handle_read_logs(array $args): array
{
    $level   = strtoupper(trim($args['level'] ?? ''));
    $limit   = min(500, max(1, (int) ($args['limit'] ?? 50)));
    $logFile = Logger::logFile();

    if (!is_file($logFile)) {
        return tool_text('Log file not found.');
    }

    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return tool_text('Could not read log file.');
    }

    $results = [];
    foreach (array_reverse($lines) as $line) {
        if (count($results) >= $limit) break;
        $entry = json_decode($line, true);
        if ($entry === null) continue;
        if ($level !== '' && ($entry['level'] ?? '') !== $level) continue;
        $results[] = $entry;
    }

    return tool_text(json_pretty($results));
}

function mcp_call(string $name, array $args): array
{
    return match ($name) {
        'list_accounts'      => mcp_handle_list('account',     $args),
        'get_account'        => mcp_handle_get('account',      $args, 'Account'),
        'list_contacts'      => mcp_handle_list('contact',     $args),
        'get_contact'        => mcp_handle_get('contact',      $args, 'Contact'),
        'list_opportunities' => mcp_handle_list('opportunity', $args),
        'get_opportunity'    => mcp_handle_get_opportunity($args),
        'list_products'      => mcp_handle_list('product',     $args),
        'get_product'        => mcp_handle_get('product',      $args, 'Product'),
        'read_logs'          => mcp_handle_read_logs($args),
        default              => throw new InvalidArgumentException("Unknown tool: {$name}"),
    };
}

// ── Dispatch ──────────────────────────────────────────────────────────────────

switch ($method) {
    case 'initialize':
        return mcp_ok($id, [
            'protocolVersion' => '2024-11-05',
            'capabilities'    => ['tools' => new stdClass()],
            'serverInfo'      => ['name' => 'aplos-crm', 'version' => '1.0.0'],
        ]);

    case 'ping':
        return mcp_ok($id, new stdClass());

    case 'tools/list':
        return mcp_ok($id, ['tools' => mcp_tools()]);

    case 'tools/call':
        $toolName = $params['name']      ?? '';
        $toolArgs = $params['arguments'] ?? [];
        try {
            return mcp_ok($id, mcp_call($toolName, $toolArgs));
        } catch (InvalidArgumentException $e) {
            return mcp_err($id, -32601, $e->getMessage());
        } catch (Throwable $e) {
            return mcp_err($id, -32603, 'Internal error: ' . $e->getMessage());
        }

    default:
        return mcp_err($id, -32601, "Method not found: {$method}");
}
