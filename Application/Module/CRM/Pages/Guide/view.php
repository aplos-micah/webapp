<?php $pageTitle = 'CRM User Guide'; ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM</p>
        <h1 class="dash-header__title">User Guide</h1>
        <p class="dash-header__sub">How to use AplosCRM to manage accounts, contacts, and opportunities</p>
    </div>
    <div>
        <a href="/crm/accounts/list" class="btn btn--secondary">
            <i class="fa-solid fa-building" aria-hidden="true"></i> View Accounts
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Contents -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-list" aria-hidden="true"></i>
        Contents
    </h2>
    <ul class="instructions__list">
        <li><a href="#overview" class="table-link">Overview</a></li>
        <li><a href="#accounts" class="table-link">Accounts</a></li>
        <li><a href="#contacts" class="table-link">Contacts</a></li>
        <li><a href="#opportunities" class="table-link">Opportunities &amp; Pipeline</a></li>
        <li><a href="#products" class="table-link">Products</a></li>
        <li><a href="#playbook-new-account" class="table-link">Playbook — Onboarding a New Account</a></li>
        <li><a href="#playbook-pipeline" class="table-link">Playbook — Managing the Sales Pipeline</a></li>
        <li><a href="#playbook-closing" class="table-link">Playbook — Closing a Deal</a></li>
    </ul>
</div>

<!-- Overview -->
<div class="card profile-card mb-lg" id="overview">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-rocket" aria-hidden="true"></i>
        Overview
    </h2>
    <p class="instructions__p">AplosCRM organises your sales and relationship data around four core objects — Accounts, Contacts, Opportunities, and Products. Together they give a complete picture of your customer base and pipeline.</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Account</dt><dd>A company or organisation you do business with or are pursuing as a customer. All other records link back to an account.</dd></div>
        <div class="field-list__row"><dt>Contact</dt><dd>An individual person at an account — a buyer, influencer, or champion in a deal.</dd></div>
        <div class="field-list__row"><dt>Opportunity</dt><dd>A potential sale — a deal in progress. Contains line items (products), value, close date, and stage.</dd></div>
        <div class="field-list__row"><dt>Product</dt><dd>Items from your product catalogue that can be added as line items to opportunities.</dd></div>
    </dl>
</div>

<!-- Accounts -->
<div class="card profile-card mb-lg" id="accounts">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-building" aria-hidden="true"></i>
        Accounts
    </h2>
    <p class="instructions__p">An account is the foundational record in the CRM. Every contact and opportunity belongs to an account. Think of it as the company record.</p>

    <p class="section-label">Key fields</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Account Name</dt><dd>The company name. Required. Used for search and display throughout the CRM.</dd></div>
        <div class="field-list__row"><dt>Account Number</dt><dd>Your internal reference number (e.g. ACCT-0042). Optional but useful for linking to billing systems.</dd></div>
        <div class="field-list__row"><dt>Type</dt><dd>Prospect, Customer, Partner, Reseller, Competitor, or Other. Set this to reflect the current relationship stage.</dd></div>
        <div class="field-list__row"><dt>Status</dt><dd>Active, Inactive, Churned, On Hold, or Blacklisted. Use this to filter your active customer base.</dd></div>
        <div class="field-list__row"><dt>Industry</dt><dd>Sector classification for segmentation and reporting.</dd></div>
        <div class="field-list__row"><dt>Owner</dt><dd>The team member responsible for this account. Affects notification routing and reporting.</dd></div>
        <div class="field-list__row"><dt>Parent Account</dt><dd>For subsidiaries and divisions — link to the parent company to build an account hierarchy.</dd></div>
    </dl>

    <p class="section-label section-label--mt">Account detail page</p>
    <p class="instructions__p">The account detail page has two columns. The left side shows the full account record. The right side shows related tiles — <strong>Contacts</strong>, <strong>Opportunities</strong>, <strong>Locations</strong>, and <strong>Performance</strong> — which can be reordered by dragging.</p>

    <p class="section-label section-label--mt">Locations</p>
    <p class="instructions__p">Each account can have multiple locations (billing, shipping, site addresses). Locations can be marked as primary and linked to opportunities as bill-to or ship-to addresses. Add locations from the Locations tile on the account detail page.</p>
</div>

