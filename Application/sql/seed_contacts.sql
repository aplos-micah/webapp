-- =============================================================================
-- AplosCRM — Seed Data: 30 Contacts across 5 Accounts
--
-- Accounts referenced (from seed_accounts.sql, inserted in order):
--   id=1  → Walmart Inc.           (Retail)
--   id=3  → Apple Inc.             (Technology)
--   id=4  → UnitedHealth Group     (Healthcare)
--   id=8  → Alphabet Inc.          (Technology)
--   id=13 → Microsoft Corporation  (Technology)
--
-- owner_id=1 assumes the first registered user owns these contacts.
-- Run after seed_accounts.sql has been executed.
-- =============================================================================

INSERT INTO contacts (
    first_name, last_name, job_title, company, account_id,
    email, work_phone, mobile_phone,
    communication_preference, lifecycle_stage, lead_source,
    owner_id, status, industry, buying_role, lead_score,
    last_contact_at, last_activity
)
VALUES

-- ============================================================
-- Walmart Inc. (account_id = 1) — Retail — 6 contacts
-- ============================================================
(
    'Jennifer', 'Walsh',
    'VP of Merchandising', 'Walmart Inc.', 6,
    'jennifer.walsh@walmart.com', '479-555-0101', '479-555-0201',
    'Email', 'Customer', 'Conference',
    1, 'Active', 'Retail', 'Decision Maker', 88,
    '2025-03-15 10:00:00', 'Quarterly business review'
),
(
    'Marcus', 'Thompson',
    'Director of Technology Partnerships', 'Walmart Inc.', 6,
    'marcus.thompson@walmart.com', '479-555-0102', '479-555-0202',
    'Email', 'Customer', 'Referral',
    1, 'Active', 'Retail', 'Champion', 74,
    '2025-03-20 14:00:00', 'Demo follow-up email sent'
),
(
    'Sarah', 'Kim',
    'Senior Buyer', 'Walmart Inc.', 6,
    'sarah.kim@walmart.com', '479-555-0103', NULL,
    'Phone', 'SQL', 'LinkedIn',
    1, 'Active', 'Retail', 'Influencer', 61,
    '2025-02-28 09:30:00', 'Discovery call completed'
),
(
    'Robert', 'Hanson',
    'Head of Supply Chain', 'Walmart Inc.', 6,
    'robert.hanson@walmart.com', '479-555-0104', '479-555-0204',
    'Email', 'MQL', 'Website',
    1, 'Active', 'Retail', 'Decision Maker', 52,
    '2025-01-10 11:00:00', 'Downloaded case study'
),
(
    'Amanda', 'Torres',
    'IT Solutions Manager', 'Walmart Inc.', 6,
    'amanda.torres@walmart.com', '479-555-0105', NULL,
    'Email', 'SQL', 'Outbound',
    1, 'Active', 'Retail', 'Champion', 69,
    '2025-03-05 15:00:00', 'Proposal sent'
),
(
    'David', 'Chen',
    'Category Manager', 'Walmart Inc.', 6,
    'david.chen@walmart.com', '479-555-0106', '479-555-0206',
    'Phone', 'Lead', 'Conference',
    1, 'Active', 'Retail', 'Influencer', 35,
    '2025-02-01 13:00:00', 'Initial outreach made'
),

