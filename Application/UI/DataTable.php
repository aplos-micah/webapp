<?php

/**
 * DataTable
 *
 * Renders a responsive data table from a column definition array.
 * On desktop: standard navy-header table with optional sort links.
 * On mobile (≤480px): each row stacks into a card via CSS data-label pattern.
 *
 * Returns HTML for the <table> element or an empty-state block only.
 * The caller (view) is responsible for the surrounding card wrapper and pagination.
 *
 * Column definition keys:
 *   label   string    — <th> text and data-label value on mobile (required)
 *   key     string    — row array key to read the cell value from
 *   sort    string    — enables a sort link on the header; omit for non-sortable columns
 *   primary bool      — marks the mobile card header cell; defaults true on first column
 *   href    callable  — fn($row): string — wraps cell value in a table-link anchor
 *   badge   array     — ['value' => 'badge--class'] map — renders value as a badge
 *   date    bool      — renders the first 10 characters of the value (YYYY-MM-DD)
 *   render  callable  — fn($row, $e): string — full custom override; use for complex cells
 *
 * Usage:
 *   // View provides the card wrapper; DataTable provides the table or empty state.
 *
 *   $tableHtml = DataTable::render([
 *       'columns'     => [
 *           ['label' => 'Name',   'key' => 'name', 'sort' => 'name', 'primary' => true,
 *            'href' => fn($r) => '/crm/accounts/details?id=' . $r['id']],
 *           ['label' => 'Status', 'key' => 'status', 'sort' => 'status',
 *            'badge' => ['Active' => 'badge--success', 'Inactive' => 'badge--neutral']],
 *           ['label' => 'Created','key' => 'created_at', 'sort' => 'created_at', 'date' => true],
 *       ],
 *       'rows'        => $rows,
 *       'sort'        => $sort,
 *       'dir'         => $dir,
 *       'qs'          => $qs,
 *       'has_filters' => $search !== '',
 *       'empty'       => ['icon' => 'fa-regular fa-building', 'message' => 'No accounts yet.',
 *                         'link' => ['href' => '/crm/accounts/new', 'text' => 'Add your first account']],
 *   ]);
 */
class DataTable
{
    // =========================================================================
    // Public API
    // =========================================================================

    public static function render(array $config): string
    {
        $columns       = $config['columns']        ?? [];
        $rows          = $config['rows']           ?? [];
        $sort          = $config['sort']           ?? '';
        $dir           = $config['dir']            ?? 'asc';
        $qs            = $config['qs']             ?? null;
        $hasFilters    = $config['has_filters']    ?? false;
        $empty         = $config['empty']          ?? [];
        $filteredEmpty = $config['filtered_empty'] ?? 'No results match your filters.';
        $download      = $config['download']       ?? true;
        $allRows       = $config['all_rows']       ?? [];

        if (empty($rows)) {
            return $hasFilters
                ? self::filteredEmptyState($filteredEmpty)
                : self::emptyState($empty);
        }

        $tableHtml  = self::table($columns, $rows, $sort, $dir, $qs);
        $exportRows = !empty($allRows) ? $allRows : $rows;
        $filename   = is_string($download) ? $download : 'export';
        $tableHtml  = self::downloadHtml($columns, $exportRows, $filename) . $tableHtml;

        return $tableHtml;
    }

    // =========================================================================
    // Table
    // =========================================================================

