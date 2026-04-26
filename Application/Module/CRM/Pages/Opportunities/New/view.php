<?php
$pageTitle = 'New Opportunity';
$v   = fn(string $f, string $d = '') => htmlspecialchars($_POST[$f] ?? $d, ENT_QUOTES, 'UTF-8');
$sel = fn(string $f, string $opt) => (($_POST[$f] ?? '') === $opt) ? ' selected' : '';
$chk = fn(string $f, string $opt) => in_array($opt, (array) ($_POST[$f] ?? []), true) ? ' checked' : '';
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/opportunities/list">Opportunities</a></p>
        <h1 class="dash-header__title">New Opportunity</h1>
    </div>
    <div>
        <a href="/crm/opportunities/list" class="btn btn--secondary">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Cancel
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($error): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
</div>
<?php endif; ?>

<form method="POST" action="/crm/opportunities/new" novalidate>

    <!-- Core Identity -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-handshake" aria-hidden="true"></i>
            Core Identity
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="opportunity_name">Opportunity Name <span class="form-required">*</span></label>
                <input id="opportunity_name" type="text" name="opportunity_name" class="input"
                       value="<?= $v('opportunity_name') ?>" placeholder="e.g. Acme Corp — Enterprise License" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="opportunity_type">Opportunity Type</label>
                <select id="opportunity_type" name="opportunity_type" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['New Business','Existing Business - Renewal','Existing Business - Upgrade','Existing Business - Downgrade'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('opportunity_type', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="lead_source">Lead Source</label>
                <select id="lead_source" name="lead_source" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Webinar','Trade Show','Referral','Cold Outreach','Inbound Inquiry','Organic Search'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('lead_source', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="account_id">Account</label>
                <div class="account-lookup" data-initial-id="<?= $v('account_id') ?>" data-initial-name="">
                    <input type="text" id="account_search" class="input account-lookup__input" autocomplete="off" placeholder="Type to search accounts…">
                    <input type="hidden" name="account_id" class="account-lookup__value" value="<?= $v('account_id') ?>">
                    <div class="account-lookup__results" hidden></div>
                </div>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="contact_search">Contact</label>
                <div class="contact-lookup" data-initial-id="<?= $v('contact_id') ?>" data-initial-name="">
                    <input type="text" id="contact_search" class="input contact-lookup__input" autocomplete="off" placeholder="Type to search contacts…">
                    <input type="hidden" name="contact_id" class="contact-lookup__value" value="<?= $v('contact_id') ?>">
                    <div class="contact-lookup__results" hidden></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial & Forecast -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-circle-dollar-to-slot" aria-hidden="true"></i>
            Financial &amp; Forecast
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="amount">Amount (USD)</label>
                <input id="amount" type="number" name="amount" class="input"
                       min="0" step="0.01" value="<?= $v('amount') ?>" placeholder="0.00">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="probability">Probability (%)</label>
                <input id="probability" type="number" name="probability" class="input"
                       min="0" max="100" value="<?= $v('probability') ?>" placeholder="0–100">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="forecast_category">Forecast Category</label>
                <select id="forecast_category" name="forecast_category" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Omitted','Pipeline','Best Case','Commit','Closed'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('forecast_category', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="close_date">Close Date</label>
                <input id="close_date" type="date" name="close_date" class="input"
                       value="<?= $v('close_date') ?>">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="billing_term">Billing Term</label>
                <select id="billing_term" name="billing_term" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Monthly','Annual','Multi-Year'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('billing_term', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="plan_type">Plan Type</label>
                <select id="plan_type" name="plan_type" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Basic','Professional','Enterprise','Custom'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('plan_type', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Sales Process -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-diagram-next" aria-hidden="true"></i>
            Sales Process
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="stage">Stage <span class="form-required">*</span></label>
                <select id="stage" name="stage" class="input">
                    <?php foreach (['New','Building','Review','Quote','Negotiating','Closed Won','Closed Lost'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('stage', $opt) ?: ($opt === 'New' && !isset($_POST['stage']) ? ' selected' : '') ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="loss_reason">Loss Reason</label>
                <select id="loss_reason" name="loss_reason" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Lost to Competitor','Price','Features/Functionality','No Budget','Project Cancelled','Poor Relationship'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('loss_reason', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Qualification & Intelligence -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i>
            Qualification &amp; Intelligence
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="decision_timeline">Decision Timeline</label>
                <select id="decision_timeline" name="decision_timeline" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Immediately','1-3 Months','3-6 Months','6+ Months','Unknown'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('decision_timeline', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--checkbox">
                <input id="budget_confirmed" type="checkbox" name="budget_confirmed" value="1"
                       <?= isset($_POST['budget_confirmed']) ? 'checked' : '' ?>>
                <label for="budget_confirmed" class="form-label form-label--inline">Budget Confirmed</label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label">Stakeholders Identified</label>
                <div class="checkbox-group">
                    <?php foreach (['Economic Buyer','Technical Evaluator','Executive Sponsor','Legal/Procurement','End User'] as $opt): ?>
                    <label class="checkbox-group__item">
                        <input type="checkbox" name="stakeholders_identified[]" value="<?= $opt ?>"<?= $chk('stakeholders_identified', $opt) ?>>
                        <?= $opt ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label">Competitor</label>
                <div class="checkbox-group">
                    <?php foreach (['Competitor A','Competitor B','Competitor C','In-House Solution','Status Quo'] as $opt): ?>
                    <label class="checkbox-group__item">
                        <input type="checkbox" name="competitor[]" value="<?= $opt ?>"<?= $chk('competitor', $opt) ?>>
                        <?= $opt ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-note-sticky" aria-hidden="true"></i>
            Notes
        </h2>
        <div class="form-group">
            <textarea name="description" class="input" rows="4"
                      placeholder="Additional context, next steps, or internal notes…"><?= $v('description') ?></textarea>
        </div>
    </div>

    <div class="profile-card__footer">
        <span></span>
        <button type="submit" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Save Opportunity
        </button>
    </div>

</form>

