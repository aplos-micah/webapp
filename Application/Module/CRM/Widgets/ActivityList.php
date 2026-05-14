<?php

class ActivityList
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function renderForAccount(int $accountId): string
    {
        return $this->render('account_id', $accountId, '/crm/activities/new?account_id=' . $accountId);
    }

    public function renderForContact(int $contactId): string
    {
        return $this->render('contact_id', $contactId, '/crm/activities/new?contact_id=' . $contactId);
    }

    public function renderForOpportunity(int $opportunityId): string
    {
        return $this->render('opportunity_id', $opportunityId, '/crm/activities/new?opportunity_id=' . $opportunityId);
    }

    private function render(string $field, int $id, string $logUrl): string
    {
        $rows = $this->db->query(
            "SELECT a.id, a.activity_date, a.outcome, a.cost, a.duration_minutes,
                    t.name AS type_name
               FROM crm_activities a
               JOIN crm_activity_types t ON t.id = a.activity_type_id
              WHERE a.{$field} = ?
              ORDER BY a.activity_date DESC
              LIMIT 5",
            [$id]
        );

        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        $outcomeBadge = [
            'Positive'           => 'badge--success',
            'Neutral'            => 'badge--neutral',
            'Negative'           => 'badge--danger',
            'Completed'          => 'badge--success',
            'No Response'        => 'badge--neutral',
            'Follow-up Required' => 'badge--warning',
            'Cancelled'          => 'badge--neutral',
        ];

        if (empty($rows)) {
            return '<div class="tile-card__empty">'
                 . '<i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>'
                 . '<p>No activities logged yet.</p>'
                 . '<a href="' . $e($logUrl) . '" class="btn btn--secondary btn--sm" style="margin-top:0.5rem">'
                 . '<i class="fa-solid fa-plus" aria-hidden="true"></i> Log Activity</a>'
                 . '</div>';
        }

        $html = '<ul class="record-list">';
        foreach ($rows as $row) {
            $badgeCls = $outcomeBadge[$row['outcome'] ?? ''] ?? 'badge--neutral';
            $cost     = $row['cost'] !== null ? '$' . number_format((float) $row['cost'], 2) : '—';
            $duration = $row['duration_minutes'] ? $row['duration_minutes'] . ' min' : null;

            $html .= '<li class="record-list__item">';
            $html .= '<div class="record-list__main">';
            $html .= '<a href="/crm/activities/details?id=' . (int) $row['id'] . '" class="record-list__name">'
                   . $e($row['type_name']) . '</a>';
            $html .= '<span class="record-list__meta">' . $e($row['activity_date']);
            if ($duration) {
                $html .= ' &middot; ' . $e($duration);
            }
            $html .= '</span>';
            $html .= '</div>';
            $html .= '<div class="record-list__meta">';
            $html .= '<span class="record-list__detail">' . $cost . '</span>';
            if ($row['outcome']) {
                $html .= '<span class="badge ' . $badgeCls . ' record-list__stage">' . $e($row['outcome']) . '</span>';
            }
            $html .= '</div>';
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '<div class="record-list__footer">'
               . '<a href="' . $e($logUrl) . '" class="record-list__all">'
               . '<i class="fa-solid fa-plus" aria-hidden="true"></i> Log Activity</a>'
               . '</div>';

        return $html;
    }
}
