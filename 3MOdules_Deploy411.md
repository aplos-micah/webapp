# Deploy Instructions — ITSM, Assets, Knowledge Base

Three new modules to deploy. Each follows the same pattern: upload files → run migration → grant access.

---

## Pre-Flight Checklist

- [ ] Backup the database before running any migrations
- [ ] Confirm the migrations table exists (`/admin/migrations` should load without error)
- [ ] Confirm you have Admin access to grant module entitlements

---

## Module 1: ITSM

### Files to Upload

Upload the entire folder:

```
Application/Module/ITSM/
```

Contains: `module.php`, `Navigation.php`, `Container.php`, `Objects/Ticket.php`,
`Pages/Dashboard/`, `Pages/Tickets/List/`, `Pages/Tickets/New/`, `Pages/Tickets/Details/`,
`Pages/Guide/`, `Api/Tickets/`, `Widgets/TicketSummary.php`, `SQL/`

### Migration

Run via `/admin/migrations`:

```
ITSM — 20260504_itsm_tickets.sql
```

Creates table: `itsm_tickets`

Ticket numbers auto-assign as `TKT-000001`, `TKT-000002`, etc. on first record creation.

### Grant Access

Admin → User Details → Module Access → **ITSM** → select tier → Save

### Verify

- [ ] `/itsm/dashboard` loads with stat cards
- [ ] `/itsm/tickets/list` loads (empty state expected)
- [ ] Create a ticket → auto-assigned ticket number appears
- [ ] `GET /api_v2/itsm/tickets` returns JSON `{ "ok": true, "data": [] }`

---

## Module 2: Assets

### Files to Upload

Upload the entire folder:

```
Application/Module/Assets/
```

Contains: `module.php`, `Navigation.php`, `Container.php`, `Objects/Asset.php`,
`Pages/Dashboard/`, `Pages/Assets/List/`, `Pages/Assets/New/`, `Pages/Assets/Details/`,
`Pages/Guide/`, `Api/Assets/`, `Widgets/AssetSummary.php`, `SQL/`

### Migration

Run via `/admin/migrations`:

```
Assets — 20260504_assets.sql
```

Creates table: `assets`

Asset tags auto-assign as `ASSET-000001`, `ASSET-000002`, etc. on first record creation.

### Grant Access

Admin → User Details → Module Access → **Assets** → select tier → Save

### Verify

- [ ] `/assets/dashboard` loads with stat cards
- [ ] `/assets/assets/list` loads (empty state expected)
- [ ] Create an asset → `ASSET-000001` tag auto-appears on details page
- [ ] Dashboard "Expiring Warranties" panel loads (empty state expected)
- [ ] `GET /api_v2/assets/assets` returns JSON `{ "ok": true, "data": [] }`

---

## Module 3: Knowledge Base (KB)

### Files to Upload

Upload the entire folder:

```
Application/Module/KB/
```

Contains: `module.php`, `Navigation.php`, `Container.php`, `Objects/Article.php`,
`Pages/Articles/List/`, `Pages/Articles/New/`, `Pages/Articles/Details/`,
`Pages/Guide/`, `Api/Articles/`, `SQL/`

### Migration

Run via `/admin/migrations`:

```
KB — 20260504_kb_articles.sql
```

Creates table: `kb_articles`

### Grant Access

Admin → User Details → Module Access → **KB** → select tier → Save

### Verify

- [ ] `/kb/articles/list` loads (empty state expected — defaults to Published filter)
- [ ] Create a Draft article → only visible to author in list
- [ ] Publish the article → visible to all KB users in list
- [ ] Open article details → view count increments on each load
- [ ] `GET /api_v2/kb/articles` returns JSON `{ "ok": true, "data": [] }` (Published only)
- [ ] `GET /api_v2/kb/articles?include_drafts=true` returns Draft articles

---

## Deployment Order (if deploying all three at once)

1. Upload `Application/Module/ITSM/`
2. Upload `Application/Module/Assets/`
3. Upload `Application/Module/KB/`
4. Navigate to `/admin/migrations`
5. Run `20260504_itsm_tickets.sql`
6. Run `20260504_assets.sql`
7. Run `20260504_kb_articles.sql`
8. Grant module access to users via Admin → User Details

---

## Session Keys (for reference)

| Module | Session Key | Navigation Guard |
|--------|-------------|-----------------|
| ITSM   | `module_itsm` | `requiresModuleItsm` |
| Assets | `module_assets` | `requiresModuleAssets` |
| KB     | `module_kb` | `requiresModuleKb` |

Access is managed through the `user_module_access` table. Users without a row for the module are redirected to `/login` (NCND routing).
