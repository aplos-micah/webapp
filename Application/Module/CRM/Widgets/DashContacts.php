<?php

/**
 * DashContacts Widget
 *
 * Provides the Contacts stat count and a recent-contacts tile
 * for the main dashboard.
 */
class DashContacts
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function count(): int
    {
        $row = $this->db->queryOne('SELECT COUNT(*) AS total FROM contacts');
        return (int) ($row['total'] ?? 0);
    }

    public function recent(int $limit = 8): array
    {
        return $this->db->query(
            "SELECT c.id, c.first_name, c.last_name, c.job_title,
                    c.lifecycle_stage, a.name AS account_name
               FROM contacts c
               LEFT JOIN accounts a ON a.id = c.account_id
              ORDER BY c.created_at DESC
              LIMIT ?",
            [$limit]
        );
    }

    public function renderTile(): string
    {
        $contacts = $this->recent();
        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        $lifecycleBadge = [
            'Lead'       => 'badge--neutral',
            'MQL'        => 'badge--info',
            'SQL'        => 'badge--warning',
            'Customer'   => 'badge--success',
            'Evangelist' => 'badge--purple',
        ];

        if (empty($contacts)) {
            return '<div class="content-panel__empty">'
                 . '<i class="fa-regular fa-address-book" aria-hidden="true"></i>'
                 . '<p>No contacts yet. <a href="/crm/contacts/new">Add your first contact</a>.</p>'
                 . '</div>';
        }

        $html = '<ul class="record-list">';
        foreach ($contacts as $c) {
            $name    = trim($e($c['first_name']) . ' ' . $e($c['last_name']));
            $sub     = $c['account_name'] ? $e($c['account_name']) : ($c['job_title'] ? $e($c['job_title']) : null);
            $stage   = $c['lifecycle_stage'] ?? '';
            $badge   = $lifecycleBadge[$stage] ?? 'badge--neutral';

            $html .= '<li class="record-list__item">';
            $html .= '<div class="record-list__main">';
            $html .= '<a href="/crm/contacts/details?id=' . (int)$c['id'] . '" class="record-list__name">' . $name . '</a>';
            if ($sub) {
                $html .= '<span class="record-list__sub">' . $sub . '</span>';
            }
            $html .= '</div>';
            if ($stage) {
                $html .= '<span class="badge ' . $badge . '">' . $e($stage) . '</span>';
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '<div class="widget-footer"><a href="/crm/contacts/list">View all contacts</a></div>';

        return $html;
    }
}
