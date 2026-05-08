<?php $pageTitle = 'Admin User Guide'; ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 class="dash-header__title">Administrator Guide</h1>
        <p class="dash-header__sub">Platform administration, user management, and operational procedures</p>
    </div>
    <div>
        <a href="/admin/userlist" class="btn btn--secondary">
            <i class="fa-solid fa-users" aria-hidden="true"></i> View Users
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Contents -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-list" aria-hidden="true"></i>
        Contents
    </h2>
    <ul class="instructions__list">
        <li><a href="#overview" class="table-link">Platform Overview</a></li>
        <li><a href="#user-management" class="table-link">User Management</a></li>
        <li><a href="#module-access" class="table-link">Module Access &amp; Entitlements</a></li>
        <li><a href="#company" class="table-link">Company Management</a></li>
        <li><a href="#oauth" class="table-link">OAuth Sessions &amp; API Keys</a></li>
        <li><a href="#logs" class="table-link">System Logs</a></li>
        <li><a href="#migrations" class="table-link">Database Migrations</a></li>
        <li><a href="#playbook-onboarding" class="table-link">Playbook — Onboarding a New User</a></li>
        <li><a href="#playbook-offboarding" class="table-link">Playbook — Offboarding a User</a></li>
        <li><a href="#playbook-deploy" class="table-link">Playbook — Deploying an Update</a></li>
    </ul>
</div>

<!-- Platform Overview -->
<div class="card profile-card mb-lg" id="overview">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-server" aria-hidden="true"></i>
        Platform Overview
    </h2>
    <p class="instructions__p">The Admin module gives platform administrators control over users, access, API credentials, system health, and database schema. Only users with <strong>user_type = admin</strong> can access this module.</p>

    <p class="section-label">Admin sections</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Users</dt><dd>Create, edit, and deactivate user accounts. Set user types and module access tiers.</dd></div>
        <div class="field-list__row"><dt>Companies</dt><dd>Manage the company records that users can belong to.</dd></div>
        <div class="field-list__row"><dt>OAuth Sessions</dt><dd>View and manage API client registrations and active bearer tokens.</dd></div>
        <div class="field-list__row"><dt>System Logs</dt><dd>Inspect application logs — errors, warnings, authentication events, and security probes.</dd></div>
        <div class="field-list__row"><dt>Migrations</dt><dd>Run database schema migrations for all installed modules.</dd></div>
        <div class="field-list__row"><dt>Validator Tests</dt><dd>Run built-in validation checks to verify the platform is configured correctly.</dd></div>
        <div class="field-list__row"><dt>Debug: Current User</dt><dd>Inspect the current session and database record for the logged-in user. Development tool only.</dd></div>
    </dl>
</div>

<!-- User Management -->
<div class="card profile-card mb-lg" id="user-management">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-users" aria-hidden="true"></i>
        User Management
    </h2>
    <p class="instructions__p">Users are managed from <strong>Admin → Users</strong>. Click a user's name to open their detail page for full profile and access management. Use the inline edit (double-click a row) for quick field updates.</p>

    <p class="section-label">User types</p>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Type</th><th>Description</th><th>Module access</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge--warning">admin</span></td>
                    <td>Full platform access including the Admin module</td>
                    <td>All modules + Admin</td>
                </tr>
                <tr>
                    <td><span class="badge badge--info">manager</span></td>
                    <td>Team or account management capabilities</td>
                    <td>Determined by module access tiers</td>
                </tr>
                <tr>
                    <td><span class="badge badge--neutral">user</span></td>
                    <td>Standard user — most users will have this type</td>
                    <td>Determined by module access tiers</td>
                </tr>
                <tr>
                    <td><span class="badge badge--neutral">free</span></td>
                    <td>Self-registered account with minimal access</td>
                    <td>Free tier only until upgraded</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="section-label section-label--mt">User status</p>
    <dl class="field-list">
        <div class="field-list__row"><dt><span class="badge badge--success">Active</span></dt><dd>User can log in and access the platform. Email must be verified.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--neutral">Inactive</span></dt><dd>User account is disabled. The user cannot log in. Use this instead of deleting — the historical data is preserved.</dd></div>
    </dl>

    <p class="section-label section-label--mt">Important rules</p>
    <ul class="instructions__list">
        <li>Never delete a user account — set it to <strong>Inactive</strong> instead. Deletion removes historical records (opportunity ownership, ticket assignments, etc.).</li>
        <li>Email addresses must be unique across all users.</li>
        <li>Users must verify their email before they can log in.</li>
        <li>You cannot change your own user_type or deactivate yourself.</li>
    </ul>