<!-- Contacts -->
<div class="card profile-card mb-lg" id="contacts">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-address-card" aria-hidden="true"></i>
        Contacts
    </h2>
    <p class="instructions__p">Contacts are the people you work with at your accounts. A contact should always be linked to an account.</p>

    <p class="section-label">Key fields</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Lifecycle Stage</dt><dd>Where the contact is in your funnel: Lead → MQL → SQL → Customer → Evangelist. Update this as the relationship progresses.</dd></div>
        <div class="field-list__row"><dt>Lead Source</dt><dd>How this contact entered your pipeline (Webinar, Referral, Cold Outreach, etc.). Important for marketing attribution.</dd></div>
        <div class="field-list__row"><dt>Buying Role</dt><dd>Decision Maker, Influencer, or Champion. In a complex deal there will be multiple contacts with different roles.</dd></div>
        <div class="field-list__row"><dt>Lead Score</dt><dd>A numeric score indicating engagement level. Higher scores indicate warmer leads.</dd></div>
        <div class="field-list__row"><dt>Communication Preference</dt><dd>Email, Phone, or SMS. Respect this field when deciding how to follow up.</dd></div>
    </dl>

    <p class="section-label section-label--mt">Lifecycle stage progression</p>
    <div class="step-progress mb-lg">
        <?php
        $stages = ['Lead', 'MQL', 'SQL', 'Customer', 'Evangelist'];
        foreach ($stages as $i => $stage):
        ?>
        <?php if ($i > 0): ?><div class="step-progress__connector"></div><?php endif; ?>
        <div class="step-progress__step<?= $stage === 'SQL' ? ' is-active' : ($i < 2 ? ' is-done' : '') ?>">
            <div class="step-progress__node"><?= $stage === 'Lead' || $stage === 'MQL' ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : ($i + 1) ?></div>
            <span class="step-progress__label"><?= $stage ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <dl class="field-list">
        <div class="field-list__row"><dt>Lead</dt><dd>A new name — not yet qualified. Just entered the system.</dd></div>
        <div class="field-list__row"><dt>MQL</dt><dd>Marketing Qualified Lead — shows intent signals (website activity, content downloads, event attendance).</dd></div>
        <div class="field-list__row"><dt>SQL</dt><dd>Sales Qualified Lead — confirmed budget, authority, need, and timeline. Passed to sales.</dd></div>
        <div class="field-list__row"><dt>Customer</dt><dd>Has purchased. Maintain the relationship for renewals and expansions.</dd></div>
        <div class="field-list__row"><dt>Evangelist</dt><dd>An active champion who refers others. Nurture carefully.</dd></div>
    </dl>
</div>

<!-- Opportunities -->
<div class="card profile-card mb-lg" id="opportunities">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-handshake" aria-hidden="true"></i>
        Opportunities &amp; Pipeline
    </h2>
    <p class="instructions__p">An opportunity tracks a potential sale from first contact to close. It links an account, a contact, and a set of products (line items) into a deal with a defined value and close date.</p>

    <p class="section-label">Pipeline stages</p>
    <div class="step-progress mb-lg">
        <?php
        $stages = ['New', 'Building', 'Review', 'Quote', 'Negotiating'];
        foreach ($stages as $i => $stage):
        ?>
        <?php if ($i > 0): ?><div class="step-progress__connector<?= $i <= 2 ? ' is-done' : '' ?>"></div><?php endif; ?>
        <div class="step-progress__step<?= $stage === 'Review' ? ' is-active' : ($i < 2 ? ' is-done' : '') ?>">
            <div class="step-progress__node"><?= $i < 2 ? '<i class="fa-solid fa-check" aria-hidden="true"></i>' : ($i + 1) ?></div>
            <span class="step-progress__label"><?= $stage ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <dl class="field-list">
        <div class="field-list__row"><dt><span class="badge badge--neutral">New</span></dt><dd>Opportunity identified. Account and initial contact set. Value and close date estimated.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--info">Building</span></dt><dd>Actively working with the prospect. Requirements are being gathered. Demo or discovery underway.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--info">Review</span></dt><dd>Solution is defined. Awaiting internal or customer review before proposal.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--warning">Quote</span></dt><dd>Formal proposal or quote has been sent to the customer.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--purple">Negotiating</span></dt><dd>Proposal accepted in principle. Working through commercial terms and finalising the contract.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--success">Closed Won</span></dt><dd>Deal signed. Move the contact to Customer lifecycle stage. Start the onboarding process.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--neutral">Closed Lost</span></dt><dd>Deal did not close. Record the loss reason for pipeline analysis.</dd></div>
    </dl>

    <p class="section-label section-label--mt">Line items</p>
    <p class="instructions__p">Add products from the catalogue as line items on an opportunity. Each line item has a unit price, quantity, and optional discount. The opportunity total is calculated automatically. Products can be added from the detail page — click <strong>Add Product</strong> in the line items section.</p>

    <p class="section-label section-label--mt">Key opportunity fields</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Amount</dt><dd>Auto-calculated as the sum of all line item totals. Do not enter manually — add line items instead.</dd></div>
        <div class="field-list__row"><dt>Close Date</dt><dd>The expected or committed date the deal will close. Used for pipeline forecasting.</dd></div>
        <div class="field-list__row"><dt>Probability</dt><dd>0–100% confidence of winning. Used in weighted pipeline reporting.</dd></div>
        <div class="field-list__row"><dt>Forecast Category</dt><dd>Pipeline, Best Case, Commit, or Closed. Use Commit for deals you are confident will close this period.</dd></div>
    </dl>
