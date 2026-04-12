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

    echo '<div class="detail-list__row">';
    echo '<dt class="detail-list__label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</dt>';

    if ($editMode) {
        echo '<dd class="detail-list__value edit-group">';
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
        echo '<dd class="detail-list__value">' . $displayed . '</dd>';
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

<div class="account-detail-layout">

    <!-- Left: Related tiles -->
    <div class="related-tiles" id="related-tiles">
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
        <div class="related-card card" draggable="true" data-tile="<?= $tile ?>">
            <div class="related-card__header">
                <span class="related-card__grip">
                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                </span>
                <i class="<?= $meta['icon'] ?> related-card__icon" aria-hidden="true"></i>
                <h3 class="related-card__title"><?= $meta['label'] ?></h3>
            </div>
            <div class="related-card__body">
                <p class="related-card__placeholder">No <?= strtolower($meta['label']) ?> yet.</p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Right: Contact info -->
    <div class="account-info">

        <!-- Basic Identity -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title detail-section-label">Basic Identity</h2>
            </div>
            <div class="card__body">
                <dl class="detail-list">
                    <?php $field('First Name', 'first_name') ?>
                    <?php $field('Last Name',  'last_name') ?>
                    <?php $field('Job Title',  'job_title') ?>
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Account</dt>
                        <?php if ($editMode): ?>
                        <dd class="detail-list__value edit-group">
                            <div class="account-lookup"
                                 data-initial-id="<?= htmlspecialchars((string) ($contact['account_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                 data-initial-name="<?= htmlspecialchars($accountName ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <input type="text" class="input account-lookup__input" autocomplete="off" placeholder="Type to search accounts…">
                                <input type="hidden" name="account_id" class="account-lookup__value" value="<?= htmlspecialchars((string) ($contact['account_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="account-lookup__results" hidden></div>
                            </div>
                        </dd>
                        <?php else: ?>
                        <dd class="detail-list__value">
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
                    <div class="detail-list__row">
                        <dt class="detail-list__label">LinkedIn URL</dt>
                        <dd class="detail-list__value">
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
                <h2 class="card__title detail-section-label">Communication Channels</h2>
            </div>
            <div class="card__body">
                <dl class="detail-list">
                    <?php if ($editMode): ?>
                    <?php $field('Primary Email', 'email', 'email') ?>
                    <?php else: ?>
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Primary Email</dt>
                        <dd class="detail-list__value">
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
                <h2 class="card__title detail-section-label">Relationship &amp; Lifecycle</h2>
            </div>
            <div class="card__body">
                <dl class="detail-list">
                    <?php $field('Lifecycle Stage', 'lifecycle_stage', 'select', ['Lead', 'MQL', 'SQL', 'Customer', 'Evangelist']) ?>
                    <?php $field('Status',          'status',          'select', ['Active', 'Inactive', 'Bounced']) ?>
                    <?php $field('Lead Source',     'lead_source') ?>
                    <?php $field('Last Contact Date', 'last_contact_at', 'datetime-local') ?>
                    <?php if ($editMode): ?>
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Owner ID</dt>
                        <dd class="detail-list__value edit-group">
                            <input type="number" name="owner_id" class="input" value="<?= htmlspecialchars((string) ($contact['owner_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </dd>
                    </div>
                    <?php else: ?>
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Owner ID</dt>
                        <dd class="detail-list__value"><?= $val('owner_id') ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Engagement & Behavior -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title detail-section-label">Engagement &amp; Behavior</h2>
            </div>
            <div class="card__body">
                <dl class="detail-list">
                    <?php $field('Lead Score',    'lead_score',    'number') ?>
                    <?php $field('Last Activity', 'last_activity') ?>
                    <?php $field('Interaction History', 'interaction_history', 'textarea') ?>
                </dl>
            </div>
        </div>

        <!-- Segmentation & Custom Data -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title detail-section-label">Segmentation &amp; Custom Data</h2>
            </div>
            <div class="card__body">
                <dl class="detail-list">
                    <?php $field('Industry',     'industry') ?>
                    <?php $field('Buying Role',  'buying_role',  'select', ['Decision Maker', 'Influencer', 'Champion']) ?>
                    <?php $field('Renewal Date', 'renewal_date', 'date') ?>
                </dl>
            </div>
        </div>

        <!-- Record (always read-only) -->
        <div class="card mb-lg">
            <div class="card__header">
                <h2 class="card__title detail-section-label">Record</h2>
            </div>
            <div class="card__body">
                <dl class="detail-list">
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Contact ID</dt>
                        <dd class="detail-list__value"><?= (int) $contact['id'] ?></dd>
                    </div>
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Created</dt>
                        <dd class="detail-list__value"><?= $val('created_at') ?></dd>
                    </div>
                    <div class="detail-list__row">
                        <dt class="detail-list__label">Last Updated</dt>
                        <dd class="detail-list__value"><?= $val('updated_at') ?></dd>
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

</div><!-- /.account-detail-layout -->

<?php if ($editMode): ?>
</form>
<?php endif; ?>

<?php if ($editMode): include __DIR__ . '/../_partials/account-lookup.js.php'; endif; ?>

<script>
(function () {
    const container = document.getElementById('related-tiles');
    if (!container) return;

    let dragging = null;

    container.addEventListener('dragstart', e => {
        dragging = e.target.closest('[data-tile]');
        if (dragging) dragging.classList.add('is-dragging');
    });

    container.addEventListener('dragend', () => {
        if (dragging) dragging.classList.remove('is-dragging');
        dragging = null;
        saveOrder();
    });

    container.addEventListener('dragover', e => {
        e.preventDefault();
        const target = e.target.closest('[data-tile]');
        if (!target || target === dragging) return;

        container.querySelectorAll('[data-tile]').forEach(el => el.classList.remove('drag-over'));
        target.classList.add('drag-over');

        const rect   = target.getBoundingClientRect();
        const middle = rect.top + rect.height / 2;
        if (e.clientY < middle) {
            container.insertBefore(dragging, target);
        } else {
            container.insertBefore(dragging, target.nextSibling);
        }
    });

    container.addEventListener('dragleave', e => {
        const target = e.target.closest('[data-tile]');
        if (target) target.classList.remove('drag-over');
    });

    function saveOrder() {
        const order = [...container.querySelectorAll('[data-tile]')].map(el => el.dataset.tile);
        fetch('/crm/contacts/savelayout', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ order }),
        });
    }
})();
</script>
