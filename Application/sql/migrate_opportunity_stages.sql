-- =============================================================================
-- AplosCRM — Migration: simplify opportunity stages
-- Run once. Safe to re-run (MODIFY COLUMN is idempotent if already correct).
-- =============================================================================

-- Step 1: remap any existing rows from old stage names to new names
UPDATE opportunities SET stage = 'New'          WHERE stage IN ('Prospecting');
UPDATE opportunities SET stage = 'Building'     WHERE stage IN ('Qualification','Needs Analysis','Value Proposition');
UPDATE opportunities SET stage = 'Review'       WHERE stage IN ('Id. Decision Makers','Perception Analysis');
UPDATE opportunities SET stage = 'Quote'        WHERE stage IN ('Proposal/Price Quote');
UPDATE opportunities SET stage = 'Negotiating'  WHERE stage IN ('Negotiation/Review');
-- 'Closed Won' and 'Closed Lost' keep their names

-- Step 2: update the ENUM to the new set
ALTER TABLE opportunities
    MODIFY COLUMN stage ENUM('New','Building','Review','Quote','Negotiating','Closed Won','Closed Lost')
                        NOT NULL DEFAULT 'New';
