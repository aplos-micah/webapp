<?php
$pageTitle = 'New Contact';

// Helper: repopulate submitted value or empty string
$val = fn(string $field, string $default = '') => htmlspecialchars($_POST[$field] ?? $default, ENT_QUOTES, 'UTF-8');
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/contacts/list">Contacts</a></p>
        <h1 class="dash-header__title">New Contact</h1>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($error): ?>
<div class="alert alert--error mb-lg">
    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<form method="POST" action="/crm/contacts/new">

    <!-- Basic Identity -->
    <div class="card mb-lg">
        <div class="card__header">
            <h2 class="card__title">Basic Identity</h2>
        </div>
        <div class="card__body">
            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" class="input" value="<?= $val('first_name') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" class="input" value="<?= $val('last_name') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="job_title">Job Title</label>
                    <input type="text" id="job_title" name="job_title" class="input" value="<?= $val('job_title') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="account_search">Account</label>
                    <div class="entity-lookup" data-initial-id="<?= $val('account_id') ?>" data-initial-name="">
                        <input type="text" id="account_search" class="input entity-lookup__input" autocomplete="off" placeholder="Type to search accounts…">
                        <input type="hidden" name="account_id" class="entity-lookup__value" value="<?= $val('account_id') ?>">
                        <div class="entity-lookup__results" hidden></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="linkedin_url">LinkedIn URL</label>
                    <input type="url" id="linkedin_url" name="linkedin_url" class="input" value="<?= $val('linkedin_url') ?>" placeholder="https://linkedin.com/in/…">
                </div>
            </div>
        </div>
    </div>

    <!-- Communication Channels -->
    <div class="card mb-lg">
        <div class="card__header">
            <h2 class="card__title">Communication Channels</h2>
        </div>
        <div class="card__body">
            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label class="form-label" for="email">Primary Email</label>
                    <input type="email" id="email" name="email" class="input" value="<?= $val('email') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="communication_preference">Communication Preference</label>
                    <select id="communication_preference" name="communication_preference" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Email', 'Phone', 'SMS'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($_POST['communication_preference'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="work_phone">Work Phone</label>
                    <input type="tel" id="work_phone" name="work_phone" class="input" value="<?= $val('work_phone') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="mobile_phone">Mobile Phone</label>
                    <input type="tel" id="mobile_phone" name="mobile_phone" class="input" value="<?= $val('mobile_phone') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="mailing_address">Mailing Address</label>
                <textarea id="mailing_address" name="mailing_address" class="input" rows="3"><?= $val('mailing_address') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Relationship & Lifecycle -->
    <div class="card mb-lg">
        <div class="card__header">
            <h2 class="card__title">Relationship &amp; Lifecycle</h2>
        </div>
        <div class="card__body">
            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label class="form-label" for="lifecycle_stage">Lifecycle Stage</label>
                    <select id="lifecycle_stage" name="lifecycle_stage" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Lead', 'MQL', 'SQL', 'Customer', 'Evangelist'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($_POST['lifecycle_stage'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        <?php foreach (['Active', 'Inactive', 'Bounced'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($_POST['status'] ?? 'Active') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lead_source">Lead Source</label>
                    <input type="text" id="lead_source" name="lead_source" class="input" value="<?= $val('lead_source') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_contact_at">Last Contact Date</label>
                    <input type="datetime-local" id="last_contact_at" name="last_contact_at" class="input" value="<?= $val('last_contact_at') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement & Behavior -->
    <div class="card mb-lg">
        <div class="card__header">
            <h2 class="card__title">Engagement &amp; Behavior</h2>
        </div>
        <div class="card__body">
            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label class="form-label" for="lead_score">Lead Score</label>
                    <input type="number" id="lead_score" name="lead_score" class="input" value="<?= $val('lead_score', '0') ?>" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_activity">Last Activity</label>
                    <input type="text" id="last_activity" name="last_activity" class="input" value="<?= $val('last_activity') ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="interaction_history">Interaction History (JSON)</label>
                <textarea id="interaction_history" name="interaction_history" class="input" rows="4" placeholder='[{"date":"2025-01-01","note":"Initial call"}]'><?= $val('interaction_history') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Segmentation & Custom Data -->
    <div class="card mb-lg">
        <div class="card__header">
            <h2 class="card__title">Segmentation &amp; Custom Data</h2>
        </div>
        <div class="card__body">
            <div class="form-grid form-grid--2">
                <div class="form-group">
                    <label class="form-label" for="industry">Industry</label>
                    <input type="text" id="industry" name="industry" class="input" value="<?= $val('industry') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="buying_role">Buying Role</label>
                    <select id="buying_role" name="buying_role" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Decision Maker', 'Influencer', 'Champion'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($_POST['buying_role'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="renewal_date">Renewal Date</label>
                    <input type="date" id="renewal_date" name="renewal_date" class="input" value="<?= $val('renewal_date') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="form-actions">
        <a href="/crm/contacts/list" class="btn btn--ghost">Cancel</a>
        <button type="submit" class="btn btn--primary">Create Contact</button>
    </div>

</form>

