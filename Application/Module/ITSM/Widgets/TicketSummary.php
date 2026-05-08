<?php

/**
 * TicketSummary Widget
 *
 * Renders a 4-block metric grid showing open ticket counts by urgency and stage.
 * Used on the dashboard or as a tile card in other detail pages.
 */
class TicketSummary
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    private function getCounts(): array
    {
        $rows = $this->db->query(
            "SELECT
                SUM(priority IN ('Critical','High') AND status NOT IN ('Resolved','Closed')) AS critical_high,
                SUM(status = 'New')         AS new_count,
                SUM(status = 'In Progress') AS in_progress,
                SUM(status = 'Pending')     AS pending
             FROM itsm_tickets"
        );
        return $rows[0] ?? ['critical_high' => 0, 'new_count' => 0, 'in_progress' => 0, 'pending' => 0];
    }

    public function render(): string
    {
        $counts = $this->getCounts();

        $criticalHigh = (int) ($counts['critical_high'] ?? 0);
        $newCount     = (int) ($counts['new_count']     ?? 0);
        $inProgress   = (int) ($counts['in_progress']   ?? 0);
        $pending      = (int) ($counts['pending']        ?? 0);

        if ($criticalHigh === 0 && $newCount === 0 && $inProgress === 0 && $pending === 0) {
            return '<div class="tile-card__empty">'
                 . '<i class="fa-solid fa-circle-check" aria-hidden="true"></i>'
                 . '<p>No open tickets.</p>'
                 . '</div>';
        }

        $html  = '<div class="metric-grid">';

        $html .= '<div class="metric-block metric-block--primary">'
               . '<div class="metric-block__icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></div>'
               . '<div class="metric-block__body">'
               . '<span class="metric-block__label">Critical / High</span>'
               . '<span class="metric-block__value">' . $criticalHigh . '</span>'
               . '<span class="metric-block__sub">open tickets</span>'
               . '</div></div>';

        $html .= '<div class="metric-block metric-block--secondary">'
               . '<div class="metric-block__icon"><i class="fa-solid fa-circle-dot" aria-hidden="true"></i></div>'
               . '<div class="metric-block__body">'
               . '<span class="metric-block__label">New</span>'
               . '<span class="metric-block__value">' . $newCount . '</span>'
               . '<span class="metric-block__sub">awaiting triage</span>'
               . '</div></div>';

        $html .= '<div class="metric-block">'
               . '<div class="metric-block__icon"><i class="fa-solid fa-spinner" aria-hidden="true"></i></div>'
               . '<div class="metric-block__body">'
               . '<span class="metric-block__label">In Progress</span>'
               . '<span class="metric-block__value">' . $inProgress . '</span>'
               . '<span class="metric-block__sub">being worked</span>'
               . '</div></div>';

        $html .= '<div class="metric-block">'
               . '<div class="metric-block__icon"><i class="fa-solid fa-clock" aria-hidden="true"></i></div>'
               . '<div class="metric-block__body">'
               . '<span class="metric-block__label">Pending</span>'
               . '<span class="metric-block__value">' . $pending . '</span>'
               . '<span class="metric-block__sub">awaiting response</span>'
               . '</div></div>';

        $html .= '</div>';

        $html .= '<div class="widget-footer">'
               . '<a href="/itsm/tickets/list" class="record-list__all">View all tickets →</a>'
               . '</div>';

        return $html;
    }
}
