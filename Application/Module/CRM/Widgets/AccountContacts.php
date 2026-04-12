<?php

/**
 * AccountContacts Widget
 *
 * Renders the Contacts related tile for an Account detail page.
 * Displays up to 50 contacts linked to the account with 4 identifying fields:
 * Name, Job Title, Email, and Lifecycle Stage.
 */
class AccountContacts
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * Return all contacts linked to the given account, ordered by last name.
     */
    public function getContacts(int $accountId): array
    {
        return $this->db->query(
            'SELECT id, first_name, last_name, job_title, email, lifecycle_stage
               FROM contacts
              WHERE account_id = ?
              ORDER BY last_name ASC, first_name ASC
              LIMIT 50',
            [$accountId]
        );
    }

    /**
     * Render the tile body HTML.
     * Returns an HTML string ready to be echoed inside a .related-card.
     */
    public function render(int $accountId): string
    {
        $contacts = $this->getContacts($accountId);

        if (empty($contacts)) {
            return '<div class="related-card__empty">'
                 . '<i class="fa-regular fa-address-book" aria-hidden="true"></i>'
                 . '<p>No contacts yet.</p>'
                 . '</div>';
        }

        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        $lifecycleBadgeClass = [
            'Lead'       => 'badge--neutral',
            'MQL'        => 'badge--info',
            'SQL'        => 'badge--warning',
            'Customer'   => 'badge--success',
            'Evangelist' => 'badge--purple',
        ];

        $html  = '<ul class="widget-contact-list">';
        foreach ($contacts as $c) {
            $fullName  = trim($e($c['first_name']) . ' ' . $e($c['last_name']));
            $jobTitle  = $c['job_title']       ? $e($c['job_title'])       : null;
            $email     = $c['email']            ? $e($c['email'])           : null;
            $stage     = $c['lifecycle_stage']  ? $e($c['lifecycle_stage']) : null;
            $badgeCls  = $lifecycleBadgeClass[$c['lifecycle_stage'] ?? ''] ?? 'badge--neutral';

            $html .= '<li class="widget-contact-list__item">';
            $html .= '<div class="widget-contact-list__main">';
            $html .= '<a href="/crm/contacts/details?id=' . (int) $c['id'] . '" class="widget-contact-list__name">'
                   . $fullName . '</a>';
            if ($jobTitle) {
                $html .= '<span class="widget-contact-list__job">' . $jobTitle . '</span>';
            }
            $html .= '</div>';
            $html .= '<div class="widget-contact-list__meta">';
            if ($email) {
                $html .= '<a href="mailto:' . $email . '" class="widget-contact-list__email">'
                       . $email . '</a>';
            }
            if ($stage) {
                $html .= '<span class="badge ' . $badgeCls . ' widget-contact-list__stage">' . $stage . '</span>';
            }
            $html .= '</div>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        $count = count($contacts);
        $html .= '<div class="widget-contact-list__footer">'
               . '<a href="/crm/contacts/list" class="widget-contact-list__all">'
               . $count . ' contact' . ($count !== 1 ? 's' : '') . ' — View all</a>'
               . '</div>';

        return $html;
    }
}