-- ============================================================
-- Apple Inc. (account_id = 3) — Technology — 6 contacts
-- ============================================================
(
    'Lisa', 'Park',
    'Enterprise Account Executive', 'Apple Inc.', 8,
    'lisa.park@apple.com', '408-555-0301', '408-555-0401',
    'Email', 'Evangelist', 'Referral',
    1, 'Active', 'Technology', 'Champion', 97,
    '2025-03-22 09:00:00', 'Renewal signed'
),
(
    'James', 'O\'Brien',
    'Developer Relations Lead', 'Apple Inc.', 8,
    'james.obrien@apple.com', '408-555-0302', NULL,
    'Email', 'Customer', 'LinkedIn',
    1, 'Active', 'Technology', 'Influencer', 83,
    '2025-03-18 14:30:00', 'Partnership discussion'
),
(
    'Michelle', 'Carter',
    'Strategic Partnerships Director', 'Apple Inc.', 8,
    'michelle.carter@apple.com', '408-555-0303', '408-555-0403',
    'Email', 'Customer', 'Conference',
    1, 'Active', 'Technology', 'Decision Maker', 91,
    '2025-03-10 10:00:00', 'Executive briefing attended'
),
(
    'Kevin', 'Zhang',
    'Product Marketing Manager', 'Apple Inc.', 8,
    'kevin.zhang@apple.com', '408-555-0304', NULL,
    'Phone', 'SQL', 'Website',
    1, 'Active', 'Technology', 'Influencer', 65,
    '2025-02-20 11:00:00', 'Product demo scheduled'
),
(
    'Rachel', 'Morrison',
    'Business Development Manager', 'Apple Inc.', 8,
    'rachel.morrison@apple.com', '408-555-0305', '408-555-0405',
    'Email', 'SQL', 'Partner',
    1, 'Active', 'Technology', 'Champion', 77,
    '2025-03-01 16:00:00', 'Contract review in progress'
),
(
    'Tyler', 'Brooks',
    'Senior Solutions Architect', 'Apple Inc.', 8,
    'tyler.brooks@apple.com', '408-555-0306', '408-555-0406',
    'Email', 'MQL', 'Outbound',
    1, 'Active', 'Technology', 'Influencer', 55,
    '2025-01-25 10:00:00', 'Technical requirements gathered'
),

-- ============================================================
-- UnitedHealth Group (account_id = 4) — Healthcare — 6 contacts
-- ============================================================
(
    'Patricia', 'Nguyen',
    'VP of Operations', 'UnitedHealth Group', 9,
    'patricia.nguyen@uhg.com', '763-555-0401', '763-555-0501',
    'Phone', 'Customer', 'Referral',
    1, 'Active', 'Healthcare', 'Decision Maker', 90,
    '2025-03-12 08:30:00', 'Contract renewal discussion'
),
(
    'Michael', 'Reyes',
    'Director of Provider Relations', 'UnitedHealth Group', 9,
    'michael.reyes@uhg.com', '763-555-0402', NULL,
    'Email', 'Customer', 'Conference',
    1, 'Active', 'Healthcare', 'Influencer', 78,
    '2025-03-08 13:00:00', 'Integration roadmap review'
),
(
    'Susan', 'Wallace',
    'Chief Compliance Officer', 'UnitedHealth Group', 9,
    'susan.wallace@uhg.com', '763-555-0403', '763-555-0503',
    'Email', 'SQL', 'LinkedIn',
    1, 'Active', 'Healthcare', 'Decision Maker', 82,
    '2025-02-14 11:00:00', 'Compliance workshop attended'
),
(
    'Brian', 'Foster',
    'Manager of Analytics', 'UnitedHealth Group', 9,
    'brian.foster@uhg.com', '763-555-0404', NULL,
    'Email', 'MQL', 'Website',
    1, 'Active', 'Healthcare', 'Champion', 58,
    '2025-01-30 14:00:00', 'Whitepaper downloaded'
),
(
    'Nancy', 'Coleman',
    'Senior Account Manager', 'UnitedHealth Group', 9,
    'nancy.coleman@uhg.com', '763-555-0405', '763-555-0505',
    'Phone', 'SQL', 'Outbound',
    1, 'Active', 'Healthcare', 'Champion', 70,
    '2025-02-22 10:30:00', 'Needs assessment call'
),
(
    'Jason', 'Rivera',
    'Technology Integration Lead', 'UnitedHealth Group', 9,
    'jason.rivera@uhg.com', '763-555-0406', NULL,
    'Email', 'Lead', 'Partner',
    1, 'Active', 'Healthcare', 'Influencer', 42,
    '2025-01-15 09:00:00', 'Introduction email sent'
),

