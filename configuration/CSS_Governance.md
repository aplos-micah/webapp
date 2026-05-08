# CSS Governance

How to write, name, and place CSS in the AplosSuite platform.

---

## Core Rule: Platform First and Only

**There are exactly two CSS files. No exceptions.**

| File | Purpose |
|------|---------|
| `public_html/assets/css/main.css` | Generic, reusable components — could be used by any product built on this platform (buttons, badges, cards, forms, tables, alerts, spacing utilities) |
| `public_html/assets/css/app.css` | Platform shell and structural layout — nav, sidebar, page layout, stat cards, tile cards, module-specific component groups |

**Never create:**
- A per-module CSS file (no `itsm.css`, `assets.css`, `kb.css`)
- Inline `style=` attributes for anything that appears more than once
- A `<style>` block inside any PHP template or view
- A third CSS file for any reason

If you need a new style, it goes into one of the two files above or it does not exist.

---

## Display-Descriptive Naming — the Only Allowed Convention

CSS class names describe **what the element looks like or how it behaves**, never what module it belongs to, what content it displays, or what entity it represents.

### Rule

> A class name must make sense in a module it was not written for.

### Examples

| Correct — display-descriptive | Wrong — vertical/content-specific |
|-------------------------------|-----------------------------------|
| `stat-card` | `crm-stat`, `itsm-stat`, `asset-card` |
| `field-list` | `ticket-fields`, `asset-details`, `article-info` |
| `prose-body` | `guide-body`, `article-body`, `kb-content` |
| `prose-list` | `guide-list`, `playbook-steps` |
| `tag-list` | `article-tags`, `kb-tags` |
| `breakdown-list` | `ticket-breakdown`, `status-breakdown` |
| `content-panel` | `itsm-panel`, `assets-panel` |
| `record-list` | `ticket-list`, `asset-list` |
| `step-progress` | `ticket-workflow`, `status-steps` |
| `form-row` | `ticket-form-row`, `asset-row` |
| `detail-layout` | `account-layout`, `asset-layout` |

### Test before naming

Ask: "Could this class appear on a completely different module doing a visually similar thing?" If yes, the name is correct. If the name only makes sense in one context, rename it.

---

## Which File to Edit

| Add to `main.css` | Add to `app.css` |
|-------------------|-----------------|
| Buttons, badges, alerts | Platform shell (top-nav, side-nav) |
| Cards (`.card`, `.profile-card`) | Page layout, split layout |
| Form inputs, form groups, form rows | Stat cards, tile cards, detail layout |
| Tables, table wrappers | Pagination, sort controls |
| Spacing / spacing utilities | List search field |
| Generic prose / text components | Step-progress workflow component |
| Accessibility, focus rings | Breakdown list, record list, metric grid |
| Auth pages (login, register) | Module navigation patterns |

When in doubt: if it could exist in a product with no sidebar at all, it belongs in `main.css`. If it is only meaningful inside the platform shell, it belongs in `app.css`.

---

## Adding a New Class

Before writing any new CSS:

1. **Search both files.** The class may already exist under a display-descriptive name you haven't used yet. (`grep -n "class-name" app.css main.css`)
2. **Check for a near-match.** A modifier on an existing component is better than a new class. (e.g. `content-panels--three` rather than a new grid class)
3. **Name it display-descriptively.** Apply the naming rule above.
4. **Place it next to the most similar existing component** in the correct file — not at the bottom, not in a module-named comment block.
5. **Add a mobile breakpoint** if the new component uses `display: grid` or `display: flex` with more than one column.

---

## Responsive Requirements

Every component that uses a multi-column grid or flex layout **must** have a mobile breakpoint.

| Breakpoint | Behaviour required |
|------------|-------------------|
| `≤ 900px` | Multi-column grids collapse to 1 column. Two-panel layouts stack vertically. |
| `≤ 480px` | Full-width inputs, stacked form rows, any fixed-width overrides removed. |

