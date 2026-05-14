<?php $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">CRM / <a href="/crm/activities/list">Activities</a></p>
        <h1 class="dash-header__title">Log Activity</h1>
    </div>
    <div class="btn-group">
        <a href="/crm/activities/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($editError): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= $e($editError) ?></div>
</div>
<?php endif; ?>

<div class="card profile-card">
    <form method="POST" action="/crm/activities/new<?= $preAccountId || $preContactId || $preOpportunityId
        ? '?' . http_build_query(array_filter(['account_id' => $preAccountId, 'contact_id' => $preContactId, 'opportunity_id' => $preOpportunityId]))
        : '' ?>" novalidate>

        <!-- Type + Date + Duration -->
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="activity_type_id">
                    Activity Type <span class="form-required">*</span>
                </label>
                <select id="activity_type_id" name="activity_type_id" class="input" required
                        data-type-costs='<?= htmlspecialchars(json_encode(array_column($activeTypes, 'average_cost', 'id')), ENT_QUOTES, 'UTF-8') ?>'>
                    <option value="">— Select type —</option>
                    <?php foreach ($activeTypes as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"
                            data-avg-cost="<?= $e($t['average_cost']) ?>"
                        <?= (int)($_POST['activity_type_id'] ?? 0) === (int)$t['id'] ? ' selected' : '' ?>>
                        <?= $e($t['name']) ?> ($<?= number_format((float)$t['average_cost'], 2) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="activity_date">
                    Date <span class="form-required">*</span>
                </label>
                <input id="activity_date" type="date" name="activity_date" class="input" required
                       value="<?= $e($_POST['activity_date'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="form-group" style="width:8rem">
                <label class="form-label" for="duration_minutes">Duration (min)</label>
                <input id="duration_minutes" type="number" name="duration_minutes" class="input"
                       min="1" placeholder="e.g. 30"
                       value="<?= $e($_POST['duration_minutes'] ?? '') ?>">
            </div>
        </div>

        <!-- Cost + Outcome -->
        <div class="form-row">
            <div class="form-group" style="width:10rem">
                <label class="form-label" for="cost">Cost ($)</label>
                <input id="cost" type="number" name="cost" class="input"
                       step="0.01" min="0" placeholder="Pre-filled from type"
                       value="<?= $e($_POST['cost'] ?? '') ?>">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="outcome">Outcome</label>
                <select id="outcome" name="outcome" class="input">
                    <option value="">— Select outcome —</option>
                    <?php foreach (Activity::OUTCOMES as $o): ?>
                    <option value="<?= $e($o) ?>"<?= ($_POST['outcome'] ?? '') === $o ? ' selected' : '' ?>>
                        <?= $e($o) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div class="form-group">
            <label class="form-label" for="notes">Notes</label>
            <textarea id="notes" name="notes" class="input" rows="3"
                      placeholder="What happened?"><?= $e($_POST['notes'] ?? '') ?></textarea>
        </div>

        <hr class="divider mb-md">
        <p class="form-hint mb-md">Link this activity to at least one of the following.</p>

        <!-- Account lookup -->
        <div class="form-group">
            <label class="form-label" for="account-lookup-input">Account</label>
            <div class="entity-lookup" data-initial-name="<?= $e($preAccountName) ?>">
                <input id="account-lookup-input" type="text" class="input entity-lookup__input"
                       placeholder="Search accounts…" autocomplete="off">
                <input type="hidden" name="account_id" class="entity-lookup__value"
                       value="<?= $e($preAccountId) ?>">
                <div class="entity-lookup__results" hidden></div>
            </div>
        </div>

        <!-- Contact lookup -->
        <div class="form-group">
            <label class="form-label" for="contact-lookup-input">Contact</label>
            <div class="entity-lookup entity-lookup--contact" data-initial-name="<?= $e($preContactName) ?>">
                <input id="contact-lookup-input" type="text" class="input entity-lookup__input"
                       placeholder="Search contacts…" autocomplete="off">
                <input type="hidden" name="contact_id" class="entity-lookup__value"
                       value="<?= $e($preContactId) ?>">
                <div class="entity-lookup__results" hidden></div>
            </div>
        </div>

        <!-- Opportunity lookup -->
        <div class="form-group">
            <label class="form-label" for="opp-lookup-input">Opportunity</label>
            <div class="entity-lookup entity-lookup--opportunity" data-initial-name="<?= $e($preOpportunityName) ?>">
                <input id="opp-lookup-input" type="text" class="input entity-lookup__input"
                       placeholder="Search opportunities…" autocomplete="off">
                <input type="hidden" name="opportunity_id" class="entity-lookup__value"
                       value="<?= $e($preOpportunityId) ?>">
                <div class="entity-lookup__results" hidden></div>
            </div>
        </div>

        <div class="form-actions mt-md">
            <a href="/crm/activities/list" class="btn btn--ghost">Cancel</a>
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save Activity
            </button>
        </div>
    </form>
</div>
