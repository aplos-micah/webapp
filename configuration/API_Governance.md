# API Governance

How to build, name, and document API endpoints and MCP tools in AplosSuite.

---

## Two API Surfaces

Every module exposes the same data through two surfaces:

| Surface | Path | Auth | Consumer |
|---------|------|------|----------|
| REST API v2 | `GET /api_v2/{module}/{endpoint}` | Session cookie or OAuth Bearer token | Browser, scripts, integrations |
| MCP v2 | `POST /api/mcp_v2` | OAuth Bearer token (`X-MCP-Key` header) | Claude AI, MCP clients |

Both surfaces read from the same `Objects/` business logic. The API controller is the only place that handles HTTP concerns — the Object class handles all data access.

---

## File Structure

Every API endpoint lives in:

```
Application/Module/{Module}/Api/{Endpoint}/
├── controller.php   — request handling, response construction
├── meta.php         — human-readable description and parameter list
└── mcp.php          — MCP tool definitions for this endpoint
```

The router discovers endpoints automatically by scanning `Module/{Name}/Api/`. No registration is needed.

---

## URL Convention

```
/api_v2/{module}/{endpoint}
```

| Segment | Rule | Example |
|---------|------|---------|
| `{module}` | Lowercase module name | `itsm`, `crm`, `assets`, `kb` |
| `{endpoint}` | Lowercase plural entity name | `tickets`, `assets`, `articles` |

All API endpoints are `GET` only. Mutations (create, update) are performed through MCP tools or the page UI. The REST API is read-only.

### Examples

```
GET /api_v2/itsm/tickets
GET /api_v2/itsm/tickets?id=42
GET /api_v2/assets/assets
GET /api_v2/kb/articles?status=Published
GET /api_v2/crm/accounts?search=acme
```

---

## Response Envelope

Every response uses the same JSON envelope. Never return a bare array or a non-conforming shape.

### Success — list

```json
{
  "ok": true,
  "data": [ … ],
  "meta": {
    "total": 142,
    "limit": 20,
    "offset": 0
  }
}
```

### Success — single record

```json
{
  "ok": true,
  "data": { … }
}
```

### Error

```json
{
  "ok": false,
  "error": "Human-readable message."
}
```

HTTP status codes used: `200` (success), `401` (unauthorized), `404` (not found), `500` (unexpected server error).

---

## controller.php Pattern

```php
<?php
/**
 * GET /api_v2/{module}/{endpoint}
 *
 * Params:
 *   id     int    — single record by ID
 *   search string — free-text filter
 *   status string — filter by status
 *   limit  int    — max records (default 20, max 100)
 *   offset int    — pagination offset
 */

$obj = {Module}Container::get('{entity}');

// Single record
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $record = $obj->findById($id);
    return $record
        ? Response::json(['ok' => true, 'data' => $record])
        : Response::json(['ok' => false, 'error' => '{Entity} not found.'], 404);
}

// List
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$limit  = min(100, max(1, (int) ($_GET['limit']  ?? 20)));
$offset = max(0,           (int) ($_GET['offset'] ?? 0));

return Response::json([
    'ok'   => true,
    'data' => $obj->findAll($limit, $offset, $search, 'created_at', 'desc', $status),
    'meta' => [
        'total'  => $obj->count($search, $status),
        'limit'  => $limit,
        'offset' => $offset,
    ],
]);
```

**Rules:**
- Always use `Response::json()` — never `echo json_encode()` directly
- Always `return` the Response — never call `->send()` inside a controller
- Clamp `limit` to `min(100, max(1, …))` — never allow unbounded queries
- Clamp `offset` to `max(0, …)` — never allow negative offsets
- Validate enum filter values against the Object's constants before passing to `findAll`

---

## meta.php Pattern

```php
<?php

return [
    'description' => 'One-sentence description of what this endpoint returns.',
    'method'      => 'GET',
    'params'      => [
        ['name' => 'id',     'type' => 'integer', 'description' => 'Return a single record by ID'],
        ['name' => 'search', 'type' => 'string',  'description' => 'Filter by …'],
        ['name' => 'status', 'type' => 'string',  'description' => 'Filter by status: Val1, Val2'],
        ['name' => 'limit',  'type' => 'integer', 'description' => 'Records per page (default 20, max 100)'],
        ['name' => 'offset', 'type' => 'integer', 'description' => 'Pagination offset (default 0)'],
    ],
];
```

