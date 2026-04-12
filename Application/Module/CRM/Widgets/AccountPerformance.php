<?php

/**
 * AccountPerformance Widget
 *
 * Renders the Customer Performance tile on the Account Details page.
 *
 * Two visuals:
 *   1. Customer Lifetime Value — sum of `amount` on all Closed Won
 *      opportunities for this account.
 *   2. Pipeline Value — sum of `amount` on all open (not Closed Won /
 *      Closed Lost) opportunities for this account.
 *
 * Below the stats: a proportional bar + a per-stage breakdown of open deals.
 */
class AccountPerformance
{
    private DB $db;

    private const OPEN_STAGES = ['New', 'Building', 'Review', 'Quote', 'Negotiating'];

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    // -------------------------------------------------------------------------
    // When $accountId is null the queries aggregate across all accounts.
    // -------------------------------------------------------------------------

    private function acctParams(?int $accountId): array
    {
        return $accountId !== null ? [$accountId] : [];
    }

    public function clv(?int $accountId = null): float
    {
        $row = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount), 0) AS total
               FROM opportunities
              WHERE stage = 'Closed Won'
              " . ($accountId !== null ? "AND account_id = ?" : ''),
            $this->acctParams($accountId)
        );
        return (float) ($row['total'] ?? 0);
    }

    public function pipelineValue(?int $accountId = null): float
    {
        $row = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount), 0) AS total
               FROM opportunities
              WHERE stage NOT IN ('Closed Won','Closed Lost')
              " . ($accountId !== null ? "AND account_id = ?" : ''),
            $this->acctParams($accountId)
        );
        return (float) ($row['total'] ?? 0);
    }

    public function openDealsByStage(?int $accountId = null): array
    {
        return $this->db->query(
            "SELECT stage,
                    COUNT(*)                 AS deal_count,
                    COALESCE(SUM(amount), 0) AS stage_value
               FROM opportunities
              WHERE stage NOT IN ('Closed Won','Closed Lost')
              " . ($accountId !== null ? "AND account_id = ?" : '') . "
              GROUP BY stage
              ORDER BY FIELD(stage,'New','Building','Review','Quote','Negotiating')",
            $this->acctParams($accountId)
        );
    }

    public function wonDealsCount(?int $accountId = null): int
    {
        $row = $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM opportunities
              WHERE stage = 'Closed Won'
              " . ($accountId !== null ? "AND account_id = ?" : ''),
            $this->acctParams($accountId)
        );
        return (int) ($row['total'] ?? 0);
    }

    // =========================================================================
    // Render
    // =========================================================================

    public function render(?int $accountId = null): string
    {
        $clv          = $this->clv($accountId);
        $pipeline     = $this->pipelineValue($accountId);
        $openByStage  = $this->openDealsByStage($accountId);
        $wonCount     = $this->wonDealsCount($accountId);

        $fmt = fn(float $v): string => '$' . number_format($v, 0);

        // Proportion bar: what fraction of (clv + pipeline) is each segment?
        $total        = $clv + $pipeline;
        $clvPct       = $total > 0 ? round(($clv      / $total) * 100, 1) : 0;
        $pipePct      = $total > 0 ? round(($pipeline / $total) * 100, 1) : 0;

        $stageBadge = [
            'New'         => 'badge--neutral',
            'Building'    => 'badge--info',
            'Review'      => 'badge--info',
            'Quote'       => 'badge--warning',
            'Negotiating' => 'badge--purple',
        ];

        ob_start(); ?>

        <!-- Stat blocks -->
        <div class="perf-stats">

            <div class="perf-stat perf-stat--clv">
                <div class="perf-stat__icon">
                    <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                </div>
                <div class="perf-stat__body">
                    <span class="perf-stat__label">Customer Lifetime Value</span>
                    <span class="perf-stat__value"><?= $fmt($clv) ?></span>
                    <span class="perf-stat__sub"><?= $wonCount ?> closed won deal<?= $wonCount !== 1 ? 's' : '' ?></span>
                </div>
            </div>

            <div class="perf-stat perf-stat--pipeline">
                <div class="perf-stat__icon">
                    <i class="fa-solid fa-filter-circle-dollar" aria-hidden="true"></i>
                </div>
                <div class="perf-stat__body">
                    <span class="perf-stat__label">Pipeline Value</span>
                    <span class="perf-stat__value"><?= $fmt($pipeline) ?></span>
                    <span class="perf-stat__sub"><?= count($openByStage) > 0 ? array_sum(array_column($openByStage, 'deal_count')) . ' open deal' . (array_sum(array_column($openByStage, 'deal_count')) !== 1 ? 's' : '') : 'No open deals' ?></span>
                </div>
            </div>

        </div>

        <?php if ($total > 0): ?>
        <!-- Proportion bar -->
        <div class="perf-bar" title="Green = Closed Won (<?= $clvPct ?>%)  |  Blue = Pipeline (<?= $pipePct ?>%)">
            <?php if ($clvPct > 0): ?>
            <div class="perf-bar__segment perf-bar__segment--won"
                 style="width:<?= $clvPct ?>%"
                 title="Closed Won <?= $clvPct ?>%"></div>
            <?php endif; ?>
            <?php if ($pipePct > 0): ?>
            <div class="perf-bar__segment perf-bar__segment--pipeline"
                 style="width:<?= $pipePct ?>%"
                 title="Pipeline <?= $pipePct ?>%"></div>
            <?php endif; ?>
        </div>
        <div class="perf-bar__legend">
            <span class="perf-bar__legend-item perf-bar__legend-item--won">
                <span class="perf-bar__legend-dot"></span> Won (<?= $clvPct ?>%)
            </span>
            <span class="perf-bar__legend-item perf-bar__legend-item--pipeline">
                <span class="perf-bar__legend-dot"></span> Pipeline (<?= $pipePct ?>%)
            </span>
        </div>
        <?php endif; ?>

        <?php if (!empty($openByStage)): ?>
        <!-- Open deals by stage -->
        <p class="detail-section-label" style="margin-top:1rem;">Open Deals by Stage</p>
        <ul class="perf-stage-list">
            <?php foreach ($openByStage as $row): ?>
            <?php $badge = $stageBadge[$row['stage']] ?? 'badge--neutral'; ?>
            <li class="perf-stage-list__item">
                <span class="badge <?= $badge ?>"><?= htmlspecialchars($row['stage'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="perf-stage-list__count"><?= (int) $row['deal_count'] ?> deal<?= (int) $row['deal_count'] !== 1 ? 's' : '' ?></span>
                <span class="perf-stage-list__value"><?= $fmt((float) $row['stage_value']) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php elseif ($clv === 0.0): ?>
        <div class="related-card__empty" style="padding:1.5rem 0 0.5rem;">
            <i class="fa-regular fa-chart-bar" aria-hidden="true"></i>
            <p>No performance data yet.</p>
        </div>
        <?php endif; ?>

        <?php return ob_get_clean();
    }
}
