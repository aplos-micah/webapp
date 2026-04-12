<?php

/**
 * DashLeads Widget
 *
 * Provides the Leads stat count and a recent-leads tile for the main dashboard.
 * A "Lead" is any contact with lifecycle_stage = 'Lead'.
 */
class DashLeads
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function count(): int
    {
        $row = $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM contacts WHERE lifecycle_stage = 'Lead'"
        );
        return (int) ($row['total'] ?? 0);
    }

    public function recent(int $limit = 8): array
    {
        return $this->db->query(
            "SELECT c.id, c.first_name, c.last_name, c.job_title,
                    c.lead_source, a.name AS account_name
               FROM contacts c
               LEFT JOIN accounts a ON a.id = c.account_id
              WHERE c.lifecycle_stage = 'Lead'
              ORDER BY c.created_at DESC
              LIMIT ?",
            [$limit]
        );
    }

    public function renderTile(): string
    {
        $leads = $this->recent();
        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        if (empty($leads)) {
            return '<div class="dash-panel__empty">'
                 . '<i class="fa-regular fa-bolt" aria-hidden="true"></i>'
                 . '<p>No leads yet. <a href="/crm/contacts/new">Add a contact</a> and mark them as a Lead.</p>'
                 . '</div>';
        }

        $html = '<ul class="dash-widget-list">';
        foreach ($leads as $l) {
            $name   = trim($e($l['first_name']) . ' ' . $e($l['last_name']));
            $sub    = $l['account_name'] ? $e($l['account_name']) : ($l['job_title'] ? $e($l['job_title']) : null);
            $source = $l['lead_source'] ? $e($l['lead_source']) : null;

            $html .= '<li class="dash-widget-list__item">';
            $html .= '<div class="dash-widget-list__main">';
            $html .= '<a href="/crm/contacts/details?id=' . (int)$l['id'] . '" class="dash-widget-list__name">' . $name . '</a>';
            if ($sub) {
                $html .= '<span class="dash-widget-list__sub">' . $sub . '</span>';
            }
            $html .= '</div>';
            if ($source) {
                $html .= '<span class="badge badge--neutral">' . $source . '</span>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '<div class="dash-widget-footer"><a href="/crm/contacts/list">View all contacts</a></div>';

        return $html;
    }
}
