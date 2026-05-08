<?php
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$val = fn(string $k) => $editMode ? $e($_POST[$k] ?? $asset[$k] ?? '') : $e($asset[$k] ?? '');

$statusBadge = [
    'Active'      => 'badge--success',
    'In Stock'    => 'badge--info',
    'In Repair'   => 'badge--warning',
    'Retired'     => 'badge--neutral',
    'Lost/Stolen' => 'badge--neutral',
];
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Assets</p>
        <h1 class="dash-header__title"><?= $e($asset['asset_tag'] ?: 'Asset') ?></h1>
        <p class="dash-header__sub"><?= $e($asset['name']) ?></p>
    </div>
    <div class="dash-header__actions">
        <?php if (!$editMode): ?>
        <a href="?id=<?= (int) $asset['id'] ?>&edit" class="btn btn--primary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <?php endif; ?>
        <a href="/assets/assets/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($editError): ?>
<div class="alert alert--error mb-md"><?= $e($editError) ?></div>
<?php endif; ?>

<?php if ($editMode): ?>
<form method="POST" action="/assets/assets/details?id=<?= (int) $asset['id'] ?>">
<?php endif; ?>

<!-- Asset Details card -->
<div class="card profile-card mb-lg">
    <div class="profile-card__header">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-laptop" aria-hidden="true"></i>
            Asset Details
        </h2>
        <?php if (!$editMode): ?>
        <span class="badge <?= $statusBadge[$asset['status']] ?? 'badge--neutral' ?>">
            <?= $e($asset['status']) ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="profile-card__body">
        <?php if ($editMode): ?>

        <div class="form-group">
            <label class="label" for="name">Name <span class="required-star">*</span></label>
            <input type="text" id="name" name="name" class="input" value="<?= $val('name') ?>" required autocomplete="off">
        </div>

        <div class="form-row">
            <div class="form-group form-group--half">
                <label class="label" for="type">Type</label>
                <select id="type" name="type" class="input">
                    <?php foreach (Asset::TYPES as $t): ?>
                    <option value="<?= $e($t) ?>"<?= ($asset['type'] ?? '') === $t ? ' selected' : '' ?>><?= $e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--half">
                <label class="label" for="category">Category</label>
                <select id="category" name="category" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (Asset::CATEGORIES as $c): ?>
                    <option value="<?= $e($c) ?>"<?= ($asset['category'] ?? '') === $c ? ' selected' : '' ?>><?= $e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="label" for="status">Status</label>
            <select id="status" name="status" class="input">
                <?php foreach (Asset::STATUSES as $s): ?>
                <option value="<?= $e($s) ?>"<?= ($asset['status'] ?? '') === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group form-group--half">
                <label class="label" for="manufacturer">Manufacturer</label>
                <input type="text" id="manufacturer" name="manufacturer" class="input" value="<?= $val('manufacturer') ?>" autocomplete="off">
            </div>
            <div class="form-group form-group--half">
                <label class="label" for="model">Model</label>
                <input type="text" id="model" name="model" class="input" value="<?= $val('model') ?>" autocomplete="off">
            </div>
        </div>

        <div class="form-group">
            <label class="label" for="serial_number">Serial Number</label>
            <input type="text" id="serial_number" name="serial_number" class="input" value="<?= $val('serial_number') ?>" autocomplete="off">
        </div>

        <div class="form-group">
            <label class="label" for="notes">Notes</label>
            <textarea id="notes" name="notes" class="input textarea" rows="4"><?= $val('notes') ?></textarea>
        </div>

        <?php else: ?>

        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Asset Tag</dt>
                <dd><?= $e($asset['asset_tag']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Name</dt>
                <dd><?= $e($asset['name']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Type</dt>
                <dd><?= $e($asset['type'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Category</dt>
                <dd><?= $e($asset['category'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Manufacturer</dt>
                <dd><?= $e($asset['manufacturer'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Model</dt>
                <dd><?= $e($asset['model'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Serial Number</dt>
                <dd><?= $e($asset['serial_number'] ?? '—') ?></dd>
            </div>
            <?php if (!empty($asset['notes'])): ?>
            <div class="detail-list__row">
                <dt>Notes</dt>
                <dd><?= nl2br($e($asset['notes'])) ?></dd>
            </div>
            <?php endif; ?>
        </dl>

        <?php endif; ?>
    </div>
</div>

<!-- Assignment & Location card -->
<div class="card profile-card mb-lg">
    <div class="profile-card__header">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-user-check" aria-hidden="true"></i>
            Assignment &amp; Location
        </h2>
    </div>
    <div class="profile-card__body">
        <?php if ($editMode): ?>

        <div class="form-group">
            <label class="label" for="assigned_to">Assigned To</label>
            <select id="assigned_to" name="assigned_to" class="input">
                <option value="">— Unassigned —</option>
                <?php foreach ($assignableUsers as $u): ?>
                <option value="<?= (int) $u['id'] ?>"<?= (int) ($asset['assigned_to'] ?? 0) === (int) $u['id'] ? ' selected' : '' ?>>
                    <?= $e($u['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="label" for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id" class="input">
                <option value="">— None —</option>
                <?php foreach ($assignableUsers as $u): ?>
                <option value="<?= (int) $u['id'] ?>"<?= (int) ($asset['owner_id'] ?? 0) === (int) $u['id'] ? ' selected' : '' ?>>
                    <?= $e($u['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="label" for="location">Location</label>
            <input type="text" id="location" name="location" class="input" value="<?= $val('location') ?>" autocomplete="off">
        </div>

        <?php else: ?>

        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Assigned To</dt>
                <dd><?= $e($asset['assigned_name'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Owner</dt>
                <dd><?= $e($asset['owner_name'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Location</dt>
                <dd><?= $e($asset['location'] ?? '—') ?></dd>
            </div>
        </dl>

        <?php endif; ?>
    </div>
</div>

<!-- Financial card -->
<div class="card profile-card mb-lg">
    <div class="profile-card__header">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-dollar-sign" aria-hidden="true"></i>
            Financial
        </h2>
    </div>
    <div class="profile-card__body">
        <?php if ($editMode): ?>

        <div class="form-row">
            <div class="form-group form-group--half">
                <label class="label" for="purchase_date">Purchase Date</label>
                <input type="date" id="purchase_date" name="purchase_date" class="input" value="<?= $val('purchase_date') ?>">
            </div>
            <div class="form-group form-group--half">
                <label class="label" for="warranty_expires">Warranty Expires</label>
                <input type="date" id="warranty_expires" name="warranty_expires" class="input" value="<?= $val('warranty_expires') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="label" for="cost">Cost</label>
            <input type="number" id="cost" name="cost" class="input" value="<?= $val('cost') ?>" step="0.01" min="0" placeholder="0.00">
        </div>

        <?php else: ?>

        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Purchase Date</dt>
                <dd><?= $e(substr($asset['purchase_date'] ?? '', 0, 10) ?: '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Warranty Expires</dt>
                <dd><?= $e(substr($asset['warranty_expires'] ?? '', 0, 10) ?: '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Cost</dt>
                <dd><?= !empty($asset['cost']) ? '$' . number_format((float) $asset['cost'], 2) : '—' ?></dd>
            </div>
        </dl>

        <?php endif; ?>
    </div>
</div>

<!-- Record card -->
<div class="card profile-card mb-lg">
    <div class="profile-card__header">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            Record
        </h2>
    </div>
    <div class="profile-card__body">
        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Asset Tag</dt>
                <dd><?= $e($asset['asset_tag']) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Created</dt>
                <dd><?= $e(substr($asset['created_at'] ?? '', 0, 10)) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Last Updated</dt>
                <dd><?= $e(substr($asset['updated_at'] ?? '', 0, 10)) ?></dd>
            </div>
        </dl>
    </div>
</div>

<?php if ($editMode): ?>
<div class="form-actions">
    <button type="submit" class="btn btn--primary">Save Changes</button>
    <a href="/assets/assets/details?id=<?= (int) $asset['id'] ?>" class="btn btn--ghost">Cancel</a>
</div>
</form>
<?php endif; ?>