The platform breakpoints are `900px` and `480px`. Do not introduce new breakpoint values.

Tables that may overflow on small screens must be wrapped in `.table-wrap`:

```html
<div class="table-wrap">
    <table class="data-table">…</table>
</div>
```

`.table-wrap` provides `overflow-x: auto` and `-webkit-overflow-scrolling: touch`.

---

## Existing Platform Components (reference)

Use these before writing anything new.

### Layout
| Class | Description |
|-------|-------------|
| `content-panels` | Two-column responsive grid for side-by-side panels |
| `content-panels--three` | Three-column variant, collapses at 900px |
| `detail-layout` | Two-column page layout (primary + aside), collapses at 1024px |
| `detail-layout__primary` / `__aside` | Columns within `detail-layout` |
| `stats-grid` | Four-column stat card row, goes 2×2 at 900px |
| `form-row` | Horizontal flex row for inline form groups |
| `form-group--grow` | `flex: 1` form group (used in search bars) |
| `form-group--half` | Half-width form group within a `form-row` |

### Cards & Panels
| Class | Description |
|-------|-------------|
| `card` | White rounded card with shadow |
| `content-panel` | Flex column panel used inside `content-panels` |
| `stat-card` | Icon + value + label stat summary card |
| `tile-card` | Draggable related-record card (CRM detail pages) |
| `profile-card` | Single-entity detail card |
| `profile-card__title` | Section heading inside a profile card |

### Tables
| Class | Description |
|-------|-------------|
| `table-wrap` | Scrollable table wrapper (`overflow-x: auto`) |
| `data-table` | Full-width styled table with navy header |
| `table-link` | Inline anchor style inside table cells |
| `sort-link` / `sort-icon` | Sortable column header link and chevron icon |

### Forms
| Class | Description |
|-------|-------------|
| `form-group` | Vertical label + input stack |
| `form-label` | Label text styling |
| `form-required` | Required field asterisk (`*`) |
| `form-hint` | Helper text below an input |
| `form-error` | Inline validation error message |
| `form-actions` | Right-aligned row of submit / cancel buttons |
| `edit-section` | Subtle background wrapper for edit-mode form fields |
| `input` | Applied to any `<input>`, `<select>`, or `<textarea>` |

### Lists & Data Display
| Class | Description |
|-------|-------------|
| `field-list` / `field-list__row` | Read-only label/value pairs in a bordered list |
| `breakdown-list` / `breakdown-list__item` | Status/type breakdown with count and percentage |
| `record-list` / `record-list__item` | Compact linked record list used in dashboard panels |
| `metric-grid` | Small metric count grid used in widgets |
| `tag-list` | Horizontal row of badge-style tags |

### Prose & Long-form Content
| Class | Description |
|-------|-------------|
| `prose-body` | Wrapper for paragraphs of long-form content. Adds line-height and spacing. |
| `prose-list` | Styled `ol` or `ul` for numbered/bulleted prose lists |

### Navigation & Headers
| Class | Description |
|-------|-------------|
| `dash-header` | Page header row with title on left, actions on right |
| `dash-header__title` | H1 inside a dash-header |
| `dash-header__sub` | Subtitle/meta line below the title |
| `pagination` / `pagination__controls` | Page navigation row |

### Badges & Status
| Class | Description |
|-------|-------------|
| `badge` | Base inline status pill |
| `badge--success` / `--warning` / `--info` / `--neutral` | Colour modifiers |

---

## What Not to Do

- Do not reference a module name in a class (`itsm-`, `crm-`, `kb-`, `assets-`)
- Do not reference content type in a class (`article-`, `ticket-`, `guide-`, `asset-`)
- Do not add a class that duplicates an existing one under a different name
- Do not write `style="…"` on any element that already has a CSS class available
- Do not write a breakpoint at any pixel value other than `900px` or `480px`
- Do not create a new CSS file