</div>

<!-- Module Access -->
<div class="card profile-card mb-lg" id="module-access">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-puzzle-piece" aria-hidden="true"></i>
        Module Access &amp; Entitlements
    </h2>
    <p class="instructions__p">Module access is managed separately from user type. A user must have a non-empty access tier for a module to see it in their sidebar and use its API/MCP tools.</p>

    <p class="section-label">Access tiers</p>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Tier</th><th>Typical use</th></tr>
            </thead>
            <tbody>
                <tr><td><span class="badge badge--neutral">Free</span></td><td>Read-only or basic access. The module is visible but features may be restricted by the module's own logic.</td></tr>
                <tr><td><span class="badge badge--info">User</span></td><td>Standard usage — can create, view, and edit records owned by or assigned to them.</td></tr>
                <tr><td><span class="badge badge--warning">Manager</span></td><td>Full access including all records, reporting, and configuration within the module.</td></tr>
            </tbody>
        </table>
    </div>

    <p class="section-label section-label--mt">Granting module access</p>
    <ol class="instructions__list">
        <li>Go to <strong>Admin → Users</strong> and click the user's name.</li>
        <li>On the user detail page, scroll to the <strong>Module Access</strong> card.</li>
        <li>For each module, select the appropriate tier from the dropdown.</li>
        <li>Click <strong>Save Module Access</strong>.</li>
        <li>The user must log out and back in for the new access to take effect.</li>
    </ol>

    <p class="section-label section-label--mt">Revoking module access</p>
    <ol class="instructions__list">
        <li>Open the user's detail page.</li>
        <li>Set the module tier to <strong>— No Access —</strong>.</li>
        <li>Save. The module will no longer appear in the user's sidebar on their next login.</li>
    </ol>

    <p class="section-label section-label--mt">New modules</p>
    <p class="instructions__p">When a new module is installed, it automatically appears in every user's Module Access card on this page. No code changes are required. Simply grant access to the appropriate users.</p>
</div>

<!-- Company Management -->
<div class="card profile-card mb-lg" id="company">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-building" aria-hidden="true"></i>
        Company Management
    </h2>
    <p class="instructions__p">Company records represent the organisations that your users belong to. A user can be associated with one company via their profile.</p>

    <dl class="field-list">
        <div class="field-list__row"><dt>Company Name</dt><dd>Required. The legal or trading name of the organisation.</dd></div>
        <div class="field-list__row"><dt>Contact details</dt><dd>Phone, email, address, and website. Used for display and communication.</dd></div>
    </dl>

    <p class="section-label section-label--mt">Invitations</p>
    <p class="instructions__p">Users can be invited to join a company. Invitations are sent by email and expire after a set period. Accepted invitations link the user's account to the company record.</p>
</div>

<!-- OAuth -->
<div class="card profile-card mb-lg" id="oauth">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-key" aria-hidden="true"></i>
        OAuth Sessions &amp; API Keys
    </h2>
    <p class="instructions__p">The OAuth Sessions page manages API client registrations and the bearer tokens that clients use to access the API and MCP tools.</p>

    <p class="section-label">Key concepts</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>OAuth Client</dt><dd>A registered application that can request access tokens. Each client has a unique Client ID and a list of allowed redirect URIs.</dd></div>
        <div class="field-list__row"><dt>Bearer Token</dt><dd>A time-limited access token issued to a client after a user authorises it. Tokens expire automatically after 180 days.</dd></div>
        <div class="field-list__row"><dt>Auth Code</dt><dd>A short-lived (10 minute) code exchanged for a bearer token as part of the PKCE flow. Used once and then discarded.</dd></div>
    </dl>

    <p class="section-label section-label--mt">Managing clients</p>
    <ul class="instructions__list">
        <li><strong>Register a new client</strong> — provide a Client ID, name, and redirect URI. The Client ID is permanent and immutable.</li>
        <li><strong>Disable a client</strong> — prevents new tokens from being issued. Existing tokens remain valid until expiry or manual revocation.</li>
        <li><strong>Delete a client</strong> — only possible when the client is disabled. Removes all associated tokens and auth codes.</li>
    </ul>

    <p class="section-label section-label--mt">Managing tokens</p>
    <ul class="instructions__list">
        <li><strong>Revoke a token</strong> — immediately invalidates the token. The user will need to re-authorise to get a new one.</li>
        <li><strong>Extend expiry</strong> — move the expiry date forward for a specific token.</li>
        <li><strong>Purge expired</strong> — removes all expired tokens and used auth codes from the database to keep the table clean.</li>
    </ul>

    <p class="section-label section-label--mt">MCP (Model Context Protocol)</p>
    <p class="instructions__p">The MCP endpoint at <code>/api/mcp_v2</code> uses the same OAuth bearer tokens. To connect Claude or another AI assistant, the user authorises via the standard OAuth flow. The MCP tools available to each user are dynamically scoped to the modules they have access to.</p>
