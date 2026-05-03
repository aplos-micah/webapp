# MCP Server — Setup & Usage Guide

## What Was Built

A **Model Context Protocol (MCP) server** was added to AplosCRM. It exposes your CRM data to Claude (Desktop, Code, or any MCP-compatible client) so you can query accounts, contacts, opportunities, products, and logs conversationally.

The endpoint lives at:
```
POST https://yourdomain.com/api/mcp_v2
```

It is routed through the existing PHP router alongside the `api/` track — no new web server config required.

---

## Files Added / Changed

| File | What Changed |
|---|---|
| `Application/Mcp/controller.php` | New — full MCP JSON-RPC handler with all 9 tools |
| `Application/router.php` | Added `mcp` route track (6 lines) |
| `configuration/.env` | Added `MCP_API_KEY` |
| `configuration/.env.example` | Added `MCP_API_KEY` placeholder |

---

## Step 1 — Deploy to Your Web Server

Push or upload the changed files to your server. The minimum set:

- `Application/Mcp/controller.php` *(new file)*
- `Application/router.php` *(updated)*

Then add `MCP_API_KEY` to your **production** `.env`:

```
MCP_API_KEY=6ea565ca9959371086dc828691a279f54556d8956bc4852a087de55457e24aba
```

> Your development `.env` already has this key. Use the same value on production, or generate a fresh one with `openssl rand -hex 32` if you prefer separate keys per environment.

---

## Step 2 — Test the Endpoint

Before connecting Claude, confirm the endpoint is live. Run this from your terminal (replace the domain):

```bash
curl -s -X POST https://yourdomain.com/api/mcp_v2 \
  -H "Content-Type: application/json" \
  -H "X-Mcp-Key: 6ea565ca9959371086dc828691a279f54556d8956bc4852a087de55457e24aba" \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2024-11-05","capabilities":{},"clientInfo":{"name":"test","version":"1.0"}}}'
```

Expected response:
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "protocolVersion": "2024-11-05",
    "capabilities": { "tools": {} },
    "serverInfo": { "name": "aplos-crm", "version": "1.0.0" }
  }
}
```

To list available tools:
```bash
curl -s -X POST https://yourdomain.com/api/mcp_v2 \
  -H "Content-Type: application/json" \
  -H "X-Mcp-Key: 6ea565ca9959371086dc828691a279f54556d8956bc4852a087de55457e24aba" \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}}'
```

---

## Step 3 — Connect Claude Desktop

Edit `~/Library/Application Support/Claude/claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "aplos-crm": {
      "url": "https://yourdomain.com/api/mcp_v2",
      "headers": {
        "X-Mcp-Key": "6ea565ca9959371086dc828691a279f54556d8956bc4852a087de55457e24aba"
      }
    }
  }
}
```

Restart Claude Desktop. You should see **aplos-crm** appear in the tools panel.

---

## Step 4 — Connect Claude Code (CLI)

Run once to register the server:

```bash
claude mcp add aplos-crm \
  --url https://yourdomain.com/api/mcp_v2 \
  --header "X-Mcp-Key: 6ea565ca9959371086dc828691a279f54556d8956bc4852a087de55457e24aba"
```

Verify it is registered:
```bash
claude mcp list
```

---

## Available Tools

Once connected, Claude can use these tools automatically based on your questions:

| Tool | What it does |
|---|---|
| `list_accounts` | Search/paginate accounts (filter by name, type, industry, status, website) |
| `get_account` | Single account by ID |
| `list_contacts` | Search/paginate contacts (filter by name, email, job title, account) |
| `get_contact` | Single contact by ID |
| `list_opportunities` | Search/paginate opportunities (filter by name, stage, forecast, account) |
| `get_opportunity` | Single opportunity by ID, including line items |
| `list_products` | Search/paginate product definitions (filter by name, SKU, family, type) |
| `get_product` | Single product by ID |
| `read_logs` | Recent log entries, filterable by level (ERROR / WARNING / INFO) |

All list tools support `search`, `limit` (max 100), and `offset` for pagination.

---

## Example Prompts

Once connected you can ask Claude things like:

- *"Show me all open opportunities over $50k"*
- *"Who are the contacts at Acme Corp?"*
- *"List any ERROR logs from the last 50 entries"*
- *"What products do we have in the Software family?"*
- *"Get me opportunity #42 with its line items"*

---

## Security Notes

- The `MCP_API_KEY` is the only thing protecting this endpoint — treat it like a password.
- The endpoint is HTTPS-only in production (your web server should enforce this).
- The key is never logged by the app (it lives only in `.env` and your MCP client config).
- To rotate the key: generate a new one with `openssl rand -hex 32`, update `.env` on the server, and update the `Authorization` header in your Claude config.
- To disable the MCP endpoint entirely: remove `MCP_API_KEY` from `.env` — requests will be rejected with 401.

> **Important:** If you ever accidentally commit `.env` to git, rotate the key immediately.
