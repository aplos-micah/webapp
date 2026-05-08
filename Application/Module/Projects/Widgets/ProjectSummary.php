<?php

class ProjectSummary
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
                SUM(status = 'Active')                                                AS active,
                SUM(status NOT IN ('Completed','Cancelled'))                          AS open,
                SUM(
                    status NOT IN ('Completed','Cancelled')
                    AND due_date IS NOT NULL
                    AND due_date < CURDATE()
                )                                                                     AS overdue
               FROM projects"
        ) ?? [];

        $active  = (int) ($row['active']  ?? 0);
        $open    = (int) ($row['open']    ?? 0);
        $overdue = (int) ($row['overdue'] ?? 0);

        ob_start();
        ?>
        <div class="metric-grid">
            <a href="/projects/projects/list?status=Active" class="metric-grid__item">
                <span class="metric-grid__value"><?= number_format($active) ?></span>
                <span class="metric-grid__label">Active</span>
            </a>
            <a href="/projects/projects/list" class="metric-grid__item">
                <span class="metric-grid__value"><?= number_format($open) ?></span>
                <span class="metric-grid__label">Open</span>
            </a>
            <div class="metric-grid__item<?= $overdue > 0 ? ' metric-grid__item--warn' : '' ?>">
                <span class="metric-grid__value"><?= number_format($overdue) ?></span>
                <span class="metric-grid__label">Overdue</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
