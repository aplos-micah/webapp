-- =============================================================================
-- AplosCRM — Trigger: sync opportunities.amount from product line item totals
--
-- Fires AFTER INSERT, UPDATE, or DELETE on opportunity_product_line_items.
-- Recalculates SUM(total_price) for the affected opportunity and writes it
-- back to opportunities.amount.  A NULL sum (no rows) sets amount to 0.
-- =============================================================================

DROP TRIGGER IF EXISTS trg_opli_after_insert;
DROP TRIGGER IF EXISTS trg_opli_after_update;
DROP TRIGGER IF EXISTS trg_opli_after_delete;

DELIMITER $$

CREATE TRIGGER trg_OpportunityLineITEM_after_insert
AFTER INSERT ON opportunity_product_line_items
FOR EACH ROW
BEGIN
    UPDATE opportunities
       SET amount = (
               SELECT COALESCE(SUM(total_price), 0)
                 FROM opportunity_product_line_items
                WHERE opportunity_id = NEW.opportunity_id
           )
     WHERE id = NEW.opportunity_id;
END$$

CREATE TRIGGER trg_OpportunityLineITEM_after_update
AFTER UPDATE ON opportunity_product_line_items
FOR EACH ROW
BEGIN
    -- If the row moved to a different opportunity, recalc both
    IF OLD.opportunity_id <> NEW.opportunity_id THEN
        UPDATE opportunities
           SET amount = (
                   SELECT COALESCE(SUM(total_price), 0)
                     FROM opportunity_product_line_items
                    WHERE opportunity_id = OLD.opportunity_id
               )
         WHERE id = OLD.opportunity_id;
    END IF;

    UPDATE opportunities
       SET amount = (
               SELECT COALESCE(SUM(total_price), 0)
                 FROM opportunity_product_line_items
                WHERE opportunity_id = NEW.opportunity_id
           )
     WHERE id = NEW.opportunity_id;
END$$

CREATE TRIGGER trg_OpportunityLineITEM_after_delete
AFTER DELETE ON opportunity_product_line_items
FOR EACH ROW
BEGIN
    UPDATE opportunities
       SET amount = (
               SELECT COALESCE(SUM(total_price), 0)
                 FROM opportunity_product_line_items
                WHERE opportunity_id = OLD.opportunity_id
           )
     WHERE id = OLD.opportunity_id;
END$$

DELIMITER ;