</div>

<!-- System Logs -->
<div class="card profile-card mb-lg" id="logs">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
        System Logs
    </h2>
    <p class="instructions__p">The Log Viewer displays application events in reverse-chronological order. Each entry is a structured JSON record with a timestamp, severity level, message, and contextual data.</p>

    <p class="section-label">Severity levels</p>
    <dl class="field-list">
        <div class="field-list__row"><dt><span class="badge badge--warning">ERROR</span></dt><dd>Something failed — a database error, an uncaught exception, a migration failure. These require immediate attention.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--info">WARNING</span></dt><dd>Something unexpected happened but the system continued — a 404, a failed auth attempt, a missing file. Monitor for patterns.</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--neutral">INFO</span></dt><dd>Normal operational events — emails sent, migrations applied, logins. Informational only.</dd></div>
    </dl>

    <p class="section-label section-label--mt">What to look for</p>
    <ul class="instructions__list">
        <li><strong>Repeated 404s for sensitive paths</strong> (e.g. <code>/.env</code>, <code>/.git/config</code>, <code>/phpinfo.php</code>) — automated security scanners. Normal background noise; no action needed unless frequency spikes.</li>
        <li><strong>Bootstrap failed</strong> — a PHP file was missing or unreadable. Check the file path in the context and re-deploy.</li>
        <li><strong>Authentication failed</strong> repeated from the same IP — potential brute-force attack. Consider blocking the IP at the server level.</li>
        <li><strong>Migration failed</strong> — a database migration threw an error. The SQL is shown in context — fix it and re-run.</li>
    </ul>

    <p class="section-label section-label--mt">Log management</p>
    <p class="instructions__p">Logs are stored in <code>storage/logs/app.log</code>. The log viewer lets you select individual entries and delete them. Use <strong>Delete Selected</strong> to remove resolved noise, or <strong>Archive</strong> to preserve a snapshot. Keep logs manageable — a very large log file can slow the viewer.</p>
</div>

<!-- Migrations -->
<div class="card profile-card mb-lg" id="migrations">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-database" aria-hidden="true"></i>
        Database Migrations
    </h2>
    <p class="instructions__p">The Migrations page tracks database schema changes. Each installed module has its own migration files. The page groups pending migrations by module.</p>

    <p class="section-label">The cardinal rule</p>
    <p class="instructions__p"><strong>Always deploy PHP code before running migrations.</strong> Code must match the schema. Deploying in the wrong order causes errors — new PHP expecting new columns that do not exist yet, or old PHP breaking on new schema.</p>

    <p class="section-label section-label--mt">Running migrations</p>
    <ol class="instructions__list">
        <li>Deploy all PHP and SQL files to the server first.</li>
        <li>Open <strong>Admin → Migrations</strong>.</li>
        <li>Review the pending migrations by module group.</li>
        <li>Click <strong>Run</strong> on individual migrations, or <strong>Run [Module]</strong> to run all pending for one module, or <strong>Run All Pending</strong> to run everything.</li>
        <li>Verify each migration succeeded (it moves to the Applied table).</li>
    </ol>

    <p class="section-label section-label--mt">Run vs Mark Applied</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Run</dt><dd>Executes the SQL and records it. Use this for normal migrations.</dd></div>
        <div class="field-list__row"><dt>Mark Applied</dt><dd>Records the migration without executing its SQL. Use when the change was applied manually (e.g. directly in phpMyAdmin) and you just need to record it.</dd></div>
    </dl>

    <p class="section-label section-label--mt">What happens on failure</p>
    <p class="instructions__p">If a migration fails, it stops immediately. Already-applied migrations in that run are not rolled back. The failed file is not recorded — fix the SQL and run it again. The error is shown in the flash message and logged to the system log.</p>

    <p class="section-label section-label--mt">Module column</p>
    <p class="instructions__p">The Applied table shows which module each migration belongs to and the platform version it was applied under. This makes it easy to correlate a schema change with a specific release.</p>
