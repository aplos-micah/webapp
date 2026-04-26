<?php
$pageTitle = htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8');

$val = fn($v) => ($v !== null && $v !== '')
    ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8')
    : '<span class="text-muted">—</span>';

$fld = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$sel = function(string $field, string $opt) use ($product): string {
    return ((string) ($product[$field] ?? '') === $opt) ? ' selected' : '';
};
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/products/list">Products</a></p>
        <h1 class="dash-header__title"><?= $val($product['product_name']) ?></h1>
        <div class="header-badge-row">
            <?php if (!empty($product['sku'])): ?>
            <span class="badge badge--neutral"><?= $val($product['sku']) ?></span>
            <?php endif; ?>
            <?php if (!empty($product['lifecycle_status'])): ?>
            <span class="badge badge--info"><?= $val($product['lifecycle_status']) ?></span>
            <?php endif; ?>
            <?php if ($product['is_active']): ?>
            <span class="badge badge--success">Active</span>
            <?php else: ?>
            <span class="badge badge--neutral">Inactive</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="btn-group">
        <?php if ($editMode): ?>
        <a href="/crm/products/details?id=<?= $id ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
        </a>
        <button type="submit" form="product-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
        </button>
        <?php else: ?>
        <a href="/crm/products/details?id=<?= $id ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <a href="/crm/products/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
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
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= htmlspecialchars($editError, ENT_QUOTES, 'UTF-8') ?></div>
</div>
<?php endif; ?>

<?php if ($editMode): ?>
<form id="product-edit-form" method="POST"
      action="/crm/products/details?id=<?= $id ?>&edit"
      novalidate>
<?php endif; ?>

