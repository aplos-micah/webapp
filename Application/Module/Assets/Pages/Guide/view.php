<?php
$pageTitle = 'Assets User Guide';
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Assets</p>
        <h1 class="dash-header__title">User Guide</h1>
        <p class="dash-header__sub">Asset management, lifecycle, and best practices</p>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Overview                                                               -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Overview
    </h2>
    <div class="guide-body">
        <p>The Assets module is a Configuration Management Database (CMDB) for tracking IT and business assets across their full lifecycle — from procurement through retirement. Each asset record stores identification details, assignment, location, and warranty information.</p>
        <p>Assets integrate with ITSM: when a ticket relates to a specific piece of equipment, it can be linked to the relevant asset record for full traceability.</p>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Asset Types & Categories                                               -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-shapes" aria-hidden="true"></i>
        Asset Types &amp; Categories
    </h2>
    <div class="guide-body">
        <p><strong>Type</strong> is the primary classification. Use the most specific type that fits.</p>
        <table class="data-table mb-md">
            <thead>
                <tr><th>Type</th><th>When to use</th></tr>
            </thead>
            <tbody>
                <tr><td><strong>Hardware</strong></td><td>Physical computing equipment — servers, desktops, laptops, peripherals</td></tr>
                <tr><td><strong>Software</strong></td><td>Installed applications or platforms tracked individually (e.g. a specific server OS)</td></tr>
                <tr><td><strong>Network</strong></td><td>Switches, routers, firewalls, access points, cabling infrastructure</td></tr>
                <tr><td><strong>Mobile</strong></td><td>Phones, tablets, and handheld devices</td></tr>
                <tr><td><strong>License</strong></td><td>Software licenses, subscriptions, and entitlements not tied to a single device</td></tr>
                <tr><td><strong>Other</strong></td><td>Assets that do not fit another category — AV equipment, office tech, etc.</td></tr>
            </tbody>
        </table>
        <p><strong>Category</strong> provides a secondary level of detail within a type (e.g. Type = Hardware, Category = Laptop). Use the closest match; use "Other" if nothing fits.</p>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Asset Statuses                                                         -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
        Asset Statuses
    </h2>
    <div class="guide-body">
        <table class="data-table">
            <thead>
                <tr><th>Status</th><th>Meaning</th></tr>
            </thead>
            <tbody>
                <tr><td><span class="badge badge--success">Active</span></td><td>In use and assigned to a user or location</td></tr>
                <tr><td><span class="badge badge--info">In Stock</span></td><td>Available for deployment — received but not yet assigned</td></tr>
                <tr><td><span class="badge badge--warning">In Repair</span></td><td>Temporarily out of service — being repaired or serviced</td></tr>
                <tr><td><span class="badge badge--neutral">Retired</span></td><td>No longer in use — decommissioned but retained in records</td></tr>
                <tr><td><span class="badge badge--neutral">Lost/Stolen</span></td><td>Asset is missing or reported stolen — retain for audit purposes</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Asset Lifecycle                                                        -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-arrows-spin" aria-hidden="true"></i>
        Asset Lifecycle
    </h2>
    <div class="guide-body">
        <ol class="guide-list">
            <li>
                <strong>Procurement</strong> — Create the asset record on arrival. Set status to <em>In Stock</em>. Record purchase date, cost, and warranty expiry immediately while the paperwork is at hand.
            </li>
            <li>
                <strong>Deployment</strong> — Assign the asset to a user and location. Change status to <em>Active</em>. Update the serial number and model fields if not already entered.
            </li>
            <li>
                <strong>In-Service</strong> — Keep the assigned_to field current whenever the device changes hands. Open an ITSM ticket for faults; link the ticket to the asset.
            </li>
            <li>
                <strong>Repair</strong> — Change status to <em>In Repair</em> when the device goes for service. Leave assigned_to set so you know whose device it is. Update back to <em>Active</em> when returned.
            </li>
            <li>
                <strong>Retirement</strong> — Change status to <em>Retired</em>. Clear the assigned_to field. Do not delete the record — it is needed for audit and warranty history.
            </li>
            <li>
                <strong>Loss or Theft</strong> — Change status to <em>Lost/Stolen</em> immediately. Open an ITSM security incident if required. Do not delete the record.
            </li>
        </ol>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Warranty Tracking                                                      -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        Warranty Tracking
    </h2>
    <div class="guide-body">
        <p>The Assets Dashboard highlights warranties expiring within the next 30 days. Best practices:</p>
        <ul class="guide-list">
            <li>Always enter <strong>Warranty Expires</strong> at procurement — it is far easier to find on the invoice than to recover later.</li>
            <li>Review the expiring warranties panel weekly and initiate renewal or replacement before expiry.</li>
            <li>For software licenses, use the License type and enter the subscription end date as the warranty expiry date.</li>
            <li>Assets with status <em>Retired</em> or <em>Lost/Stolen</em> are excluded from the expiry panel.</li>
        </ul>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Onboarding Playbook                                                    -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
        Playbook: New Employee Onboarding
    </h2>
    <div class="guide-body">
        <p>When equipping a new team member:</p>
        <ol class="guide-list">
            <li>Search Assets for <em>In Stock</em> hardware matching the required spec.</li>
            <li>Open the asset record and update <strong>Assigned To</strong> to the new employee's name.</li>
            <li>Set status to <strong>Active</strong> and update <strong>Location</strong> to their desk or office.</li>
            <li>Repeat for any additional devices (monitor, phone, etc.).</li>
            <li>Record the handover date in an ITSM Service Request if your process requires a paper trail.</li>
        </ol>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- Offboarding Playbook                                                   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-user-minus" aria-hidden="true"></i>
        Playbook: Employee Offboarding
    </h2>
    <div class="guide-body">
        <p>When a team member leaves:</p>
        <ol class="guide-list">
            <li>Search Assets and filter by <strong>Assigned To</strong> for the departing employee.</li>
            <li>For each asset, open the record and clear <strong>Assigned To</strong>.</li>
            <li>If the device is being re-deployed, set status to <strong>In Stock</strong> and clear <strong>Location</strong>.</li>
            <li>If the device needs to be wiped or inspected first, set status to <strong>In Repair</strong> until ready.</li>
            <li>If the device is being retired, set status to <strong>Retired</strong>.</li>
            <li>Log the equipment return in an ITSM Service Request if required by policy.</li>
        </ol>
    </div>
</div>
