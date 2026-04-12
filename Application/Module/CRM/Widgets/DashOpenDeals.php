<?php

/**
 * DashOpenDeals Widget
 *
 * Provides the Open Deals stat count and a recent open-opportunities tile
 * for the main dashboard.  "Open" = not Closed Won and not Closed Lost.
 */
class DashOpenDeals
{
    private DB $db;

    private const CLOSED = ['Closed Won', 'Closed Lost'];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function count(): int
    {
        $row = $this->db->queryOne(
            "SELECT COUNT(*) AS total
               FROM opportunities
              WHERE stage NOT IN ('Closed Won','Closed Lost')"
        );
        return (int) ($row['total'] ?? 0);
    }

    public function totalValue(): float
    {
        $row = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount), 0) AS total_value
               FROM opportunities
              WHERE stage NOT IN ('Closed Won','Closed Lost')"
        );
        return (float) ($row['total_value'] ?? 0);
    }

    public function recent(int $limit = 8): array
    {
        return $this->db->query(
            "SELECT o.id, o.opportunity_name, o.stage, o.amount, o.close_date,
                    a.name AS account_name
               FROM opportunities o
               LEFT JOIN accounts a ON a.id = o.account_id
              WHERE o.stage NOT IN ('Closed Won','Closed Lost')
              ORDER BY o.created_at DESC
              LIMIT ?",
            [$limit]
        );
    }

    public function renderTile(): string
    {
        $deals = $this->recent();
        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        $stageBadge = [
            'New'         => 'badge--neutral',
            'Building'    => 'badge--info',
            'Review'      => 'badge--info',
            'Quote'       => 'badge--warning',
            'Negotiating' => 'badge--purple',
        ];

        if (empty($deals)) {
            return '<div class="dash-panel__empty">'
                 . '<i class="fa-regular fa-handshake" aria-hidden="true"></i>'
                 . '<p>No open deals. <a href="/crm/opportunities/new">Create one</a>.</p>'
                 . '</div>';
        }

        $html = '<ul class="dash-widget-list">';
        foreach ($deals as $d) {
            $badge = $stageBadge[$d['stage'] ?? ''] ?? 'badge--neutral';
            $amount = $d['amount'] !== null
                ? 'USD ' . number_format((float) $d['amount'], 0)
                : null;

            $html .= '<li class="dash-widget-list__item">';
            $html .= '<div class="dash-widget-list__main">';
            $html .= '<a href="/crm/opportunities/details?id=' . (int)$d['id'] . '" class="dash-widget-list__name">'
                   . $e($d['opportunity_name']) . '</a>';
            if ($d['account_name']) {
                $html .= '<span class="dash-widget-list__sub">' . $e($d['account_name']) . '</span>';
            }
            $html .= '</div>';
            $html .= '<div class="dash-widget-list__right">';
            $html .= '<span class="badge ' . $badge . '">' . $e($d['stage']) . '</span>';
            if ($amount) {
                $html .= '<span class="dash-widget-list__amount">' . $amount . '</span>';
            }
            $html .= '</div>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '<div class="dash-widget-footer"><a href="/crm/opportunities/list">View all opportunities</a></div>';

        return $html;
    }
}
