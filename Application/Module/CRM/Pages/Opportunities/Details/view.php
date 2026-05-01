<?php
$pageTitle = htmlspecialchars($opp['opportunity_name'], ENT_QUOTES, 'UTF-8');

$val = fn($v) => ($v !== null && $v !== '')
    ? htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8')
    : '<span class="text-muted">—</span>';

$fld = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$sel = function(string $field, string $opt) use ($opp): string {
    return ((string) ($opp[$field] ?? '') === $opt) ? ' selected' : '';
};

$chkArr = function(array $arr, string $opt): string {
    return in_array($opt, $arr, true) ? ' checked' : '';
};

$stageBadgeClass = [
    'New'         => 'badge--neutral',
    'Building'    => 'badge--info',
    'Review'      => 'badge--info',
    'Quote'       => 'badge--warning',
    'Negotiating' => 'badge--purple',
    'Closed Won'  => 'badge--success',
    'Closed Lost' => 'badge--neutral',
];
$stageCls = $stageBadgeClass[$opp['stage'] ?? ''] ?? 'badge--neutral';

// Progress bar — ordered pipeline stages (Closed Lost is off-track, handled separately)
$pipelineStages  = ['New', 'Building', 'Review', 'Quote', 'Negotiating', 'Closed Won'];
$currentStage    = $opp['stage'] ?? 'New';
$isClosedLost    = $currentStage === 'Closed Lost';
$isClosed        = $isClosedLost || $currentStage === 'Closed Won';
$activeIndex     = array_search($currentStage, $pipelineStages, true);
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/opportunities/list">Opportunities</a></p>
        <h1 class="dash-header__title"><?= $val($opp['opportunity_name']) ?></h1>
        <?php
            $subParts = array_filter([
                !empty($opp['account_name']) ? htmlspecialchars($opp['account_name'], ENT_QUOTES, 'UTF-8') : null,
                !empty($opp['contact_name']) ? htmlspecialchars(trim($opp['contact_name']), ENT_QUOTES, 'UTF-8') : null,
            ]);
            if ($subParts): ?>
        <p class="dash-header__sub"><?= implode(' · ', $subParts) ?></p>
        <?php endif; ?>
        <div class="header-badge-row">
            <?php if (!empty($opp['stage'])): ?>
            <span class="badge <?= $stageCls ?>"><?= $val($opp['stage']) ?></span>
            <?php endif; ?>
            <?php if ($opp['amount'] !== null): ?>
            <span class="badge badge--neutral">USD <?= number_format((float) $opp['amount'], 2) ?></span>
            <?php endif; ?>
            <?php if ($opp['probability'] !== null): ?>
            <span class="badge badge--neutral"><?= (int) $opp['probability'] ?>% probability</span>
            <?php endif; ?>
            <?php if (!empty($opp['close_date'])): ?>
            <span class="badge badge--neutral">Closes <?= $val($opp['close_date']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="btn-group">
        <?php if ($isClosed): ?>
        <span class="badge badge--neutral">
            <i class="fa-solid fa-lock" aria-hidden="true"></i> Read Only
        </span>
        <a href="/crm/opportunities/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
        <?php elseif ($editMode): ?>
        <a href="/crm/opportunities/details?id=<?= $id ?>" class="btn btn--ghost">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel
        </a>
        <button type="submit" form="opp-edit-form" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
        </button>
        <?php else: ?>
        <a href="/crm/opportunities/details?id=<?= $id ?>&edit" class="btn btn--secondary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <a href="/crm/opportunities/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
        <?php endif; ?>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Stage Progress Bar -->
<div class="step-progress <?= $isClosedLost ? 'step-progress--error' : '' ?> mb-xl">
    <?php if ($isClosedLost): ?>
        <div class="step-progress__lost">
            <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
            Closed Lost
        </div>
    <?php else: ?>
        <?php foreach ($pipelineStages as $i => $stage): ?>
        <?php
            $isDone   = $activeIndex !== false && $i < $activeIndex;
            $isActive = $activeIndex !== false && $i === $activeIndex;
            $cls      = $isDone ? 'is-done' : ($isActive ? 'is-active' : '');
        ?>
        <div class="step-progress__step <?= $cls ?>">
            <div class="step-progress__node">
                <?php if ($isDone): ?>
                <i class="fa-solid fa-check" aria-hidden="true"></i>
                <?php else: ?>
                <span><?= $i + 1 ?></span>
                <?php endif; ?>
            </div>
            <div class="step-progress__label"><?= htmlspecialchars($stage, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php if ($i < count($pipelineStages) - 1): ?>
        <div class="step-progress__connector <?= $isDone ? 'is-done' : '' ?>"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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

<!-- Mobile tab bar (hidden on desktop via CSS) -->
<div class="tab-bar tab-bar--split" id="tab-bar" role="tablist">
    <button class="tab-bar__btn is-active" data-tab="detail" role="tab" aria-selected="true">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i> Details
    </button>
    <button class="tab-bar__btn" data-tab="line-items" role="tab" aria-selected="false">
        <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i> Products
    </button>
</div>

<!-- =========================================================================
     Two-column layout: LEFT 2/3 = product line items | RIGHT 1/3 = details
     Mobile: tabbed — Details tab (default), Products tab
     ========================================================================= -->
<div class="split-layout">

    <!-- =====================================================================
         LEFT / Products tab: Product Line Items (2/3)
         ===================================================================== -->
    <div class="split-layout__main" id="opp-panel-line-items">

        <div class="card tile-card">
            <div class="tile-card__header">
                <h2 class="tile-card__title">
                    <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
                    Product Line Items
                </h2>
                <?php if (!$isClosed): ?>
                <button type="button" id="line-item-toggle" class="btn btn--sm btn--primary">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Add Line Item
                </button>
                <?php endif; ?>
            </div>

            <?php if (!empty($lineItems)): ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Total</th>
                            <th>Schedule</th>
                            <th>Ship To</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineItems as $li): ?>
                        <?php $liId = (int) $li['id']; ?>
                        <tr data-li-id="<?= $liId ?>">
                            <td>
                                <span class="item-name"><?= htmlspecialchars($li['product_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($li['service_date']): ?>
                                <span class="item-detail">Svc: <?= htmlspecialchars($li['service_date'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <?php if ($li['subscription_term']): ?>
                                <span class="item-detail"><?= (int) $li['subscription_term'] ?> mo.</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted"><?= $li['sku'] ? htmlspecialchars($li['sku'], ENT_QUOTES, 'UTF-8') : '—' ?></span></td>
                            <td class="text-right"><?= number_format((float) $li['unit_price'], 2) ?></td>
                            <td class="text-right"><?= rtrim(rtrim(number_format((float) $li['quantity'], 4), '0'), '.') ?></td>
                            <td class="text-right">
                                <?php if ((float) $li['discount_percentage'] > 0): ?>
                                <?= number_format((float) $li['discount_percentage'], 1) ?>%
                                <?php elseif ((float) $li['discount_amount'] > 0): ?>
                                $<?= number_format((float) $li['discount_amount'], 2) ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="text-right"><strong><?= number_format((float) $li['total_price'], 2) ?></strong></td>
                            <td><span class="badge badge--neutral"><?= htmlspecialchars($li['revenue_schedule_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <?php if (!empty($li['ship_to_location_id'])): ?>
                                <?php
                                $stParts = array_filter([
                                    $li['ship_to_location_name'] ?? null,
                                    $li['ship_to_city'] ?? null,
                                    $li['ship_to_state'] ?? null,
                                ]);
                                ?>
                                <span class="text-sm"><?= htmlspecialchars(implode(', ', $stParts), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="td-nowrap">
                                <?php if (!$isClosed): ?>
                                <button type="button" class="btn btn--ghost btn--sm li-edit-btn"
                                        data-li="<?= $liId ?>" title="Edit">
                                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                </button>
                                <form method="POST"
                                      action="/crm/opportunities/details?id=<?= $id ?><?= $editMode ? '&edit' : '' ?>"
                                      onsubmit="return confirm('Remove this line item?')"
                                      class="d-inline">
                                    <input type="hidden" name="_action"      value="remove_line_item">
                                    <input type="hidden" name="line_item_id" value="<?= $liId ?>">
                                    <button type="submit" class="btn btn--ghost btn--sm" title="Remove">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!$isClosed): ?>
                        <tr class="inline-edit-row" id="li-edit-<?= $liId ?>" hidden>
                            <td colspan="9" class="inline-edit-cell">
                                <form method="POST"
                                      action="/crm/opportunities/details?id=<?= $id ?><?= $editMode ? '&edit' : '' ?>"
                                      class="line-item-form"
                                      novalidate>
                                    <input type="hidden" name="_action"      value="update_line_item">
                                    <input type="hidden" name="line_item_id" value="<?= $liId ?>">
                                    <div class="inline-edit-grid">
                                        <div>
                                            <div class="form-row">
                                                <div class="form-group form-group--grow">
                                                    <label class="form-label" for="edit_name_<?= $liId ?>">Product Name <span class="form-required">*</span></label>
                                                    <input id="edit_name_<?= $liId ?>" type="text" name="product_name" class="input"
                                                           value="<?= $fld($li['product_name']) ?>" required>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label class="form-label" for="edit_up_<?= $liId ?>">Unit Price</label>
                                                    <input id="edit_up_<?= $liId ?>" type="number" name="unit_price" class="input"
                                                           min="0" step="0.01" value="<?= (float) $li['unit_price'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="edit_qty_<?= $liId ?>">Qty</label>
                                                    <input id="edit_qty_<?= $liId ?>" type="number" name="quantity" class="input"
                                                           min="0.0001" step="any" value="<?= (float) $li['quantity'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="edit_total_<?= $liId ?>">Total</label>
                                                    <input id="edit_total_<?= $liId ?>" type="number" name="total_price" class="input" readonly
                                                           value="<?= (float) $li['total_price'] ?>"
                                                           class="input input--readonly-bg">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label class="form-label" for="edit_dp_<?= $liId ?>">Disc %</label>
                                                    <input id="edit_dp_<?= $liId ?>" type="number" name="discount_percentage" class="input"
                                                           min="0" max="100" step="0.01" value="<?= (float) $li['discount_percentage'] ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label" for="edit_da_<?= $liId ?>">Disc $</label>
                                                    <input id="edit_da_<?= $liId ?>" type="number" name="discount_amount" class="input"
                                                           min="0" step="0.01" value="<?= (float) $li['discount_amount'] ?>">
                                                </div>
                                                <div class="form-group form-group--grow">
                                                    <label class="form-label" for="edit_sched_<?= $liId ?>">Schedule</label>
                                                    <select id="edit_sched_<?= $liId ?>" name="revenue_schedule_type" class="input">
                                                        <?php foreach (['One-time', 'Monthly', 'Quarterly', 'Annually'] as $opt): ?>
                                                        <option value="<?= $opt ?>"<?= ($li['revenue_schedule_type'] === $opt) ? ' selected' : '' ?>><?= $opt ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label class="form-label" for="edit_sd_<?= $liId ?>">Service Date</label>
                                                    <input id="edit_sd_<?= $liId ?>" type="date" name="service_date" class="input"
                                                           value="<?= $fld($li['service_date'] ?? '') ?>">
                                                </div>
                                                <div class="form-group form-group--grow">
                                                    <label class="form-label" for="edit_st_<?= $liId ?>">Sub Term (mo.)</label>
                                                    <input id="edit_st_<?= $liId ?>" type="number" name="subscription_term" class="input"
                                                           min="1" step="1" value="<?= $li['subscription_term'] !== null ? (int) $li['subscription_term'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group form-group--grow">
                                                    <label class="form-label" for="edit_shipto_<?= $liId ?>">Ship To Location</label>
                                                    <select id="edit_shipto_<?= $liId ?>" name="ship_to_location_id" class="input">
                                                        <option value="">— None —</option>
                                                        <?php foreach ($shipToLocs as $sl): ?>
                                                        <option value="<?= (int) $sl['id'] ?>"
                                                            <?= ((int) ($li['ship_to_location_id'] ?? 0) === (int) $sl['id']) ? ' selected' : '' ?>>
                                                            <?= htmlspecialchars(
                                                                ($sl['location_name'] ?: implode(', ', array_filter([$sl['street_address_1'], $sl['city']]))),
                                                                ENT_QUOTES, 'UTF-8'
                                                            ) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="button" class="btn btn--ghost btn--sm li-edit-cancel">Cancel</button>
                                        <button type="submit" class="btn btn--primary btn--sm">
                                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="items-total">
                            <td colspan="5" class="text-right"><strong>Grand Total</strong></td>
                            <td class="text-right"><strong>USD <?= number_format($lineItemsTotal, 2) ?></strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php else: ?>
            <div class="tile-card__empty">
                <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
                <p>No products added yet.</p>
            </div>
            <?php endif; ?>

            <?php if (!$isClosed): ?>
            <!-- Inline add form (toggled) -->
            <div id="line-item-form-wrap" hidden>
                <hr class="divider--green divider--compact">
                <form method="POST"
                      action="/crm/opportunities/details?id=<?= $id ?><?= $editMode ? '&edit' : '' ?>"
                      class="line-item-form"
                      novalidate>
                    <input type="hidden" name="_action" value="add_line_item">
                    <div class="li-add-inner">

                        <div class="form-row">
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="product_search">Product <span class="form-required">*</span></label>
                                <div class="entity-lookup"
                                     data-initial-id=""
                                     data-initial-name=""
                                     data-name-target="li_product_name"
                                     data-price-target="li_unit_price">
                                    <input type="text" id="product_search" class="input entity-lookup__input"
                                           autocomplete="off" placeholder="Search by name or SKU…">
                                    <input type="hidden" name="product_definition_id" class="entity-lookup__value" value="">
                                    <div class="entity-lookup__results" hidden></div>
                                </div>
                            </div>
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_product_name">Line Item Name <span class="form-required">*</span></label>
                                <input id="li_product_name" type="text" name="product_name" class="input"
                                       placeholder="Customise if needed" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_unit_price">Unit Price</label>
                                <input id="li_unit_price" type="number" name="unit_price" class="input"
                                       min="0" step="0.01" placeholder="0.00" value="">
                            </div>
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_quantity">Quantity</label>
                                <input id="li_quantity" type="number" name="quantity" class="input"
                                       min="0.0001" step="any" value="1">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_discount_pct">Discount %</label>
                                <input id="li_discount_pct" type="number" name="discount_percentage" class="input"
                                       min="0" max="100" step="0.01" placeholder="0">
                            </div>
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_discount_amt">Discount $</label>
                                <input id="li_discount_amt" type="number" name="discount_amount" class="input"
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_total">Total Price</label>
                                <input id="li_total" type="number" name="total_price" class="input input--readonly-bg"
                                       readonly placeholder="0.00">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_service_date">Service Date</label>
                                <input id="li_service_date" type="date" name="service_date" class="input">
                            </div>
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_sub_term">Subscription Term (months)</label>
                                <input id="li_sub_term" type="number" name="subscription_term" class="input"
                                       min="1" step="1" placeholder="e.g. 12">
                            </div>
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_schedule">Revenue Schedule</label>
                                <select id="li_schedule" name="revenue_schedule_type" class="input">
                                    <option value="One-time" selected>One-time</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Annually">Annually</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group form-group--grow">
                                <label class="form-label" for="li_ship_to">Ship To Location</label>
                                <select id="li_ship_to" name="ship_to_location_id" class="input">
                                    <option value="">— None —</option>
                                    <?php foreach ($shipToLocs as $sl): ?>
                                    <option value="<?= (int) $sl['id'] ?>">
                                        <?= htmlspecialchars(
                                            ($sl['location_name'] ?: implode(', ', array_filter([$sl['street_address_1'], $sl['city']]))),
                                            ENT_QUOTES, 'UTF-8'
                                        ) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions--sm">
                            <button type="button" id="line-item-cancel" class="btn btn--ghost btn--sm">Cancel</button>
                            <button type="submit" class="btn btn--primary btn--sm">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i> Add to Opportunity
                            </button>
                        </div>

                    </div>
                </form>
            </div>
            <?php endif; ?>

        </div><!-- /.tile-card -->

    </div><!-- /.split-layout__main -->

    <!-- =====================================================================
         RIGHT / Details tab: Opportunity detail cards (1/3)
         Desktop: visible by default. Mobile: active when Details tab selected.
         ===================================================================== -->
    <div class="split-layout__sidebar is-active" id="opp-panel-detail">

    <?php if ($editMode): ?>
    <form id="opp-edit-form" method="POST"
          action="/crm/opportunities/details?id=<?= $id ?>&edit"
          novalidate>
    <?php endif; ?>

    <!-- Core Identity -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-handshake" aria-hidden="true"></i>
            Core Identity
        </h2>

        <?php if ($editMode): ?>
        <div class="edit-section">
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="opportunity_name">Opportunity Name <span class="form-required">*</span></label>
                    <input id="opportunity_name" type="text" name="opportunity_name" class="input"
                           value="<?= $fld($opp['opportunity_name']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="opportunity_type">Type</label>
                    <select id="opportunity_type" name="opportunity_type" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['New Business','Existing Business - Renewal','Existing Business - Upgrade','Existing Business - Downgrade'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('opportunity_type', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="lead_source">Lead Source</label>
                    <select id="lead_source" name="lead_source" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Webinar','Trade Show','Referral','Cold Outreach','Inbound Inquiry','Organic Search'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('lead_source', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="account_search">Account</label>
                    <div class="entity-lookup"
                         data-initial-id="<?= $fld($opp['account_id']) ?>"
                         data-initial-name="<?= $fld($opp['account_name'] ?? '') ?>">
                        <input type="text" id="account_search" class="input entity-lookup__input"
                               autocomplete="off" placeholder="Type to search accounts…">
                        <input type="hidden" name="account_id" class="entity-lookup__value"
                               value="<?= $fld($opp['account_id']) ?>">
                        <div class="entity-lookup__results" hidden></div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="bill_to_location_id">
                        Bill To Location <span class="form-required">*</span>
                    </label>
                    <?php if (empty($billToLocs)): ?>
                    <p class="form-hint">No Bill To locations on the linked account. <a href="/crm/accounts/details?id=<?= (int) ($opp['account_id'] ?? 0) ?>">Add one first.</a></p>
                    <input type="hidden" name="bill_to_location_id" value="">
                    <?php else: ?>
                    <select id="bill_to_location_id" name="bill_to_location_id" class="input" required>
                        <option value="">— Select Bill To —</option>
                        <?php foreach ($billToLocs as $bl): ?>
                        <option value="<?= (int) $bl['id'] ?>"
                            <?= ((int) ($opp['bill_to_location_id'] ?? 0) === (int) $bl['id']) ? ' selected' : '' ?>>
                            <?= htmlspecialchars(
                                ($bl['location_name'] ?: implode(', ', array_filter([$bl['street_address_1'], $bl['city']]))),
                                ENT_QUOTES, 'UTF-8'
                            ) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="contact_search">Contact</label>
                    <div class="entity-lookup"
                         data-initial-id="<?= $fld($opp['contact_id']) ?>"
                         data-initial-name="<?= $fld(trim($opp['contact_name'] ?? '')) ?>">
                        <input type="text" id="contact_search" class="input entity-lookup__input"
                               autocomplete="off" placeholder="Type to search contacts…">
                        <input type="hidden" name="contact_id" class="entity-lookup__value"
                               value="<?= $fld($opp['contact_id']) ?>">
                        <div class="entity-lookup__results" hidden></div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <dl class="field-list">
            <div class="field-list__row"><dt>Name</dt><dd><?= $val($opp['opportunity_name']) ?></dd></div>
            <div class="field-list__row"><dt>Type</dt><dd><?= $val($opp['opportunity_type']) ?></dd></div>
            <div class="field-list__row"><dt>Lead Source</dt><dd><?= $val($opp['lead_source']) ?></dd></div>
            <div class="field-list__row">
                <dt>Account</dt>
                <dd>
                    <?php if (!empty($opp['account_id']) && !empty($opp['account_name'])): ?>
                    <a href="/crm/accounts/details?id=<?= (int) $opp['account_id'] ?>" class="table-link">
                        <?= $val($opp['account_name']) ?>
                    </a>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </dd>
            </div>
            <div class="field-list__row">
                <dt>Bill To</dt>
                <dd>
                    <?php if (!empty($opp['bill_to_location_id'])): ?>
                    <?php
                    $btParts = array_filter([
                        $opp['bill_to_location_name'] ?? null,
                        $opp['bill_to_street'] ?? null,
                        implode(', ', array_filter([$opp['bill_to_city'] ?? null, $opp['bill_to_state'] ?? null, $opp['bill_to_zip'] ?? null])) ?: null,
                    ]);
                    ?>
                    <span><?= htmlspecialchars(implode(' · ', $btParts), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </dd>
            </div>
            <div class="field-list__row">
                <dt>Contact</dt>
                <dd>
                    <?php if (!empty($opp['contact_id']) && !empty($opp['contact_name'])): ?>
                    <a href="/crm/contacts/details?id=<?= (int) $opp['contact_id'] ?>" class="table-link">
                        <?= htmlspecialchars(trim($opp['contact_name']), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </dd>
            </div>
        </dl>
        <?php endif; ?>
    </div>

    <!-- Financial & Forecast -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-circle-dollar-to-slot" aria-hidden="true"></i>
            Financial &amp; Forecast
        </h2>

        <?php if ($editMode): ?>
        <div class="edit-section">
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="amount">Amount (USD)</label>
                    <input id="amount" type="number" name="amount" class="input"
                           min="0" step="0.01" value="<?= $fld($opp['amount']) ?>">
                </div>
                <div class="form-group form-group--grow">
                    <label class="form-label" for="probability">Probability (%)</label>
                    <input id="probability" type="number" name="probability" class="input"
                           min="0" max="100" value="<?= $fld($opp['probability']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="forecast_category">Forecast</label>
                    <select id="forecast_category" name="forecast_category" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Omitted','Pipeline','Best Case','Commit','Closed'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('forecast_category', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="close_date">Close Date</label>
                    <input id="close_date" type="date" name="close_date" class="input"
                           value="<?= $fld($opp['close_date']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="plan_type">Plan Type</label>
                    <select id="plan_type" name="plan_type" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Basic','Professional','Enterprise','Custom'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('plan_type', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--grow">
                    <label class="form-label" for="billing_term">Billing Term</label>
                    <select id="billing_term" name="billing_term" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Monthly','Annual','Multi-Year'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('billing_term', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php else: ?>
        <dl class="field-list">
            <div class="field-list__row"><dt>Amount</dt><dd><?= $opp['amount'] !== null ? 'USD ' . number_format((float) $opp['amount'], 2) : '<span class="text-muted">—</span>' ?></dd></div>
            <div class="field-list__row"><dt>Probability</dt><dd><?= $opp['probability'] !== null ? (int) $opp['probability'] . '%' : '<span class="text-muted">—</span>' ?></dd></div>
            <div class="field-list__row"><dt>Forecast</dt><dd><?= $val($opp['forecast_category']) ?></dd></div>
            <div class="field-list__row"><dt>Close Date</dt><dd><?= $val($opp['close_date']) ?></dd></div>
            <div class="field-list__row"><dt>Plan Type</dt><dd><?= $val($opp['plan_type']) ?></dd></div>
            <div class="field-list__row"><dt>Billing Term</dt><dd><?= $val($opp['billing_term']) ?></dd></div>
        </dl>
        <?php endif; ?>
    </div>

    <!-- Sales Process -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-diagram-next" aria-hidden="true"></i>
            Sales Process
        </h2>

        <?php if ($editMode): ?>
        <div class="edit-section">
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="stage">Stage</label>
                    <select id="stage" name="stage" class="input">
                        <?php foreach (['New','Building','Review','Quote','Negotiating','Closed Won','Closed Lost'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('stage', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="loss_reason">Loss Reason</label>
                    <select id="loss_reason" name="loss_reason" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Lost to Competitor','Price','Features/Functionality','No Budget','Project Cancelled','Poor Relationship'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('loss_reason', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <?php else: ?>
        <dl class="field-list">
            <div class="field-list__row">
                <dt>Stage</dt>
                <dd><?php if (!empty($opp['stage'])): ?><span class="badge <?= $stageCls ?>"><?= $val($opp['stage']) ?></span><?php else: ?><span class="text-muted">—</span><?php endif; ?></dd>
            </div>
            <div class="field-list__row"><dt>Loss Reason</dt><dd><?= $val($opp['loss_reason']) ?></dd></div>
        </dl>
        <?php endif; ?>
    </div>

    <!-- Qualification & Intelligence -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-magnifying-glass-chart" aria-hidden="true"></i>
            Qualification
        </h2>

        <?php if ($editMode): ?>
        <div class="edit-section">
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label" for="decision_timeline">Decision Timeline</label>
                    <select id="decision_timeline" name="decision_timeline" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (['Immediately','1-3 Months','3-6 Months','6+ Months','Unknown'] as $opt): ?>
                        <option value="<?= $opt ?>"<?= $sel('decision_timeline', $opt) ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--checkbox">
                    <input id="budget_confirmed" type="checkbox" name="budget_confirmed" value="1"
                           <?= $opp['budget_confirmed'] ? 'checked' : '' ?>>
                    <label for="budget_confirmed" class="form-label form-label--inline">Budget Confirmed</label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label">Stakeholders</label>
                    <div class="checkbox-group">
                        <?php foreach (['Economic Buyer','Technical Evaluator','Executive Sponsor','Legal/Procurement','End User'] as $opt): ?>
                        <label class="checkbox-group__item">
                            <input type="checkbox" name="stakeholders_identified[]" value="<?= $opt ?>"<?= $chkArr($stakeholders, $opt) ?>>
                            <?= $opt ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group form-group--grow">
                    <label class="form-label">Competitors</label>
                    <div class="checkbox-group">
                        <?php foreach (['Competitor A','Competitor B','Competitor C','In-House Solution','Status Quo'] as $opt): ?>
                        <label class="checkbox-group__item">
                            <input type="checkbox" name="competitor[]" value="<?= $opt ?>"<?= $chkArr($competitors, $opt) ?>>
                            <?= $opt ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <dl class="field-list">
            <div class="field-list__row"><dt>Budget Confirmed</dt><dd><?= $opp['budget_confirmed'] ? 'Yes' : 'No' ?></dd></div>
            <div class="field-list__row"><dt>Decision Timeline</dt><dd><?= $val($opp['decision_timeline']) ?></dd></div>
            <div class="field-list__row">
                <dt>Stakeholders</dt>
                <dd>
                    <?php if (!empty($stakeholders)): ?>
                    <?php foreach ($stakeholders as $s): ?><span class="badge badge--neutral badge--tight"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </dd>
            </div>
            <div class="field-list__row">
                <dt>Competitors</dt>
                <dd>
                    <?php if (!empty($competitors)): ?>
                    <?php foreach ($competitors as $c): ?><span class="badge badge--neutral badge--tight"><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </dd>
            </div>
        </dl>
        <?php endif; ?>
    </div>

    <!-- Notes -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-note-sticky" aria-hidden="true"></i>
            Notes
        </h2>
        <?php if ($editMode): ?>
        <div class="edit-section">
            <div class="form-group">
                <textarea name="description" class="input" rows="4"><?= $fld($opp['description']) ?></textarea>
            </div>
        </div>
        <?php elseif (!empty($opp['description'])): ?>
        <p class="field-text"><?= nl2br($val($opp['description'])) ?></p>
        <?php else: ?>
        <p class="field-text"><span class="text-muted">—</span></p>
        <?php endif; ?>
    </div>

    <!-- Record (always read-only) -->
    <div class="card profile-card">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            Record
        </h2>
        <dl class="field-list">
            <div class="field-list__row"><dt>ID</dt><dd><?= (int) $opp['id'] ?></dd></div>
            <div class="field-list__row"><dt>Created</dt><dd><?= $val($opp['created_at']) ?></dd></div>
            <div class="field-list__row"><dt>Last Updated</dt><dd><?= $val($opp['updated_at']) ?></dd></div>
        </dl>
    </div>

    <?php if ($editMode): ?>
    <div class="profile-card__footer profile-card__footer--end">
        <a href="/crm/opportunities/details?id=<?= $id ?>" class="btn btn--ghost">Cancel</a>
        <button type="submit" class="btn btn--primary">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Changes
        </button>
    </div>
    </form>
    <?php endif; ?>

    </div><!-- /.split-layout__sidebar -->

</div><!-- /.split-layout -->