<!-- Identity & Description -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-box" aria-hidden="true"></i>
        Identity &amp; Description
    </h2>

    <?php if ($editMode): ?>
    <div class="edit-group">
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="product_name">Product Name <span class="form-required">*</span></label>
                <input id="product_name" type="text" name="product_name" class="input"
                       value="<?= $fld($product['product_name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="sku">SKU / Product Code</label>
                <input id="sku" type="text" name="sku" class="input"
                       value="<?= $fld($product['sku']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="product_family">Product Family</label>
                <select id="product_family" name="product_family" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Software','Hardware','Consulting','Training','Maintenance'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('product_family', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="product_type">Product Type</label>
                <select id="product_type" name="product_type" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Inventory','Non-Inventory','Service','Bundle'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('product_type', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="lifecycle_status">Lifecycle Status</label>
                <select id="lifecycle_status" name="lifecycle_status" class="input">
                    <?php foreach (['Draft','Pending Approval','Activated','Archived'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('lifecycle_status', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--checkbox">
                <input id="is_active" type="checkbox" name="is_active" value="1"
                       <?= $product['is_active'] ? 'checked' : '' ?>>
                <label for="is_active" class="form-label form-label--inline">Active</label>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="product_description">Description</label>
            <textarea id="product_description" name="product_description" class="input" rows="4"><?= $fld($product['product_description']) ?></textarea>
        </div>
    </div>
    <?php else: ?>
    <dl class="detail-list">
        <div class="detail-list__row"><dt>Product Name</dt><dd><?= $val($product['product_name']) ?></dd></div>
        <div class="detail-list__row"><dt>SKU</dt><dd><?= $val($product['sku']) ?></dd></div>
        <div class="detail-list__row"><dt>Product Family</dt><dd><?= $val($product['product_family']) ?></dd></div>
        <div class="detail-list__row"><dt>Product Type</dt><dd><?= $val($product['product_type']) ?></dd></div>
        <div class="detail-list__row"><dt>Lifecycle Status</dt><dd><?= $val($product['lifecycle_status']) ?></dd></div>
        <div class="detail-list__row"><dt>Active</dt><dd><?= $product['is_active'] ? 'Yes' : 'No' ?></dd></div>
    </dl>
    <?php if (!empty($product['product_description'])): ?>
    <p class="detail-section-label">Description</p>
    <p class="detail-description"><?= nl2br($val($product['product_description'])) ?></p>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Pricing & Financials -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-circle-dollar-to-slot" aria-hidden="true"></i>
        Pricing &amp; Financials
    </h2>

    <?php if ($editMode): ?>
    <div class="edit-group">
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="list_price">List Price</label>
                <input id="list_price" type="number" name="list_price" class="input"
                       min="0" step="0.01" value="<?= $fld($product['list_price']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="currency">Currency</label>
                <select id="currency" name="currency" class="input">
                    <?php foreach (['USD','EUR','GBP','CAD'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('currency', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="unit_cost">Unit Cost</label>
                <input id="unit_cost" type="number" name="unit_cost" class="input"
                       min="0" step="0.01" value="<?= $fld($product['unit_cost']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="unit_of_measure">Unit of Measure</label>
                <select id="unit_of_measure" name="unit_of_measure" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Each','Hour','Day','License','Box','Month'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('unit_of_measure', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="pricing_model">Pricing Model</label>
                <select id="pricing_model" name="pricing_model" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Flat','Tiered','Volume','Usage-Based'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('pricing_model', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="tax_category">Tax Category</label>
                <select id="tax_category" name="tax_category" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (['Standard','Exempt','Reduced Rate','Service Tax'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('tax_category', $opt) ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <?php else: ?>
    <dl class="detail-list">
        <div class="detail-list__row">
            <dt>List Price</dt>
            <dd><?= $product['list_price'] !== null
                ? htmlspecialchars($product['currency'] ?? 'USD', ENT_QUOTES, 'UTF-8') . ' ' . number_format((float) $product['list_price'], 2)
                : '<span class="text-muted">—</span>' ?></dd>
        </div>
        <div class="detail-list__row">
            <dt>Unit Cost</dt>
            <dd><?= $product['unit_cost'] !== null
                ? htmlspecialchars($product['currency'] ?? 'USD', ENT_QUOTES, 'UTF-8') . ' ' . number_format((float) $product['unit_cost'], 2)
                : '<span class="text-muted">—</span>' ?></dd>
        </div>
        <div class="detail-list__row"><dt>Unit of Measure</dt><dd><?= $val($product['unit_of_measure']) ?></dd></div>
        <div class="detail-list__row"><dt>Pricing Model</dt><dd><?= $val($product['pricing_model']) ?></dd></div>
        <div class="detail-list__row"><dt>Tax Category</dt><dd><?= $val($product['tax_category']) ?></dd></div>
    </dl>
    <?php endif; ?>
</div>

<!-- Technical & Subscription Specs -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
        Technical &amp; Subscription Specs
    </h2>

    <?php if ($editMode): ?>
    <div class="edit-group">
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="subscription_term_months">Subscription Term (months)</label>
                <input id="subscription_term_months" type="number" name="subscription_term_months" class="input"
                       min="0" value="<?= $fld($product['subscription_term_months']) ?>">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="usage_metrics">Usage Metrics</label>
                <input id="usage_metrics" type="text" name="usage_metrics" class="input"
                       value="<?= $fld($product['usage_metrics']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="weight">Weight</label>
                <input id="weight" type="number" name="weight" class="input"
                       min="0" step="0.001" value="<?= $fld($product['weight']) ?>">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="dimensions">Dimensions</label>
                <input id="dimensions" type="text" name="dimensions" class="input"
                       value="<?= $fld($product['dimensions']) ?>">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="material">Material</label>
                <input id="material" type="text" name="material" class="input"
                       value="<?= $fld($product['material']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="competitive_notes">Competitive Notes</label>
            <textarea id="competitive_notes" name="competitive_notes" class="input" rows="4"><?= $fld($product['competitive_notes']) ?></textarea>
        </div>
    </div>
    <?php else: ?>
    <dl class="detail-list">
        <div class="detail-list__row"><dt>Subscription Term</dt><dd><?= $product['subscription_term_months'] !== null ? $val($product['subscription_term_months']) . ' months' : '<span class="text-muted">—</span>' ?></dd></div>
        <div class="detail-list__row"><dt>Usage Metrics</dt><dd><?= $val($product['usage_metrics']) ?></dd></div>
        <div class="detail-list__row"><dt>Weight</dt><dd><?= $val($product['weight']) ?></dd></div>
        <div class="detail-list__row"><dt>Dimensions</dt><dd><?= $val($product['dimensions']) ?></dd></div>
        <div class="detail-list__row"><dt>Material</dt><dd><?= $val($product['material']) ?></dd></div>
    </dl>
    <?php if (!empty($product['competitive_notes'])): ?>
    <p class="detail-section-label">Competitive Notes</p>
    <p class="detail-description"><?= nl2br($val($product['competitive_notes'])) ?></p>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Record (always read-only) -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Record
    </h2>
    <dl class="detail-list">
        <div class="detail-list__row"><dt>Product ID</dt><dd><?= (int) $product['id'] ?></dd></div>
        <div class="detail-list__row"><dt>Created</dt><dd><?= $val($product['created_at']) ?></dd></div>
        <div class="detail-list__row"><dt>Last Updated</dt><dd><?= $val($product['updated_at']) ?></dd></div>
    </dl>
</div>

<?php if ($editMode): ?>
<div class="profile-card__footer profile-card__footer--end">
    <a href="/crm/products/details?id=<?= $id ?>" class="btn btn--ghost">Cancel</a>
    <button type="submit" form="product-edit-form" class="btn btn--primary">
        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
    </button>
</div>
</form>
<?php endif; ?>
