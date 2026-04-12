<?php
$pageTitle = 'New Product';
$v = fn(string $f, string $d = '') => htmlspecialchars($_POST[$f] ?? $d, ENT_QUOTES, 'UTF-8');
$sel = fn(string $f, string $opt) => (($_POST[$f] ?? '') === $opt) ? ' selected' : '';
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/products/list">Products</a></p>
        <h1 class="dash-header__title">New Product</h1>
    </div>
    <div>
        <a href="/crm/products/list" class="btn btn--secondary">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Cancel
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

<form method="POST" action="/crm/products/new" novalidate>

    <!-- Identity & Description -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-box" aria-hidden="true"></i>
            Identity &amp; Description
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="product_name">Product Name <span class="form-required">*</span></label>
                <input id="product_name" type="text" name="product_name" class="input"
                       value="<?= $v('product_name') ?>" placeholder="e.g. Enterprise License" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="sku">SKU / Product Code</label>
                <input id="sku" type="text" name="sku" class="input"
                       value="<?= $v('sku') ?>" placeholder="e.g. PRD-00123">
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
                    <option value="<?= $opt ?>"<?= $sel('lifecycle_status', $opt) ?: ($opt === 'Draft' && !isset($_POST['lifecycle_status']) ? ' selected' : '') ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;padding-top:1.6rem;">
                <input id="is_active" type="checkbox" name="is_active" value="1"
                       <?= (!isset($_POST['product_name']) || isset($_POST['is_active'])) ? 'checked' : '' ?>>
                <label for="is_active" class="form-label" style="margin:0;">Active (available to sales reps)</label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="product_description">Product Description</label>
            <textarea id="product_description" name="product_description" class="input" rows="4"
                      placeholder="Detailed features and value proposition…"><?= $v('product_description') ?></textarea>
        </div>
    </div>

    <!-- Pricing & Financials -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-circle-dollar-to-slot" aria-hidden="true"></i>
            Pricing &amp; Financials
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="list_price">List Price</label>
                <input id="list_price" type="number" name="list_price" class="input"
                       min="0" step="0.01" value="<?= $v('list_price') ?>" placeholder="0.00">
            </div>
            <div class="form-group">
                <label class="form-label" for="currency">Currency</label>
                <select id="currency" name="currency" class="input">
                    <?php foreach (['USD','EUR','GBP','CAD'] as $opt): ?>
                    <option value="<?= $opt ?>"<?= $sel('currency', $opt) ?: ($opt === 'USD' && !isset($_POST['currency']) ? ' selected' : '') ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="unit_cost">Unit Cost</label>
                <input id="unit_cost" type="number" name="unit_cost" class="input"
                       min="0" step="0.01" value="<?= $v('unit_cost') ?>" placeholder="0.00">
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

    <!-- Technical & Subscription Specs -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
            Technical &amp; Subscription Specs
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="subscription_term_months">Subscription Term (months)</label>
                <input id="subscription_term_months" type="number" name="subscription_term_months" class="input"
                       min="0" value="<?= $v('subscription_term_months') ?>" placeholder="e.g. 12">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="usage_metrics">Usage Metrics</label>
                <input id="usage_metrics" type="text" name="usage_metrics" class="input"
                       value="<?= $v('usage_metrics') ?>" placeholder="e.g. Seat Size, API Calls, Data Limit">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="weight">Weight</label>
                <input id="weight" type="number" name="weight" class="input"
                       min="0" step="0.001" value="<?= $v('weight') ?>" placeholder="e.g. 1.500">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="dimensions">Dimensions</label>
                <input id="dimensions" type="text" name="dimensions" class="input"
                       value="<?= $v('dimensions') ?>" placeholder="e.g. 10 x 5 x 3 cm">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="material">Material</label>
                <input id="material" type="text" name="material" class="input"
                       value="<?= $v('material') ?>" placeholder="e.g. Aluminum">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="competitive_notes">Competitive Notes</label>
            <textarea id="competitive_notes" name="competitive_notes" class="input" rows="4"
                      placeholder="Comparison data against key competitors…"><?= $v('competitive_notes') ?></textarea>
        </div>
    </div>

    <div class="profile-card__footer">
        <span></span>
        <button type="submit" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Save Product
        </button>
    </div>

</form>
