<?php

class AssetSummary
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function render(): string
    {
        $row = $this->db->queryOne(
            "SELECT
                SUM(status = 'Active')   AS active,
                SUM(status = 'In Repair') AS in_repair,
                SUM(
                    warranty_expires IS NOT NULL
                    AND warranty_expires <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    AND warranty_expires >= CURDATE()
                    AND status NOT IN ('Retired','Lost/Stolen')
                ) AS expiring
               FROM assets"
        ) ?? [];

        $active   = (int) ($row['active']   ?? 0);
        $inRepair = (int) ($row['in_repair'] ?? 0);
        $expiring = (int) ($row['expiring']  ?? 0);

        ob_start();
        ?>
        <div class="metric-grid">
            <a href="/assets/assets/list?status=Active" class="metric-grid__item">
                <span class="metric-grid__value"><?= number_format($active) ?></span>
                <span class="metric-grid__label">Active</span>
            </a>
            <a href="/assets/assets/list?status=In+Repair" class="metric-grid__item">
                <span class="metric-grid__value"><?= number_format($inRepair) ?></span>
                <span class="metric-grid__label">In Repair</span>
            </a>
            <div class="metric-grid__item<?= $expiring > 0 ? ' metric-grid__item--warn' : '' ?>">
                <span class="metric-grid__value"><?= number_format($expiring) ?></span>
                <span class="metric-grid__label">Warranties Expiring</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