    private static function table(
        array $columns,
        array $rows,
        string $sort,
        string $dir,
        ?callable $qs
    ): string {
        $e = self::escaper();

        $html  = '<table class="data-table"><thead><tr>';

        foreach ($columns as $col) {
            $label   = $col['label'] ?? '';
            $sortKey = $col['sort']  ?? null;

            if ($sortKey !== null && $qs !== null) {
                $newDir = ($sort === $sortKey && $dir === 'asc') ? 'desc' : 'asc';
                $href   = $qs(['sort' => $sortKey, 'dir' => $newDir, 'page' => 1]);
                $icon   = self::sortIcon($sortKey, $sort, $dir);
                $html  .= '<th><a href="' . $e($href) . '" class="sort-link">'
                        . $e($label) . ' ' . $icon . '</a></th>';
            } else {
                $html .= '<th>' . $e($label) . '</th>';
            }
        }

        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($columns as $i => $col) {
                $label     = $col['label']   ?? '';
                $primary   = $col['primary'] ?? ($i === 0);
                $labelAttr = $primary ? '' : ' data-label="' . $e($label) . '"';
                $html     .= '<td' . $labelAttr . '>' . self::renderCell($col, $row, $e) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    // =========================================================================
    // Cell rendering
    // =========================================================================

    private static function renderCell(array $col, array $row, callable $e): string
    {
        // Custom callable overrides all declarative types
        if (isset($col['render'])) {
            return ($col['render'])($row, $e);
        }

        $key   = $col['key'] ?? '';
        $value = $row[$key]  ?? '';

        // Link — wraps the cell value in a table-link anchor
        if (isset($col['href'])) {
            $href = ($col['href'])($row);
            return '<a href="' . $e($href) . '" class="table-link">'
                 . $e($value !== '' && $value !== null ? $value : '—') . '</a>';
        }

        // Badge — looks up badge class from a value→class map
        if (isset($col['badge'])) {
            if ($value === '' || $value === null) return '—';
            $class = $col['badge'][$value] ?? 'badge--neutral';
            return '<span class="badge ' . $e($class) . '">' . $e($value) . '</span>';
        }

        // Date — renders first 10 characters (YYYY-MM-DD)
        if (!empty($col['date'])) {
            $date = substr((string) $value, 0, 10);
            return $e($date ?: '—');
        }

        // Default — plain escaped text
        return $e($value !== '' && $value !== null ? $value : '—');
    }

    // =========================================================================
    // Empty states
    // =========================================================================

    private static function emptyState(array $empty): string
    {
        $e       = self::escaper();
        $icon    = $empty['icon']    ?? 'fa-regular fa-folder-open';
        $message = $empty['message'] ?? 'No records found.';
        $link    = $empty['link']    ?? null;

        $inner = '<i class="' . $e($icon) . '" aria-hidden="true"></i><p>' . $e($message);
        if ($link) {
            $inner .= ' <a href="' . $e($link['href']) . '">' . $e($link['text']) . '</a>';
        }
        $inner .= '</p>';

        return '<div class="content-panel__empty">' . $inner . '</div>';
    }

    private static function filteredEmptyState(string $message): string
    {
        $e = self::escaper();
        return '<div class="content-panel__empty">'
             . '<i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>'
             . '<p>' . $e($message) . '</p>'
             . '</div>';
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    // =========================================================================
    // Download
    // =========================================================================

    private static function downloadHtml(array $columns, array $allRows, string $filename): string
    {
        $e    = self::escaper();
        $csv  = self::buildCsv($columns, $allRows);
        $href = 'data:text/csv;charset=utf-8;base64,' . base64_encode($csv);

        return '<div class="data-table-toolbar">'
             . '<a href="' . $href . '" download="' . $e($filename . '.csv') . '" class="btn btn--ghost btn--sm">'
             . '<i class="fa-solid fa-download" aria-hidden="true"></i> Download CSV'
             . '</a>'
             . '</div>';
    }

    private static function buildCsv(array $columns, array $rows): string
    {
        $lines = [];

        // Header row
        $headers = array_map(fn($col) => self::csvField($col['label'] ?? ''), $columns);
        $lines[] = implode(',', $headers);

        // Data rows
        foreach ($rows as $row) {
            $cells = [];
            foreach ($columns as $col) {
                $key   = $col['key'] ?? '';
                $value = $row[$key]  ?? '';

                if (!empty($col['date'])) {
                    $value = substr((string) $value, 0, 10);
                }

                $cells[] = self::csvField((string) ($value ?? ''));
            }
            $lines[] = implode(',', $cells);
        }

        // UTF-8 BOM + CRLF line endings (Excel compatibility)
        return "\xEF\xBB\xBF" . implode("\r\n", $lines);
    }

    private static function csvField(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private static function sortIcon(string $col, string $sort, string $dir): string
    {
        if ($sort !== $col) {
            return '<i class="fa-solid fa-sort sort-icon sort-icon--idle" aria-hidden="true"></i>';
        }
        $icon = $dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        return '<i class="fa-solid ' . $icon . ' sort-icon sort-icon--active" aria-hidden="true"></i>';
    }

    private static function escaper(): callable
    {
        return fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
