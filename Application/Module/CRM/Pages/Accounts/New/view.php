<?php $pageTitle = 'New Account'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / Accounts</p>
        <h1 class="dash-header__title">New Account</h1>
    </div>
    <div>
        <a href="/crm/accounts/list" class="btn btn--secondary">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Cancel
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if (!empty($error)): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
</div>
<?php endif; ?>

<form method="POST" action="/crm/accounts/new" novalidate>

    <!-- Section: Core Info -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-building" aria-hidden="true"></i>
            Account Information
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="name">Account Name <span class="form-required">*</span></label>
                <input id="name" type="text" name="name" class="input"
                    value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Acme Corporation" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="account_number">Account Number</label>
                <input id="account_number" type="text" name="account_number" class="input"
                    value="<?= htmlspecialchars($_POST['account_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. ACC-00123">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="site">Account Site</label>
                <input id="site" type="text" name="site" class="input"
                    value="<?= htmlspecialchars($_POST['site'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. HQ, London Office">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="website">Company Domain</label>
                <input id="website" type="url" name="website" class="input"
                    value="<?= htmlspecialchars($_POST['website'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="https://example.com">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="type">Account Type</label>
                <input id="type" type="text" name="type" class="input"
                    value="<?= htmlspecialchars($_POST['type'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Prospect, Customer, Partner">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="status">Status</label>
                <input id="status" type="text" name="status" class="input"
                    value="<?= htmlspecialchars($_POST['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Active, Onboarding, Churned">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="industry">Industry</label>
                <input id="industry" type="text" name="industry" class="input"
                    value="<?= htmlspecialchars($_POST['industry'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Technology, Finance, Healthcare">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="ownership">Ownership</label>
                <input id="ownership" type="text" name="ownership" class="input"
                    value="<?= htmlspecialchars($_POST['ownership'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. Public, Private, Government">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="employee_count">Number of Employees</label>
                <input id="employee_count" type="number" name="employee_count" class="input" min="0"
                    value="<?= htmlspecialchars($_POST['employee_count'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. 250">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="annual_revenue">Annual Revenue</label>
                <input id="annual_revenue" type="number" name="annual_revenue" class="input" min="0" step="0.01"
                    value="<?= htmlspecialchars($_POST['annual_revenue'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="e.g. 5000000.00">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="input" rows="3"
                placeholder="Brief summary of the company's business…"><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
    </div>

    <!-- Section: Addresses -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
            Addresses
        </h2>

        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="billing_address">Billing Address</label>
                <textarea id="billing_address" name="billing_address" class="input" rows="3"
                    placeholder="Street, City, State, ZIP, Country"><?= htmlspecialchars($_POST['billing_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="shipping_address">Shipping Address</label>
                <textarea id="shipping_address" name="shipping_address" class="input" rows="3"
                    placeholder="Street, City, State, ZIP, Country"><?= htmlspecialchars($_POST['shipping_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Save -->
    <div class="profile-card__footer">
        <span></span>
        <button type="submit" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Save Account
        </button>
    </div>

</form>
