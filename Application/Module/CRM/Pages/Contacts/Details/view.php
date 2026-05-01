<?php
$fullName  = trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''));
$pageTitle = $fullName ?: 'Contact Details';

// Helper: escape and display a field value, or a dash if empty
$val = function(string $field, string $default = '—') use ($contact): string {
    $v = $contact[$field] ?? '';
    return $v !== '' && $v !== null ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') : $default;
};

// Helper: render a read/edit field pair
$field = function(
    string $label,
    string $name,
    string $type = 'text',
    array  $options = [],
    string $placeholder = ''
) use ($contact, $editMode): void {
    $raw       = $contact[$name] ?? '';
    $displayed = ($raw !== '' && $raw !== null) ? htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8') : '—';
    $inputVal  = htmlspecialchars((string) ($raw ?? ''), ENT_QUOTES, 'UTF-8');

    echo '<div class="field-list__row">';
    echo '<dt class="field-list__label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</dt>';

    if ($editMode) {
        echo '<dd class="field-list__value edit-section">';
        if ($type === 'select') {
            echo '<select name="' . $name . '" class="input">';
            echo '<option value="">— Select —</option>';
            foreach ($options as $opt) {
                $sel = ((string) $raw === $opt) ? ' selected' : '';
                echo '<option value="' . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . '"' . $sel . '>'
                   . htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') . '</option>';
            }
            echo '</select>';
        } elseif ($type === 'textarea') {
            echo '<textarea name="' . $name . '" class="input" rows="4">' . $inputVal . '</textarea>';
        } else {
            $ph = $placeholder ? ' placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"' : '';
            echo '<input type="' . $type . '" name="' . $name . '" class="input" value="' . $inputVal . '"' . $ph . '>';
        }
        echo '</dd>';
    } else {
        echo '<dd class="field-list__value">' . $displayed . '</dd>';
    }
    echo '</div>';
};
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/contacts/list">Contacts</a></p>
        <h1 class="dash-header__title"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if (!empty($contact['job_title']) || $accountName): ?>
        <p class="dash-header__sub">
            <?= $val('job_title') ?><?= (!empty($contact['job_title']) && $accountName) ? ' · ' : '' ?><?= htmlspecialchars($accountName ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
        <?php endif; ?>
    </div>
    <div>
        <?php if ($editMode): ?>
        <a href="/crm/contacts/details?id=<?= $id ?>" class="btn btn--ghost">Cancel</a>
        <?php else: ?>
        <a href="/crm/contacts/details?id=<?= $id ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <?php endif; ?>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if (!empty($_SESSION['_flash'])): ?>
<?php $flash = $_SESSION['_flash']; unset($_SESSION['_flash']); ?>
<div class="alert alert--<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?> mb-lg">
    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<?php if ($editError): ?>
<div class="alert alert--error mb-lg">
    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
    <?= htmlspecialchars($editError, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<?php if ($editMode): ?>
<form method="POST" action="/crm/contacts/details?id=<?= $id ?>">
<?php endif; ?>

<div class="detail-layout">

    <!-- Left: Related tiles -->
    <div class="related-tiles" id="related-tiles" data-save-url="/crm/contacts/savelayout">
        <?php foreach ($tileOrder as $tile): ?>
        <?php
            $tileLabels = [
                'opportunities' => ['label' => 'Opportunities', 'icon' => 'fa-solid fa-handshake'],
                'accounts'      => ['label' => 'Accounts',      'icon' => 'fa-solid fa-building'],
                'activities'    => ['label' => 'Activities',    'icon' => 'fa-solid fa-calendar-check'],
                'notes'         => ['label' => 'Notes',         'icon' => 'fa-solid fa-note-sticky'],
            ];
            $meta = $tileLabels[$tile] ?? ['label' => ucfirst($tile), 'icon' => 'fa-solid fa-circle'];
        ?>
        <div class="tile-card card" draggable="true" data-tile="<?= $tile ?>">
            <div class="tile-card__header">
                <span class="tile-card__grip">
                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                </span>
                <i class="<?= $meta['icon'] ?> tile-card__icon" aria-hidden="true"></i>
                <h3 class="tile-card__title"><?= $meta['label'] ?></h3>
            </div>
            <div class="tile-card__body">
                <p class="tile-card__placeholder">No <?= strtolower($meta['label']) ?> yet.</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Right: Contact info -->
    <div class="account-info">

        <!-- Basic Identity -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title section-label">Basic Identity</h2>
            </div>
            <div class="card__body">
                <dl class="field-list">
                    <?php $field('First Name', 'first_name') ?>
                    <?php $field('Last Name',  'last_name') ?>
                    <?php $field('Job Title',  'job_title') ?>
                    <div class="field-list__row">
                        <dt class="field-list__label">Account</dt>
                        <?php if ($editMode): ?>
                        <dd class="field-list__value edit-section">
                            <div class="entity-lookup"
                                 data-initial-id="<?= htmlspecialchars((string) ($contact['account_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                 data-initial-name="<?= htmlspecialchars($accountName ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="text" class="input entity-lookup__input" autocomplete="off" placeholder="Type to search accounts…">
                                <input type="hidden" name="account_id" class="entity-lookup__value" value="<?= htmlspecialchars((string) ($contact['account_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="entity-lookup__results" hidden></div>
                            </div>
                        </dd>
                        <?php else: ?>
                        <dd class="field-list__value">
                            <?php if (!empty($contact['account_id']) && $accountName): ?>
                            <a href="/crm/accounts/details?id=<?= (int) $contact['account_id'] ?>" class="table-link">
                                <?= htmlspecialchars($accountName, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <?php elseif (!empty($contact['account_id'])): ?>
                            #<?= (int) $contact['account_id'] ?>
                            <?php else: ?>
                            —
                            <?php endif; ?>
                        </dd>
                        <?php endif; ?>
                    </div>
                    <?php if ($editMode): ?>
                    <?php $field('LinkedIn URL', 'linkedin_url', 'url', [], 'https://linkedin.com/in/…') ?>
                    <?php else: ?>
                    <div class="field-list__row">
                        <dt class="field-list__label">LinkedIn URL</dt>
                        <dd class="field-list__value">
                            <?php if (!empty($contact['linkedin_url'])): ?>
                            <a href="<?= $val('linkedin_url') ?>" target="_blank" rel="noopener noreferrer" class="table-link"><?= $val('linkedin_url') ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Communication Channels -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title section-label">Communication Channels</h2>
            </div>
            <div class="card__body">
                <dl class="field-list">
                    <?php if ($editMode): ?>
                    <?php $field('Primary Email', 'email', 'email') ?>
                    <?php else: ?>
                    <div class="field-list__row">
                        <dt class="field-list__label">Primary Email</dt>
                        <dd class="field-list__value">
                            <?php if (!empty($contact['email'])): ?>
                            <a href="mailto:<?= $val('email') ?>" class="table-link"><?= $val('email') ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </dd>
                    </div>
                    <?php endif; ?>
                    <?php $field('Work Phone',   'work_phone',   'tel') ?>
                    <?php $field('Mobile Phone', 'mobile_phone', 'tel') ?>
                    <?php $field('Mailing Address', 'mailing_address', 'textarea') ?>
                    <?php $field('Communication Preference', 'communication_preference', 'select', ['Email', 'Phone', 'SMS']) ?>
                </dl>
            </div>
        </div>

        <!-- Relationship & Lifecycle -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title section-label">Relationship &amp; Lifecycle</h2>
            </div>
            <div class="card__body">
                <dl class="field-list">
                    <?php $field('Lifecycle Stage', 'lifecycle_stage', 'select', ['Lead', 'MQL', 'SQL', 'Customer', 'Evangelist']) ?>
                    <?php $field('Status',          'status',          'select', ['Active', 'Inactive', 'Bounced']) ?>
                    <?php $field('Lead Source',     'lead_source') ?>
                    <?php $field('Last Contact Date', 'last_contact_at', 'datetime-local') ?>
                    <?php if ($editMode): ?>
                    <div class="field-list__row">
                        <dt class="field-list__label">Owner ID</dt>
                        <dd class="field-list__value edit-section">
                            <input type="number" name="owner_id" class="input" value="<?= htmlspecialchars((string) ($contact['owner_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </dd>
                    </div>
                    <?php else: ?>
                    <div class="field-list__row">
                        <dt class="field-list__label">Owner ID</dt>
                        <dd class="field-list__value"><?= $val('owner_id') ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Engagement & Behavior -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title section-label">Engagement &amp; Behavior</h2>
            </div>
            <div class="card__body">
                <dl class="field-list">
                    <?php $field('Lead Score',    'lead_score',    'number') ?>
                    <?php $field('Last Activity', 'last_activity') ?>
                    <?php $field('Interaction History', 'interaction_history', 'textarea') ?>
                </dl>
            </div>
        </div>

        <!-- Segmentation & Custom Data -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title section-label">Segmentation &amp; Custom Data</h2>
            </div>
            <div class="card__body">
                <dl class="field-list">
                    <?php $field('Industry',     'industry') ?>
                    <?php $field('Buying Role',  'buying_role',  'select', ['Decision Maker', 'Influencer', 'Champion']) ?>
                    <?php $field('Renewal Date', 'renewal_date', 'date') ?>
                </dl>
            </div>
        </div>

        <!-- Record (always read-only) -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title section-label">Record</h2>
            </div>
            <div class="card__body">
                <dl class="field-list">
                    <div class="field-list__row">
                        <dt class="field-list__label">Contact ID</dt>
                        <dd class="field-list__value"><?= (int) $contact['id'] ?></dd>
                    </div>
                    <div class="field-list__row">
                        <dt class="field-list__label">Created</dt>
                        <dd class="field-list__value"><?= $val('created_at') ?></dd>
                    </div>
                    <div class="field-list__row">
                        <dt class="field-list__label">Last Updated</dt>
                        <dd class="field-list__value"><?= $val('updated_at') ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <?php if ($editMode): ?>
        <div class="form-actions">
            <a href="/crm/contacts/details?id=<?= $id ?>" class="btn btn--ghost">Cancel</a>
            <button type="submit" class="btn btn--primary">Save Changes</button>
        </div>
        <?php endif; ?>

    </div><!-- /.account-info -->

</div><!-- /.detail-layout -->

<?php if ($editMode): ?>
</form>
<?php endif; ?>


