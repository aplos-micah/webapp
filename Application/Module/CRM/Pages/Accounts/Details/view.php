<?php
$pageTitle = htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8');

// Helper: render a value or an em-dash placeholder
$val = fn($v) => ($v !== null && $v !== '')
    ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8')
    : '<span class="text-muted">—</span>';

// Helper: safe string for form field values
$field = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / Accounts</p>
        <h1 class="dash-header__title"><?= $val($account['name']) ?></h1>
        <?php if (!empty($account['status'])): ?>
        <span class="badge badge--info"><?= $val($account['status']) ?></span>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:0.5rem;align-items:center;">
        <?php if ($editMode): ?>
        <a href="/crm/accounts/details?id=<?= $account['id'] ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            Cancel
        </a>
        <button type="submit" form="account-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Save Changes
        </button>
        <?php else: ?>
        <a href="/crm/accounts/details?id=<?= $account['id'] ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i>
            Edit
        </a>
        <a href="/crm/accounts/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Back
        </a>
        <?php endif; ?>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if (!empty($editError)): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= htmlspecialchars($editError, ENT_QUOTES, 'UTF-8') ?></div>
</div>
<?php endif; ?>

<div class="account-detail-layout">

    <!-- ══════════════════════════════════════════════════════════════════════
         LEFT COLUMN — Related content (drag to reorder)
         ══════════════════════════════════════════════════════════════════════ -->
    <div class="account-detail-layout__related" id="related-tiles">
        <?php
        $tiles = [
            'opportunities' => [
                'icon'  => 'fa-solid fa-handshake',
                'label' => 'Opportunities',
                'new'   => true,
                'empty' => ['fa-regular fa-handshake', 'No opportunities yet.'],
            ],
            'contacts' => [
                'icon'  => 'fa-solid fa-address-book',
                'label' => 'Contacts',
                'new'   => true,
                'empty' => ['fa-regular fa-address-book', 'No contacts yet.'],
            ],
            'locations' => [
                'icon'  => 'fa-solid fa-map-location-dot',
                'label' => 'Locations',
                'new'   => true,
                'empty' => ['fa-solid fa-map-location-dot', 'No locations yet.'],
            ],
            'leads' => [
                'icon'  => 'fa-solid fa-bolt',
                'label' => 'Leads',
                'new'   => true,
                'empty' => ['fa-regular fa-bolt', 'No leads yet.'],
            ],
            'performance' => [
                'icon'  => 'fa-solid fa-chart-line',
                'label' => 'Customer Performance',
                'new'   => false,
                'empty' => ['fa-regular fa-chart-bar', 'No performance history yet.'],
            ],
        ];
        foreach ($tileOrder as $key):
            $tile = $tiles[$key];
        ?>
        <div class="card related-card" draggable="true" data-tile="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
            <div class="related-card__header">
                <h2 class="related-card__title">
                    <i class="related-card__drag-handle fa-solid fa-grip-vertical" aria-hidden="true" title="Drag to reorder"></i>
                    <i class="<?= $tile['icon'] ?>" aria-hidden="true"></i>
                    <?= htmlspecialchars($tile['label'], ENT_QUOTES, 'UTF-8') ?>
                </h2>
                <?php if ($key === 'contacts'): ?>
                <a href="/crm/contacts/new" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> New
                </a>
                <?php elseif ($key === 'opportunities'): ?>
                <a href="/crm/opportunities/new" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> New
                </a>
                <?php elseif ($key === 'locations'): ?>
                <button type="button" id="loc-add-btn" class="btn btn--ghost btn--sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Add Location
                </button>
                <?php elseif ($tile['new']): ?>
                <button class="btn btn--ghost btn--sm" disabled title="Coming soon">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> New
                </button>
                <?php endif; ?>
            </div>
            <?php if ($key === 'contacts'): ?>
            <?= $contactsWidget->render((int) $account['id']) ?>
            <?php elseif ($key === 'performance'): ?>
            <?= $performanceWidget->render((int) $account['id']) ?>
            <?php elseif ($key === 'opportunities'): ?>
            <?= $opportunitiesWidget->render((int) $account['id']) ?>
            <?php elseif ($key === 'locations'): ?>
            <?= $locationsWidget->render((int) $account['id'], '/crm/accounts/details?id=' . (int) $account['id']) ?>
            <?php else: ?>
            <div class="related-card__empty">
                <i class="<?= $tile['empty'][0] ?>" aria-hidden="true"></i>
                <p><?= htmlspecialchars($tile['empty'][1], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         RIGHT COLUMN — Account details
         ══════════════════════════════════════════════════════════════════════ -->
    <?php if ($editMode): ?>
    <form id="account-edit-form" method="POST"
          action="/crm/accounts/details?id=<?= $account['id'] ?>&edit"
          novalidate>
    <?php endif; ?>
    <div class="account-detail-layout__info">

        <!-- Account Information -->
        <div class="card profile-card">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-building" aria-hidden="true"></i>
                Account Information
            </h2>

            <!-- Identity -->
            <p class="detail-section-label">Identity</p>
            <?php if ($editMode): ?>
            <div class="edit-group">
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="name">Account Name <span class="form-required">*</span></label>
                        <input id="name" type="text" name="name" class="input"
                               value="<?= $field($account['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="account_number">Account Number</label>
                        <input id="account_number" type="text" name="account_number" class="input"
                               value="<?= $field($account['account_number']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="site">Account Site</label>
                        <input id="site" type="text" name="site" class="input"
                               value="<?= $field($account['site']) ?>" placeholder="e.g. HQ, London Office">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="website">Company Domain</label>
                        <input id="website" type="url" name="website" class="input"
                               value="<?= $field($account['website']) ?>" placeholder="https://example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="parent_id">Parent Account ID</label>
                    <input id="parent_id" type="number" name="parent_id" class="input" min="1"
                           value="<?= $field($account['parent_id']) ?>">
                </div>
            </div>
            <?php else: ?>
            <dl class="detail-list">
                <div class="detail-list__row">
                    <dt>Account Name</dt>
                    <dd><?= $val($account['name']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Account Number</dt>
                    <dd><?= $val($account['account_number']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Account Site</dt>
                    <dd><?= $val($account['site']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Parent Account</dt>
                    <dd><?= $val($account['parent_id']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Company Domain</dt>
                    <dd>
                        <?php if (!empty($account['website'])): ?>
                        <a href="<?= $val($account['website']) ?>" target="_blank" rel="noopener noreferrer" class="table-link">
                            <?= $val($account['website']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>
            <?php endif; ?>

            <!-- Classification -->
            <p class="detail-section-label">Classification</p>
            <?php if ($editMode): ?>
            <div class="edit-group">
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="type">Account Type</label>
                        <input id="type" type="text" name="type" class="input"
                               value="<?= $field($account['type']) ?>" placeholder="e.g. Prospect, Customer, Partner">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="status">Status</label>
                        <input id="status" type="text" name="status" class="input"
                               value="<?= $field($account['status']) ?>" placeholder="e.g. Active, Onboarding, Churned">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="industry">Industry</label>
                        <input id="industry" type="text" name="industry" class="input"
                               value="<?= $field($account['industry']) ?>" placeholder="e.g. Technology, Finance">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="ownership">Ownership</label>
                        <input id="ownership" type="text" name="ownership" class="input"
                               value="<?= $field($account['ownership']) ?>" placeholder="e.g. Public, Private, Government">
                    </div>
                </div>
            </div>
            <?php else: ?>
            <dl class="detail-list">
                <div class="detail-list__row">
                    <dt>Account Type</dt>
                    <dd><?= $val($account['type']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Status</dt>
                    <dd><?= $val($account['status']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Industry</dt>
                    <dd><?= $val($account['industry']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Ownership</dt>
                    <dd><?= $val($account['ownership']) ?></dd>
                </div>
            </dl>
            <?php endif; ?>

            <!-- Business -->
            <p class="detail-section-label">Business</p>
            <?php if ($editMode): ?>
            <div class="edit-group">
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="annual_revenue">Annual Revenue</label>
                        <input id="annual_revenue" type="number" name="annual_revenue" class="input"
                               min="0" step="0.01" value="<?= $field($account['annual_revenue']) ?>"
                               placeholder="e.g. 5000000.00">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="employee_count">Number of Employees</label>
                        <input id="employee_count" type="number" name="employee_count" class="input"
                               min="0" value="<?= $field($account['employee_count']) ?>" placeholder="e.g. 250">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="owner_id">Account Owner ID</label>
                        <input id="owner_id" type="number" name="owner_id" class="input"
                               min="1" value="<?= $field($account['owner_id']) ?>">
                    </div>
                    <div class="form-group form-group--grow">
                        <label class="form-label" for="last_activity_at">Last Interaction</label>
                        <input id="last_activity_at" type="datetime-local" name="last_activity_at" class="input"
                               value="<?= $field(!empty($account['last_activity_at']) ? date('Y-m-d\TH:i', strtotime($account['last_activity_at'])) : '') ?>">
                    </div>
                </div>
            </div>
            <?php else: ?>
            <dl class="detail-list">
                <div class="detail-list__row">
                    <dt>Annual Revenue</dt>
                    <dd><?= $account['annual_revenue'] !== null ? '$' . number_format((float) $account['annual_revenue'], 2) : '<span class="text-muted">—</span>' ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Number of Employees</dt>
                    <dd><?= $val($account['employee_count']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Account Owner</dt>
                    <dd><?= $val($account['owner_id']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Last Interaction</dt>
                    <dd><?= $val($account['last_activity_at']) ?></dd>
                </div>
            </dl>
            <?php endif; ?>

            <!-- Description -->
            <p class="detail-section-label">Description</p>
            <?php if ($editMode): ?>
            <div class="edit-group">
                <div class="form-group">
                    <textarea name="description" class="input" rows="4"
                              placeholder="Brief summary of the company's business…"><?= $field($account['description']) ?></textarea>
                </div>
            </div>
            <?php elseif (!empty($account['description'])): ?>
            <p class="detail-description"><?= nl2br($val($account['description'])) ?></p>
            <?php else: ?>
            <p class="detail-description"><span class="text-muted">—</span></p>
            <?php endif; ?>

            <!-- Record (always read-only) -->
            <p class="detail-section-label">Record</p>
            <dl class="detail-list">
                <div class="detail-list__row">
                    <dt>Account ID</dt>
                    <dd><?= $val($account['id']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Created</dt>
                    <dd><?= $val($account['created_at']) ?></dd>
                </div>
                <div class="detail-list__row">
                    <dt>Last Updated</dt>
                    <dd><?= $val($account['updated_at']) ?></dd>
                </div>
            </dl>
        </div>

        <!-- Addresses -->
        <div class="card profile-card">
            <h2 class="profile-card__title">
                <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
                Addresses
            </h2>

            <?php if ($editMode): ?>
            <div class="edit-group">
                <div class="form-group">
                    <label class="form-label" for="billing_address">Billing Address</label>
                    <textarea id="billing_address" name="billing_address" class="input" rows="3"
                              placeholder="Street, City, State, ZIP, Country"><?= $field($account['billing_address']) ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="shipping_address">Shipping Address</label>
                    <textarea id="shipping_address" name="shipping_address" class="input" rows="3"
                              placeholder="Street, City, State, ZIP, Country"><?= $field($account['shipping_address']) ?></textarea>
                </div>
            </div>
            <?php else: ?>
            <dl class="detail-list">
                <div class="detail-list__row detail-list__row--block">
                    <dt>Billing Address</dt>
                    <dd><?= !empty($account['billing_address']) ? nl2br($val($account['billing_address'])) : '<span class="text-muted">—</span>' ?></dd>
                </div>
                <div class="detail-list__row detail-list__row--block">
                    <dt>Shipping Address</dt>
                    <dd><?= !empty($account['shipping_address']) ? nl2br($val($account['shipping_address'])) : '<span class="text-muted">—</span>' ?></dd>
                </div>
            </dl>
            <?php endif; ?>
        </div>

        <?php if ($editMode): ?>
        <div class="profile-card__footer" style="justify-content:flex-end;gap:0.5rem;">
            <a href="/crm/accounts/details?id=<?= $account['id'] ?>" class="btn btn--ghost">Cancel</a>
            <button type="submit" form="account-edit-form" class="btn btn--primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                Save Changes
            </button>
        </div>
        <?php endif; ?>

    </div>
    <?php if ($editMode): ?>
    </form>
    <?php endif; ?>

</div>

<script>
(function () {
    const container = document.getElementById('related-tiles');
    if (!container) return;

    let dragging = null;

    container.addEventListener('dragstart', function (e) {
        dragging = e.target.closest('[data-tile]');
        if (!dragging) return;
        dragging.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    container.addEventListener('dragend', function () {
        if (dragging) dragging.classList.remove('is-dragging');
        container.querySelectorAll('[data-tile]').forEach(el => el.classList.remove('drag-over'));
        dragging = null;
        saveOrder();
    });

    container.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const target = e.target.closest('[data-tile]');
        if (!target || target === dragging) return;
        container.querySelectorAll('[data-tile]').forEach(el => el.classList.remove('drag-over'));
        target.classList.add('drag-over');
        const after = e.clientY > target.getBoundingClientRect().top + target.getBoundingClientRect().height / 2;
        container.insertBefore(dragging, after ? target.nextSibling : target);
    });

    container.addEventListener('dragleave', function (e) {
        const target = e.target.closest('[data-tile]');
        if (target) target.classList.remove('drag-over');
    });

    function saveOrder() {
        const order = [...container.querySelectorAll('[data-tile]')].map(el => el.dataset.tile);
        fetch('/crm/accounts/savelayout', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ order })
        });
    }
}());
</script>
