<?php

/**
 * AccountOpportunities Widget
 *
 * Renders the open Opportunities tile on the Account Details page.
 * Shows all opportunities for the account that are not Closed Won or Closed Lost.
 * Fields: Name, Stage, Amount, Probability, Close Date.
 */
class AccountOpportunities
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function getOpportunities(int $accountId): array
    {
        return $this->db->query(
            "SELECT id, opportunity_name, stage, amount, probability, close_date
               FROM opportunities
              WHERE account_id = ?
                AND stage NOT IN ('Closed Won','Closed Lost')
              ORDER BY close_date ASC, id DESC",
            [$accountId]
        );
    }

    public function render(int $accountId): string
    {
        $opps = $this->getOpportunities($accountId);

        if (empty($opps)) {
            return '<div class="related-card__empty">'
                 . '<i class="fa-regular fa-handshake" aria-hidden="true"></i>'
                 . '<p>No open opportunities for this account.</p>'
                 . '</div>';
        }

        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        $stageBadge = [
            'New'         => 'badge--neutral',
            'Building'    => 'badge--info',
            'Review'      => 'badge--info',
            'Quote'       => 'badge--warning',
            'Negotiating' => 'badge--purple',
        ];

        ob_start(); ?>
        <ul class="opp-widget-list">
        <?php foreach ($opps as $o):
            $badge      = $stageBadge[$o['stage'] ?? ''] ?? 'badge--neutral';
            $amount     = $o['amount'] !== null ? '$' . number_format((float) $o['amount'], 0) : null;
            $prob       = $o['probability'] !== null ? (int) $o['probability'] . '%' : null;
            $closeDate  = $o['close_date'] ?? null;

            // Flag overdue close dates
            $isOverdue  = $closeDate && $closeDate < date('Y-m-d');
        ?>
        <li class="opp-widget-list__item">
            <div class="opp-widget-list__top">
                <a href="/crm/opportunities/details?id=<?= (int) $o['id'] ?>"
                   class="opp-widget-list__name">
                    <?= $e($o['opportunity_name']) ?>
                </a>
                <span class="badge <?= $badge ?>"><?= $e($o['stage']) ?></span>
            </div>
            <div class="opp-widget-list__meta">
                <?php if ($amount): ?>
                <span class="opp-widget-list__amount"><?= $amount ?></span>
                <?php endif; ?>
                <?php if ($prob): ?>
                <span class="opp-widget-list__prob"><?= $prob ?> prob.</span>
                <?php endif; ?>
                <?php if ($closeDate): ?>
                <span class="opp-widget-list__date <?= $isOverdue ? 'opp-widget-list__date--overdue' : '' ?>">
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    <?= $e($closeDate) ?>
                    <?php if ($isOverdue): ?>
                    <span class="opp-widget-list__overdue-label">Overdue</span>
                    <?php endif; ?>
                </span>
                <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
        </ul>
        <div class="widget-contact-list__footer">
            <a href="/crm/opportunities/list" class="widget-contact-list__all">
                <?= count($opps) ?> open deal<?= count($opps) !== 1 ? 's' : '' ?> — View all
            </a>
        </div>
        <?php return ob_get_clean();
    }
}
