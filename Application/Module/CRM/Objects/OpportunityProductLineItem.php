<?php

/**
 * OpportunityProductLineItem
 *
 * Data access layer for the opportunity_product_line_items table.
 */
class OpportunityProductLineItem
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // Queries
    // =========================================================================

    public function findByOpportunity(int $opportunityId): array
    {
        return $this->db->query(
            "SELECT li.id, li.product_definition_id, li.product_name,
                    li.unit_price, li.quantity, li.discount_percentage,
                    li.discount_amount, li.total_price,
                    li.service_date, li.subscription_term,
                    li.revenue_schedule_type,
                    li.ship_to_location_id,
                    p.sku,
                    sl.location_name AS ship_to_location_name,
                    sl.street_address_1 AS ship_to_street,
                    sl.city AS ship_to_city,
                    sl.state_province AS ship_to_state,
                    sl.zip_postal_code AS ship_to_zip
               FROM opportunity_product_line_items li
               LEFT JOIN product_definitions p ON p.id = li.product_definition_id
               LEFT JOIN locations sl ON sl.id = li.ship_to_location_id
              WHERE li.opportunity_id = ?
              ORDER BY li.id ASC",
            [$opportunityId]
        );
    }

    // =========================================================================
    // Mutations
    // =========================================================================

    public function add(int $opportunityId, array $data): array
    {
        $name = trim($data['product_name'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'id' => null, 'error' => 'Product name is required.'];
        }

        $unitPrice      = round((float) ($data['unit_price'] ?? 0), 2);
        $quantity       = max(0.0001, round((float) ($data['quantity'] ?? 1), 4));
        $discountPct    = round(max(0, (float) ($data['discount_percentage'] ?? 0)), 2);
        // Discount amount: if pct provided, derive it; otherwise use explicit amount
        $discountAmount = $discountPct > 0
            ? round($unitPrice * $quantity * ($discountPct / 100), 2)
            : round(max(0, (float) ($data['discount_amount'] ?? 0)), 2);
        $totalPrice     = round(($unitPrice * $quantity) - $discountAmount, 2);

        $productDefId   = ($data['product_definition_id'] ?? '') !== ''
            ? (int) $data['product_definition_id'] : null;

        $serviceDate    = ($data['service_date'] ?? '') !== '' ? $data['service_date'] : null;
        $subTerm        = ($data['subscription_term'] ?? '') !== '' ? (int) $data['subscription_term'] : null;
        $scheduleType   = in_array($data['revenue_schedule_type'] ?? '', ['One-time','Monthly','Quarterly','Annually'], true)
            ? $data['revenue_schedule_type'] : 'One-time';
        $shipToLocId    = ($data['ship_to_location_id'] ?? '') !== '' ? (int) $data['ship_to_location_id'] : null;

        $id = $this->db->insert(
            "INSERT INTO opportunity_product_line_items
                (opportunity_id, product_definition_id, product_name,
                 unit_price, quantity, discount_percentage, discount_amount, total_price,
                 service_date, subscription_term, revenue_schedule_type, ship_to_location_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $opportunityId, $productDefId, $name,
                $unitPrice, $quantity, $discountPct, $discountAmount, $totalPrice,
                $serviceDate, $subTerm, $scheduleType, $shipToLocId,
            ]
        );

        return ['ok' => true, 'id' => (int) $id, 'error' => null];
    }

    public function remove(int $lineItemId, int $opportunityId): bool
    {
        $affected = $this->db->execute(
            'DELETE FROM opportunity_product_line_items WHERE id = ? AND opportunity_id = ?',
            [$lineItemId, $opportunityId]
        );
        return $affected > 0;
    }

    public function update(int $lineItemId, int $opportunityId, array $data): array
    {
        $name = trim($data['product_name'] ?? '');
        if ($name === '') {
            return ['ok' => false, 'error' => 'Product name is required.'];
        }

        $unitPrice      = round((float) ($data['unit_price'] ?? 0), 2);
        $quantity       = max(0.0001, round((float) ($data['quantity'] ?? 1), 4));
        $discountPct    = round(max(0, (float) ($data['discount_percentage'] ?? 0)), 2);
        $discountAmount = $discountPct > 0
            ? round($unitPrice * $quantity * ($discountPct / 100), 2)
            : round(max(0, (float) ($data['discount_amount'] ?? 0)), 2);
        $totalPrice     = round(($unitPrice * $quantity) - $discountAmount, 2);

        $serviceDate  = ($data['service_date'] ?? '') !== '' ? $data['service_date'] : null;
        $subTerm      = ($data['subscription_term'] ?? '') !== '' ? (int) $data['subscription_term'] : null;
        $scheduleType = in_array($data['revenue_schedule_type'] ?? '', ['One-time', 'Monthly', 'Quarterly', 'Annually'], true)
            ? $data['revenue_schedule_type'] : 'One-time';
        $shipToLocId  = ($data['ship_to_location_id'] ?? '') !== '' ? (int) $data['ship_to_location_id'] : null;

        $affected = $this->db->execute(
            "UPDATE opportunity_product_line_items
                SET product_name = ?, unit_price = ?, quantity = ?,
                    discount_percentage = ?, discount_amount = ?, total_price = ?,
                    service_date = ?, subscription_term = ?, revenue_schedule_type = ?,
                    ship_to_location_id = ?
              WHERE id = ? AND opportunity_id = ?",
            [
                $name, $unitPrice, $quantity,
                $discountPct, $discountAmount, $totalPrice,
                $serviceDate, $subTerm, $scheduleType,
                $shipToLocId,
                $lineItemId, $opportunityId,
            ]
        );

        return $affected > 0
            ? ['ok' => true,  'error' => null]
            : ['ok' => false, 'error' => 'Line item not found.'];
    }

    // =========================================================================
    // Aggregates
    // =========================================================================

    public function totalForOpportunity(int $opportunityId): float
    {
        $row = $this->db->queryOne(
            'SELECT COALESCE(SUM(total_price), 0) AS grand_total
               FROM opportunity_product_line_items
              WHERE opportunity_id = ?',
            [$opportunityId]
        );
        return (float) ($row['grand_total'] ?? 0);
    }
}