-- ============================================================
-- Alphabet Inc. (account_id = 8) — Technology — 6 contacts
-- ============================================================
(
    'Christopher', 'Lee',
    'Head of Cloud Partnerships', 'Alphabet Inc.', 13,
    'christopher.lee@google.com', '650-555-0801', '650-555-0901',
    'Email', 'Customer', 'Partner',
    1, 'Active', 'Technology', 'Decision Maker', 13,
    '2025-03-21 11:00:00', 'Cloud migration deal closed'
),
(
    'Emily', 'Adams',
    'Senior Program Manager', 'Alphabet Inc.', 13,
    'emily.adams@google.com', '650-555-0802', NULL,
    'Email', 'Evangelist', 'Referral',
    1, 'Active', 'Technology', 'Champion', 13,
    '2025-03-19 09:30:00', 'Case study interview completed'
),
(
    'Daniel', 'Wright',
    'Director of Business Development', 'Alphabet Inc.', 13,
    'daniel.wright@google.com', '650-555-0803', '650-555-0903',
    'Email', 'Customer', 'Conference',
    1, 'Active', 'Technology', 'Decision Maker', 85,
    '2025-03-05 14:00:00', 'Partnership agreement signed'
),
(
    'Stephanie', 'Harris',
    'Enterprise Sales Lead', 'Alphabet Inc.', 13,
    'stephanie.harris@google.com', '650-555-0804', '650-555-0904',
    'Phone', 'SQL', 'LinkedIn',
    1, 'Active', 'Technology', 'Champion', 73,
    '2025-02-27 10:00:00', 'Negotiation in progress'
),
(
    'Andrew', 'Kim',
    'Solutions Consultant', 'Alphabet Inc.', 13,
    'andrew.kim@google.com', '650-555-0805', NULL,
    'Email', 'MQL', 'Website',
    1, 'Active', 'Technology', 'Influencer', 49,
    '2025-02-10 13:30:00', 'Technical evaluation started'
),
(
    'Lauren', 'Martinez',
    'Partner Success Manager', 'Alphabet Inc.', 13,
    'lauren.martinez@google.com', '650-555-0806', '650-555-0906',
    'Email', 'SQL', 'Outbound',
    1, 'Active', 'Technology', 'Influencer', 64,
    '2025-03-03 15:00:00', 'Onboarding plan shared'
),

-- ============================================================
-- Microsoft Corporation (account_id = 13) — Technology — 6 contacts
-- ============================================================
(
    'William', 'Scott',
    'VP of Enterprise Sales', 'Microsoft Corporation', 18,
    'william.scott@microsoft.com', '425-555-1301', '425-555-1401',
    'Email', 'Customer', 'Conference',
    1, 'Active', 'Technology', 'Decision Maker', 92,
    '2025-03-17 09:00:00', 'Annual review meeting'
),
(
    'Catherine', 'Bell',
    'Director of Customer Success', 'Microsoft Corporation', 18,
    'catherine.bell@microsoft.com', '425-555-1302', NULL,
    'Email', 'Evangelist', 'Referral',
    1, 'Active', 'Technology', 'Champion', 96,
    '2025-03-20 11:00:00', 'Spoke at partner webinar'
),
(
    'Thomas', 'Mitchell',
    'Senior Account Executive', 'Microsoft Corporation', 18,
    'thomas.mitchell@microsoft.com', '425-555-1303', '425-555-1403',
    'Phone', 'Customer', 'LinkedIn',
    1, 'Active', 'Technology', 'Champion', 80,
    '2025-03-11 14:00:00', 'Upsell opportunity identified'
),
(
    'Olivia', 'Turner',
    'Partner Development Manager', 'Microsoft Corporation', 18,
    'olivia.turner@microsoft.com', '425-555-1304', NULL,
    'Email', 'SQL', 'Partner',
    1, 'Active', 'Technology', 'Influencer', 68,
    '2025-02-25 10:30:00', 'Partner program application submitted'
),
(
    'Samuel', 'Garcia',
    'Cloud Solutions Architect', 'Microsoft Corporation', 18,
    'samuel.garcia@microsoft.com', '425-555-1305', '425-555-1405',
    'Email', 'SQL', 'Outbound',
    1, 'Active', 'Technology', 'Champion', 72,
    '2025-03-04 16:00:00', 'Architecture review call'
),
(
    'Grace', 'Patel',
    'Business Development Representative', 'Microsoft Corporation', 18,
    'grace.patel@microsoft.com', '425-555-1306', NULL,
    'Email', 'MQL', 'Website',
    1, 'Active', 'Technology', 'Influencer', 46,
    '2025-01-28 09:00:00', 'Responded to outbound email'
);