</div>

<!-- Products -->
<div class="card profile-card mb-lg" id="products">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-box" aria-hidden="true"></i>
        Products
    </h2>
    <p class="instructions__p">The product catalogue defines what you sell. Products are added as line items on opportunities. Keeping the catalogue accurate ensures consistent pricing across all deals.</p>

    <dl class="field-list">
        <div class="field-list__row"><dt>Lifecycle Status</dt><dd>Draft → Pending Approval → Activated → Archived. Only Activated products appear in the line item lookup when adding to an opportunity.</dd></div>
        <div class="field-list__row"><dt>List Price</dt><dd>The standard price for this product. Pre-fills the unit price when the product is added to an opportunity. Can be overridden per line item.</dd></div>
        <div class="field-list__row"><dt>SKU</dt><dd>Your internal product code. Shown in the lookup dropdown to help identify products quickly.</dd></div>
        <div class="field-list__row"><dt>Product Family</dt><dd>Software, Hardware, Consulting, Training, or Maintenance. Used for grouping and reporting.</dd></div>
        <div class="field-list__row"><dt>Pricing Model</dt><dd>Flat, Tiered, Volume, or Usage-Based. Informational — pricing logic is applied at the line item level.</dd></div>
    </dl>
</div>

<!-- Playbook: Onboarding a New Account -->
<h2 class="migration-heading mb-lg">
    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
    Playbooks
</h2>

<div class="card profile-card mb-lg" id="playbook-new-account">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-building-circle-arrow-right" aria-hidden="true"></i>
        Playbook — Onboarding a New Account
    </h2>
    <p class="instructions__p">Use this playbook when you start working with a new company — whether it's a cold prospect or a warm referral.</p>

    <p class="section-label">Step 1 — Create the Account</p>
    <ol class="instructions__list">
        <li>Go to <strong>Accounts → New Account</strong>.</li>
        <li>Enter the company name, website, and industry.</li>
        <li>Set <strong>Type</strong> to <em>Prospect</em>.</li>
        <li>Set <strong>Status</strong> to <em>Active</em>.</li>
        <li>Assign to yourself or the owning sales rep.</li>
        <li>Save — the account is now the parent record for all further activity.</li>
    </ol>

    <p class="section-label section-label--mt">Step 2 — Add Contacts</p>
    <ol class="instructions__list">
        <li>From the account detail page, click <strong>New</strong> in the Contacts tile.</li>
        <li>Add all known contacts — aim for at least one Decision Maker and one Champion.</li>
        <li>Set lifecycle stage to <em>Lead</em> for new contacts.</li>
        <li>Record job titles and communication preferences.</li>
    </ol>

    <p class="section-label section-label--mt">Step 3 — Add Locations (if applicable)</p>
    <ol class="instructions__list">
        <li>Click <strong>Add Location</strong> in the Locations tile.</li>
        <li>Enter the billing and/or shipping address.</li>
        <li>Mark the primary location.</li>
    </ol>

    <p class="section-label section-label--mt">Step 4 — Create an Opportunity</p>
    <ol class="instructions__list">
        <li>Click <strong>New</strong> in the Opportunities tile on the account.</li>
        <li>Name the opportunity clearly (e.g. "Acme Corp — Enterprise Licence 2026").</li>
        <li>Set stage to <em>New</em>, set an estimated close date, and link the primary contact.</li>
        <li>Add initial product line items if known.</li>
    </ol>