</div>

<!-- Playbook: Onboarding -->
<h2 class="migration-heading mb-lg">
    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
    Playbooks
</h2>

<div class="card profile-card mb-lg" id="playbook-onboarding">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
        Playbook — Onboarding a New User
    </h2>

    <ol class="instructions__list">
        <li>Go to <strong>Admin → Users</strong> and click <strong>New User</strong> (or have the user self-register at <code>/register</code>).</li>
        <li>Set <strong>name</strong>, <strong>email</strong>, and initial <strong>password</strong>.</li>
        <li>Set <strong>User Type</strong>:
            <ul style="margin-top:0.35rem; padding-left:1.25rem;">
                <li>Most users → <code>user</code></li>
                <li>Team leads or account managers → <code>manager</code></li>
                <li>Platform administrators → <code>admin</code> (grant sparingly)</li>
            </ul>
        </li>
        <li>Set status to <strong>Active</strong>.</li>
        <li>Save the user record.</li>
        <li>Open the user's detail page and navigate to the <strong>Module Access</strong> card.</li>
        <li>Grant access to the required modules (e.g. CRM → User, ITSM → User).</li>
        <li>Save Module Access.</li>
        <li>The user receives an email verification link. They must verify their email before logging in.</li>
        <li>Confirm the user can log in and see the expected modules in their sidebar.</li>
    </ol>

    <p class="section-label section-label--mt">Self-registered users</p>
    <p class="instructions__p">Users who self-register via <code>/register</code> receive <code>user_type = free</code> and no module access by default. An admin must review and upgrade their access after registration.</p>
</div>

<!-- Playbook: Offboarding -->
<div class="card profile-card mb-lg" id="playbook-offboarding">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-user-minus" aria-hidden="true"></i>
        Playbook — Offboarding a User
    </h2>

    <ol class="instructions__list">
        <li>Go to <strong>Admin → Users</strong> and open the user's detail page.</li>
        <li>Click <strong>Edit</strong>.</li>
        <li>Set status to <strong>Inactive</strong>.</li>
        <li>Save — the user can no longer log in immediately.</li>
        <li>Go to <strong>Admin → OAuth Sessions → Tokens</strong>.</li>
        <li>Revoke any active bearer tokens for this user. This invalidates any active API/MCP connections.</li>
        <li>Reassign any open opportunities, tickets, or accounts owned by the user to an active team member.</li>
        <li>Do <em>not</em> delete the user account — historical records depend on the user ID.</li>
    </ol>

    <p class="section-label section-label--mt">Important</p>
    <p class="instructions__p">Setting a user to Inactive prevents login but does not revoke active API tokens. Always revoke tokens as part of offboarding to ensure no continued API access.</p>
</div>

<!-- Playbook: Deploying an Update -->
<div class="card profile-card mb-lg" id="playbook-deploy">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
        Playbook — Deploying a Platform Update
    </h2>
    <p class="instructions__p">Follow this order every time code and schema changes are deployed together.</p>

    <ol class="instructions__list">
        <li><strong>Review the changes</strong> — identify which files changed and whether any include new migration files in <code>Module/*/SQL/InterimUpdates/</code>.</li>
        <li><strong>Bump <code>PLATFORM_VERSION</code></strong> in <code>configuration/.env</code> if this is a named release.</li>
        <li><strong>Deploy PHP files first</strong> — upload all modified PHP, JS, and CSS files to the server.</li>
        <li><strong>Deploy SQL migration files</strong> — upload the new <code>.sql</code> files to the appropriate <code>SQL/InterimUpdates/</code> folders.</li>
        <li><strong>Run migrations</strong> — go to <strong>Admin → Migrations</strong> and run all pending migrations.</li>
        <li><strong>Verify</strong> — check the System Log for any errors. Test the changed features.</li>
        <li><strong>Monitor</strong> — review the System Log 30 minutes after deployment for unexpected errors.</li>
    </ol>

    <p class="section-label section-label--mt">Rollback</p>
    <p class="instructions__p">If a deployment causes critical errors: revert PHP files to the previous version immediately. Do not reverse a migration unless you have prepared a corresponding rollback SQL — schema rollbacks are risky. Instead, fix the issue forward with a new migration file.</p>
</div>
