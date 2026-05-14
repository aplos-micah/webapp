<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / Setup</p>
        <h1 class="dash-header__title">Activity Types</h1>
        <p class="dash-header__sub"><?= count($types) ?> type<?= count($types) !== 1 ? 's' : '' ?></p>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($editError): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= $e($editError) ?></div>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="col-num">Avg Cost</th>
                    <th class="col-status">Status</th>
                    <th class="col-actions"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($types)): ?>
                <tr>
                    <td colspan="5" class="data-table__empty">
                        <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                        <p>No activity types yet. Add one below.</p>
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($types as $type): ?>
                <!-- Display row -->
                <tr data-type-id="<?= (int) $type['id'] ?>" style="cursor:pointer"
                    title="Double-click to edit">
                    <td data-label="Name"><strong><?= $e($type['name']) ?></strong></td>
                    <td data-label="Description"><?= $e($type['description']) ?></td>
                    <td data-label="Avg Cost" class="col-num">$<?= number_format((float) $type['average_cost'], 2) ?></td>
                    <td data-label="Status">
                        <span class="badge <?= $type['is_active'] ? 'badge--success' : 'badge--neutral' ?>">
                            <?= $type['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="col-actions">
                        <form method="POST" action="/crm/setup/activitytypes" style="display:inline">
                            <input type="hidden" name="action" value="toggle_active">
                            <input type="hidden" name="id" value="<?= (int) $type['id'] ?>">
                            <button type="submit" class="btn btn--ghost btn--sm">
                                <?= $type['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <!-- Inline edit row -->
                <tr id="type-edit-<?= (int) $type['id'] ?>" class="inline-edit-row" hidden>
                    <td colspan="5">
                        <form method="POST" action="/crm/setup/activitytypes" class="inline-edit-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int) $type['id'] ?>">
                            <div class="form-row">
                                <div class="form-group form-group--grow">
                                    <label class="form-label">Name <span class="form-required">*</span></label>
                                    <input type="text" name="name" class="input"
                                           value="<?= $e($type['name']) ?>" required>
                                </div>
                                <div class="form-group" style="width:9rem">
                                    <label class="form-label">Avg Cost ($)</label>
                                    <input type="number" name="average_cost" class="input"
                                           step="0.01" min="0"
                                           value="<?= $e($type['average_cost']) ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="input"
                                       value="<?= $e($type['description']) ?>">
                            </div>
                            <input type="hidden" name="is_active" value="<?= (int) $type['is_active'] ?>">
                            <div class="inline-edit-actions">
                                <button type="submit" class="btn btn--primary btn--sm">Save</button>
                                <button type="button" class="btn btn--ghost btn--sm type-edit-cancel">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add new type -->
<div class="card mt-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> New Activity Type
    </h2>
    <form method="POST" action="/crm/setup/activitytypes">
        <input type="hidden" name="action" value="create">
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="new-name">Name <span class="form-required">*</span></label>
                <input id="new-name" type="text" name="name" class="input"
                       placeholder="e.g. Phone Call" required>
            </div>
            <div class="form-group" style="width:9rem">
                <label class="form-label" for="new-cost">Avg Cost ($)</label>
                <input id="new-cost" type="number" name="average_cost" class="input"
                       step="0.01" min="0" placeholder="0.00">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="new-desc">Description</label>
            <input id="new-desc" type="text" name="description" class="input"
                   placeholder="When is this type used?">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Add Type
            </button>
        </div>
    </form>
</div>

