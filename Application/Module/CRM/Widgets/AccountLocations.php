<?php

/**
 * AccountLocations Widget
 *
 * Renders the Locations related tile for an Account detail page.
 * Supports inline add (toggle button) and dblclick-to-edit per location.
 * Forms POST back to $baseUrl with _action = add_location | update_location | remove_location.
 */
class AccountLocations
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function getLocations(int $accountId): array
    {
        return $this->db->query(
            'SELECT * FROM locations
              WHERE account_id = ?
              ORDER BY is_primary DESC, location_name ASC, id ASC',
            [$accountId]
        );
    }

    // =========================================================================
    // Render
    // =========================================================================

    public function render(int $accountId, string $baseUrl): string
    {
        $locations = $this->getLocations($accountId);
        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        ob_start();
        ?>

        <?php if (empty($locations)): ?>
        <div class="related-card__empty" id="loc-empty">
            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
            <p>No locations yet.</p>
        </div>
        <?php else: ?>
        <ul class="loc-list" id="loc-list">
            <?php foreach ($locations as $loc): ?>
            <?php $locId = (int) $loc['id']; ?>
            <li class="loc-list__item" data-loc-id="<?= $locId ?>">

                <!-- ── Display row ── -->
                <div class="loc-list__display" title="Double-click to edit">
                    <div class="loc-list__main">
                        <span class="loc-list__name">
                            <?= $loc['location_name'] ? $e($loc['location_name']) : '<em class="text-muted">Unnamed</em>' ?>
                        </span>
                        <div class="loc-list__badges">
                            <?php if ($loc['location_type']): ?>
                            <span class="badge badge--info"><?= $e($loc['location_type']) ?></span>
                            <?php endif; ?>
                            <?php if ($loc['is_primary']): ?>
                            <span class="badge badge--success">Primary</span>
                            <?php endif; ?>
                            <?php
                            $statusCls = match($loc['location_status']) {
                                'Active'    => 'badge--success',
                                'Inactive'  => 'badge--neutral',
                                'Closed'    => 'badge--neutral',
                                'Temporary' => 'badge--warning',
                                default     => 'badge--neutral',
                            };
                            ?>
                            <span class="badge <?= $statusCls ?>"><?= $e($loc['location_status']) ?></span>
                            <?php if ($loc['validation_status'] === 'Verified'): ?>
                            <span class="badge badge--success" title="Address verified">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            </span>
                            <?php elseif ($loc['validation_status'] === 'Invalid'): ?>
                            <span class="badge badge--error" title="Address invalid">
                                <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php
                        $addrParts = array_filter([
                            $loc['street_address_1'],
                            $loc['city'],
                            implode(' ', array_filter([$loc['state_province'], $loc['zip_postal_code']])),
                        ]);
                        if ($addrParts): ?>
                        <p class="loc-list__addr"><?= $e(implode(', ', $addrParts)) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="loc-list__actions">
                        <button type="button" class="btn btn--ghost btn--sm loc-edit-btn"
                                data-loc="<?= $locId ?>" title="Edit location">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </button>
                        <form method="POST" action="<?= $e($baseUrl) ?>"
                              onsubmit="return confirm('Remove this location?')"
                              class="d-inline">
                            <input type="hidden" name="_action"     value="remove_location">
                            <input type="hidden" name="location_id" value="<?= $locId ?>">
                            <button type="submit" class="btn btn--ghost btn--sm" title="Remove location">
                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ── Inline edit panel ── -->
                <div class="loc-list__edit-panel" id="loc-edit-<?= $locId ?>" hidden>
                    <?= $this->renderForm('edit_' . $locId, $loc, 'update_location', $baseUrl, $locId) ?>
                </div>

            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <!-- ── Inline add panel (hidden, toggled by header button) ── -->
        <div id="loc-add-wrap" hidden>
            <hr class="divider--green divider--top-sm">
            <?= $this->renderForm('add', [], 'add_location', $baseUrl, 0) ?>
        </div>

        <script>
        (function () {
            const baseUrl  = <?= json_encode($baseUrl) ?>;

            // ── Toggle add form ───────────────────────────────────────────────
            const addBtn  = document.getElementById('loc-add-btn');
            const addWrap = document.getElementById('loc-add-wrap');
            if (addBtn && addWrap) {
                addBtn.addEventListener('click', () => {
                    const open = !addWrap.hidden;
                    addWrap.hidden = open;
                    addBtn.innerHTML = open
                        ? '<i class="fa-solid fa-plus" aria-hidden="true"></i> Add Location'
                        : '<i class="fa-solid fa-xmark" aria-hidden="true"></i> Cancel';
                });
            }

            // ── Open/close edit panels ────────────────────────────────────────
            function openLocEdit(locId) {
                // Close all others first
                document.querySelectorAll('.loc-list__edit-panel').forEach(p => {
                    p.hidden = true;
                });
                const panel = document.getElementById('loc-edit-' + locId);
                if (panel) {
                    panel.hidden = false;
                    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }

            function closeLocEdit(locId) {
                const panel = document.getElementById('loc-edit-' + locId);
                if (panel) panel.hidden = true;
            }

            // Edit pen buttons
            document.querySelectorAll('.loc-edit-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.stopPropagation();
                    openLocEdit(btn.dataset.loc);
                });
            });

            // Double-click on display row
            document.querySelectorAll('.loc-list__display').forEach(row => {
                row.addEventListener('dblclick', () => {
                    const item = row.closest('[data-loc-id]');
                    if (item) openLocEdit(item.dataset.locId);
                });
                row.style.cursor = 'pointer';
            });

            // Cancel buttons inside edit panels
            document.querySelectorAll('.loc-edit-cancel').forEach(btn => {
                btn.addEventListener('click', () => {
                    const panel = btn.closest('.loc-list__edit-panel');
                    if (panel) panel.hidden = true;
                });
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    // =========================================================================
    // Form builder
    // =========================================================================

    /**
     * Renders an add or edit form.
     *
     * @param string $prefix    Unique prefix for input IDs (prevents duplicate IDs on page)
     * @param array  $row       Existing data to pre-fill (empty array for add form)
     * @param string $action    Value for hidden _action field
     * @param string $baseUrl   POST target URL
     * @param int    $locId     Location ID for update forms (0 for add)
     */
    private function renderForm(string $prefix, array $row, string $action, string $baseUrl, int $locId): string
    {
        $e   = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
        $sel = fn(string $opt, string $field) => ($row[$field] ?? '') === $opt ? ' selected' : '';
        $chk = fn(string $field) => !empty($row[$field]) ? ' checked' : '';
        $v   = fn(string $field) => $e($row[$field] ?? '');

        $types      = \Location::LOCATION_TYPES;
        $statuses   = \Location::STATUSES;
        $validations = \Location::VALIDATION_STATUSES;

        ob_start();
        ?>
        <form method="POST" action="<?= $e($baseUrl) ?>" class="loc-form" novalidate>
            <input type="hidden" name="_action"     value="<?= $e($action) ?>">
            <?php if ($locId > 0): ?>
            <input type="hidden" name="location_id" value="<?= $locId ?>">
            <?php endif; ?>

            <div class="loc-form__body">

                <!-- ── Section: Identity ── -->
                <div class="loc-form__section-head">Identity</div>
                <div class="loc-form__grid">
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_name">Location Name</label>
                        <input id="<?= $e($prefix) ?>_name" type="text" name="location_name"
                               class="input" value="<?= $v('location_name') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_type">Type</label>
                        <select id="<?= $e($prefix) ?>_type" name="location_type" class="input">
                            <option value="">— Select —</option>
                            <?php foreach ($types as $t): ?>
                            <option value="<?= $e($t) ?>"<?= $sel($t, 'location_type') ?>><?= $e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_status">Status</label>
                        <select id="<?= $e($prefix) ?>_status" name="location_status" class="input">
                            <?php foreach ($statuses as $s): ?>
                            <option value="<?= $e($s) ?>"<?= $sel($s, 'location_status') ?>><?= $e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_validation">Validation</label>
                        <select id="<?= $e($prefix) ?>_validation" name="validation_status" class="input">
                            <?php foreach ($validations as $vs): ?>
                            <option value="<?= $e($vs) ?>"<?= $sel($vs, 'validation_status') ?>><?= $e($vs) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-group--align-end">
                        <label class="form-check">
                            <input type="checkbox" name="is_primary" value="1"<?= $chk('is_primary') ?>>
                            <span>Primary location</span>
                        </label>
                    </div>
                </div>

                <!-- ── Section: Address ── -->
                <div class="loc-form__section-head">Address</div>
                <div class="loc-form__grid">
                    <div class="form-group loc-form__span2">
                        <label class="form-label" for="<?= $e($prefix) ?>_addr1">Street Address</label>
                        <input id="<?= $e($prefix) ?>_addr1" type="text" name="street_address_1"
                               class="input" value="<?= $v('street_address_1') ?>" placeholder="123 Main St">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_addr2">Address Line 2</label>
                        <input id="<?= $e($prefix) ?>_addr2" type="text" name="street_address_2"
                               class="input" value="<?= $v('street_address_2') ?>" placeholder="Suite, Floor, etc.">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_addr3">Address Line 3</label>
                        <input id="<?= $e($prefix) ?>_addr3" type="text" name="street_address_3"
                               class="input" value="<?= $v('street_address_3') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_city">City</label>
                        <input id="<?= $e($prefix) ?>_city" type="text" name="city"
                               class="input" value="<?= $v('city') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_state">State / Province</label>
                        <input id="<?= $e($prefix) ?>_state" type="text" name="state_province"
                               class="input" value="<?= $v('state_province') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_zip">ZIP / Postal Code</label>
                        <input id="<?= $e($prefix) ?>_zip" type="text" name="zip_postal_code"
                               class="input" value="<?= $v('zip_postal_code') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="<?= $e($prefix) ?>_country">Country / Region</label>
                        <input id="<?= $e($prefix) ?>_country" type="text" name="country_region"
                               class="input" value="<?= $v('country_region') ?>">
                    </div>
                </div>

                <!-- ── Section: Extended Address (collapsible) ── -->
                <details class="loc-form__details">
                    <summary class="loc-form__section-head loc-form__section-head--toggle">
                        Extended Address
                        <i class="fa-solid fa-chevron-down loc-form__chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="loc-form__grid">
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_county">County</label>
                            <input id="<?= $e($prefix) ?>_county" type="text" name="county"
                                   class="input" value="<?= $v('county') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_district">District / Neighborhood</label>
                            <input id="<?= $e($prefix) ?>_district" type="text" name="district_neighborhood"
                                   class="input" value="<?= $v('district_neighborhood') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_bldg">Building Name / Number</label>
                            <input id="<?= $e($prefix) ?>_bldg" type="text" name="building_name_number"
                                   class="input" value="<?= $v('building_name_number') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_floor">Floor / Suite / Apt</label>
                            <input id="<?= $e($prefix) ?>_floor" type="text" name="floor_suite_apartment"
                                   class="input" value="<?= $v('floor_suite_apartment') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_xstreet">Intersection / Cross Street</label>
                            <input id="<?= $e($prefix) ?>_xstreet" type="text" name="intersection_cross_street"
                                   class="input" value="<?= $v('intersection_cross_street') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_pobox">PO Box</label>
                            <input id="<?= $e($prefix) ?>_pobox" type="text" name="po_box"
                                   class="input" value="<?= $v('po_box') ?>">
                        </div>
                    </div>
                </details>

                <!-- ── Section: Geospatial ── -->
                <details class="loc-form__details">
                    <summary class="loc-form__section-head loc-form__section-head--toggle">
                        Geospatial
                        <i class="fa-solid fa-chevron-down loc-form__chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="loc-form__grid">
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_lat">Latitude</label>
                            <input id="<?= $e($prefix) ?>_lat" type="number" name="latitude" step="any"
                                   class="input" value="<?= $v('latitude') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_lng">Longitude</label>
                            <input id="<?= $e($prefix) ?>_lng" type="number" name="longitude" step="any"
                                   class="input" value="<?= $v('longitude') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_tz">Timezone (UTC offset)</label>
                            <input id="<?= $e($prefix) ?>_tz" type="text" name="timezone_utc_offset"
                                   class="input" value="<?= $v('timezone_utc_offset') ?>" placeholder="e.g. UTC−06:00">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_geo">Geofence Radius (m)</label>
                            <input id="<?= $e($prefix) ?>_geo" type="number" name="geofence_radius" min="0" step="1"
                                   class="input" value="<?= $v('geofence_radius') ?>">
                        </div>
                    </div>
                </details>

                <!-- ── Section: Logistics ── -->
                <details class="loc-form__details">
                    <summary class="loc-form__section-head loc-form__section-head--toggle">
                        Logistics &amp; Access
                        <i class="fa-solid fa-chevron-down loc-form__chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="loc-form__grid">
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_hours">Receiving Hours</label>
                            <input id="<?= $e($prefix) ?>_hours" type="text" name="receiving_hours"
                                   class="input" value="<?= $v('receiving_hours') ?>" placeholder="e.g. Mon–Fri 7am–4pm">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_carrier">Preferred Carrier</label>
                            <input id="<?= $e($prefix) ?>_carrier" type="text" name="preferred_carrier"
                                   class="input" value="<?= $v('preferred_carrier') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_clearance">Vehicle Clearance</label>
                            <input id="<?= $e($prefix) ?>_clearance" type="text" name="vehicle_clearance"
                                   class="input" value="<?= $v('vehicle_clearance') ?>" placeholder="e.g. 13'6&quot; max height">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="<?= $e($prefix) ?>_gate">Gate / Entry Code</label>
                            <input id="<?= $e($prefix) ?>_gate" type="text" name="gate_entry_code"
                                   class="input" value="<?= $v('gate_entry_code') ?>">
                        </div>
                        <div class="form-group form-group--align-center">
                            <label class="form-check">
                                <input type="checkbox" name="liftgate_required" value="1"<?= $chk('liftgate_required') ?>>
                                <span>Liftgate required</span>
                            </label>
                        </div>
                        <div class="form-group form-group--align-center">
                            <label class="form-check">
                                <input type="checkbox" name="forklift_available" value="1"<?= $chk('forklift_available') ?>>
                                <span>Forklift on-site</span>
                            </label>
                        </div>
                        <div class="form-group loc-form__span2">
                            <label class="form-label" for="<?= $e($prefix) ?>_dock">Dock Instructions</label>
                            <textarea id="<?= $e($prefix) ?>_dock" name="dock_instructions"
                                      class="input" rows="3"><?= $e($row['dock_instructions'] ?? '') ?></textarea>
                        </div>
                    </div>
                </details>

            </div><!-- /.loc-form__body -->

            <!-- ── Form actions ── -->
            <div class="loc-form__footer">
                <?php if ($action === 'update_location'): ?>
                <button type="button" class="btn btn--ghost btn--sm loc-edit-cancel">Cancel</button>
                <button type="submit" class="btn btn--primary btn--sm">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save
                </button>
                <?php else: ?>
                <button type="reset" class="btn btn--ghost btn--sm">Clear</button>
                <button type="submit" class="btn btn--primary btn--sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Add Location
                </button>
                <?php endif; ?>
            </div>

        </form>
        <?php
        return ob_get_clean();
    }
}
