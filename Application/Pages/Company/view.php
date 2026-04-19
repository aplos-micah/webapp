<?php
$isNew     = empty($data['company']);
$company   = $data['company'] ?? [];
$error     = $data['error']   ?? null;
$action    = $isNew ? 'create_company' : 'update_company';
$pageTitle = $isNew ? 'Define Your Company' : 'My Company';
$val       = fn(string $key) => htmlspecialchars($company[$key] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Account</p>
        <h1 class="dash-header__title"><?= $pageTitle ?></h1>
        <p class="dash-header__sub">
            <?= $isNew ? 'Add the company you represent.' : 'Manage your company information.' ?>
        </p>
    </div>
</div>

<hr class="divider--green mb-xl">

<div class="profile-layout">

    <div class="card profile-card">

        <h2 class="profile-card__title">
            <i class="fa-solid fa-building" aria-hidden="true"></i>
            Company Information
        </h2>

        <?php if ($error): ?>
        <div class="alert alert--warning mb-md" role="alert">
            <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="alert__body"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/company" novalidate>
            <input type="hidden" name="_action" value="<?= $action ?>">

            <div class="form-group">
                <label class="form-label" for="name">Company Name <span aria-hidden="true">*</span></label>
                <input id="name" type="text" name="name" class="input"
                    value="<?= $val('name') ?>"
                    placeholder="e.g. Acme Corporation"
                    autocomplete="organization"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input id="phone" type="tel" name="phone" class="input"
                    value="<?= $val('phone') ?>"
                    placeholder="e.g. +1 (555) 000-0000"
                    autocomplete="tel">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="input"
                    value="<?= $val('email') ?>"
                    placeholder="e.g. info@company.com"
                    autocomplete="email">
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <input id="address" type="text" name="address" class="input"
                    value="<?= $val('address') ?>"
                    placeholder="Street address"
                    autocomplete="street-address">
            </div>

            <div class="form-group">
                <label class="form-label" for="city">City</label>
                <input id="city" type="text" name="city" class="input"
                    value="<?= $val('city') ?>"
                    placeholder="City"
                    autocomplete="address-level2">
            </div>

            <div class="form-group">
                <label class="form-label" for="state">State</label>
                <input id="state" type="text" name="state" class="input"
                    value="<?= $val('state') ?>"
                    placeholder="State / Province"
                    autocomplete="address-level1">
            </div>

            <div class="form-group">
                <label class="form-label" for="zip">ZIP / Postal Code</label>
                <input id="zip" type="text" name="zip" class="input"
                    value="<?= $val('zip') ?>"
                    placeholder="ZIP or postal code"
                    autocomplete="postal-code">
            </div>

            <div class="form-group">
                <label class="form-label" for="website">Website</label>
                <input id="website" type="url" name="website" class="input"
                    value="<?= $val('website') ?>"
                    placeholder="https://example.com"
                    autocomplete="url">
            </div>

            <div class="profile-card__footer">
                <span></span>
                <button type="submit" class="btn btn--primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    <?= $isNew ? 'Create Company' : 'Save Changes' ?>
                </button>
            </div>

        </form>
    </div>

</div>