</div>

<!-- Playbook: Pipeline -->
<div class="card profile-card mb-lg" id="playbook-pipeline">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-filter-circle-dollar" aria-hidden="true"></i>
        Playbook — Managing the Sales Pipeline
    </h2>
    <p class="instructions__p">A healthy pipeline requires regular review and accurate stage management. Stale opportunities distort forecasting.</p>

    <p class="section-label">Weekly pipeline review</p>
    <ol class="instructions__list">
        <li>Open <strong>Opportunities → List</strong> and filter by your name as owner.</li>
        <li>Review every open opportunity. Ask for each one:
            <ul style="margin-top:0.35rem; padding-left:1.25rem;">
                <li>Is the stage still accurate?</li>
                <li>Is the close date still realistic?</li>
                <li>Has there been any activity in the last 7 days?</li>
            </ul>
        </li>
        <li>Update any stale records — adjust stage, close date, or probability.</li>
        <li>Opportunities with no activity for 14+ days should be reviewed with the account owner.</li>
    </ol>

    <p class="section-label section-label--mt">Stage advancement criteria</p>
    <table class="data-table">
        <thead>
            <tr><th>Move to this stage</th><th>When…</th></tr>
        </thead>
        <tbody>
            <tr><td><span class="badge badge--info">Building</span></td><td data-label="When…">Discovery call completed; requirements being gathered; clear fit identified</td></tr>
            <tr><td><span class="badge badge--info">Review</span></td><td data-label="When…">Solution defined and internally reviewed; ready for proposal</td></tr>
            <tr><td><span class="badge badge--warning">Quote</span></td><td data-label="When…">Formal written proposal or quote delivered to the customer</td></tr>
            <tr><td><span class="badge badge--purple">Negotiating</span></td><td data-label="When…">Customer has indicated intent to purchase; discussing terms</td></tr>
        </tbody>
    </table>
</div>

<!-- Playbook: Closing -->
<div class="card profile-card mb-lg" id="playbook-closing">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-trophy" aria-hidden="true"></i>
        Playbook — Closing a Deal
    </h2>

    <p class="section-label">Closing Won</p>
    <ol class="instructions__list">
        <li>Set the opportunity stage to <strong>Closed Won</strong>.</li>
        <li>Confirm the final amount matches the signed contract — update line items if needed.</li>
        <li>Update the primary contact's lifecycle stage to <strong>Customer</strong>.</li>
        <li>Update the account type to <strong>Customer</strong>.</li>
        <li>Hand off to the delivery or onboarding team with the opportunity record as the reference.</li>
    </ol>

    <p class="section-label section-label--mt">Closing Lost</p>
    <ol class="instructions__list">
        <li>Set the opportunity stage to <strong>Closed Lost</strong>.</li>
        <li>Select a <strong>Loss Reason</strong> — this data drives pipeline analysis and process improvement.</li>
        <li>Do <em>not</em> delete the opportunity — the historical record is valuable.</li>
        <li>If the relationship should be preserved, keep the account active and note the situation in the description.</li>
        <li>Schedule a follow-up task if there is a realistic future opportunity.</li>
    </ol>

    <p class="section-label section-label--mt">Loss reason guide</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Lost to Competitor</dt><dd>Customer chose a competing product or vendor.</dd></div>
        <div class="field-list__row"><dt>Price</dt><dd>Our pricing was outside the customer's budget or perceived value.</dd></div>
        <div class="field-list__row"><dt>Features / Functionality</dt><dd>Our product did not meet a specific requirement.</dd></div>
        <div class="field-list__row"><dt>No Budget</dt><dd>Budget was not approved or was cut.</dd></div>
        <div class="field-list__row"><dt>Project Cancelled</dt><dd>The customer's internal initiative was cancelled or postponed.</dd></div>
        <div class="field-list__row"><dt>Poor Relationship</dt><dd>Relationship breakdown — review what happened and whether recovery is possible.</dd></div>
    </dl>
</div>