---

## mcp.php — MCP Tool Definitions

Each endpoint registers exactly **four MCP tools**: `list_`, `get_`, `create_`, `update_`. Use singular entity name in the tool name.

### Tool naming

| Tool | Pattern | Example |
|------|---------|---------|
| List | `list_{entity}` | `list_tickets`, `list_assets`, `list_articles` |
| Get | `get_{entity}` | `get_ticket`, `get_asset`, `get_article` |
| Create | `create_{entity}` | `create_ticket`, `create_asset`, `create_article` |
| Update | `update_{entity}` | `update_ticket`, `update_asset`, `update_article` |

### Handlers

Each tool definition maps to a handler string that the MCP dispatcher resolves:

| `handler` value | What it does |
|-----------------|-------------|
| `'list'` | Calls `findAll()` with filter params |
| `'get'` | Calls `findById($id)` |
| `'create'` | Calls `create($data)` |
| `'update'` | Calls `update($id, $data)` |

### `follow_up` field

Only add `follow_up` to `create_` tools where a logical next step exists. The value is a plain-text string appended to the tool response to guide Claude toward the next action.

```php
'follow_up' => 'The ticket was created. Ask if the user would like to assign it or set a priority.',
```

Do not add `follow_up` to list, get, or update tools.

### mcp.php skeleton

```php
<?php

$pagination = [
    'search' => ['type' => 'string',  'description' => 'Filter by …'],
    'status' => ['type' => 'string',  'description' => 'Filter by status: Val1, Val2'],
    'limit'  => ['type' => 'integer', 'description' => 'Max records to return (default 20, max 100)'],
    'offset' => ['type' => 'integer', 'description' => 'Records to skip for pagination (default 0)'],
];

return [
    [
        'name'        => 'list_{entities}',
        'description' => 'Search and list {entities}.',
        'inputSchema' => ['type' => 'object', 'properties' => $pagination],
        'handler'     => 'list',
        'service'     => '{entity}',
    ],
    [
        'name'        => 'get_{entity}',
        'description' => 'Get a single {entity} by its numeric ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => ['id' => ['type' => 'integer', 'description' => '{Entity} ID']],
            'required'   => ['id'],
        ],
        'handler' => 'get',
        'service' => '{entity}',
        'label'   => '{Entity}',
    ],
    [
        'name'        => 'create_{entity}',
        'description' => 'Create a new {entity}.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'description' => '{Entity} name (required)'],
                // … additional fields
            ],
            'required' => ['name'],
        ],
        'handler'   => 'create',
        'service'   => '{entity}',
        'follow_up' => 'The {entity} was created. Ask if …',
    ],
    [
        'name'        => 'update_{entity}',
        'description' => 'Update an existing {entity} by ID.',
        'inputSchema' => [
            'type'       => 'object',
            'properties' => [
                'id'   => ['type' => 'integer', 'description' => '{Entity} ID to update (required)'],
                'name' => ['type' => 'string',  'description' => 'Updated name'],
                // … additional fields
            ],
            'required' => ['id'],
        ],
        'handler' => 'update',
        'service' => '{entity}',
        'label'   => '{Entity}',
    ],
];
```

---

## Authentication

### REST API (browser / session)

The router checks `isLoggedIn()` before dispatching any `api_v2/*` request. Module access rules from `module.php` are enforced automatically — no additional auth code is needed in `controller.php`.

### MCP v2 (Claude / external clients)

MCP requests are authenticated via OAuth Bearer token passed in the `X-MCP-Key` header. The `Mcp_v2/controller.php` validates the token using `OAuthServer::validateToken()` before dispatching any tool. Module access is enforced using the token user's `module_tiers` map.

Never manually check auth inside an API controller or MCP tool handler — the dispatcher handles it.

---

## What Not to Do

- Do not use `POST`, `PUT`, `PATCH`, or `DELETE` for REST endpoints — the API is GET/read-only
- Do not return a bare array — always use the `{ ok, data, meta }` envelope
- Do not call `exit` or `die` in a controller — always `return Response::json(…)`
- Do not put business logic (queries, validation) in the API controller — it belongs in the Object class
- Do not add more than four MCP tools per endpoint (`list_`, `get_`, `create_`, `update_`)
- Do not add `follow_up` to anything other than a `create_` tool
