<?php $pageTitle = 'ITSM User Guide'; ?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">ITSM</p>
        <h1 class="dash-header__title">User Guide</h1>
        <p class="dash-header__sub">How to use the ITSM ticket system and response playbooks</p>
    </div>
    <div>
        <a href="/itsm/tickets/list" class="btn btn--secondary">
            <i class="fa-solid fa-ticket" aria-hidden="true"></i> View Tickets
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Quick-nav anchors -->
<div class="card profile-card mb-lg">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-list" aria-hidden="true"></i>
        Contents
    </h2>
    <ul class="instructions__list">
        <li><a href="#getting-started" class="table-link">Getting Started</a></li>
        <li><a href="#ticket-types" class="table-link">Ticket Types</a></li>
        <li><a href="#priority-matrix" class="table-link">Priority Matrix</a></li>
        <li><a href="#ticket-lifecycle" class="table-link">Ticket Lifecycle</a></li>
        <li><a href="#creating-tickets" class="table-link">Creating &amp; Managing Tickets</a></li>
        <li><a href="#playbook-incident" class="table-link">Playbook — Incident Response</a></li>
        <li><a href="#playbook-service-request" class="table-link">Playbook — Service Requests</a></li>
        <li><a href="#playbook-change" class="table-link">Playbook — Change Management</a></li>
        <li><a href="#escalation" class="table-link">Escalation Procedures</a></li>
    </ul>
</div>

<!-- ── Getting Started ───────────────────────────────────────────────────── -->
<div class="card profile-card mb-lg" id="getting-started">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-rocket" aria-hidden="true"></i>
        Getting Started
    </h2>
    <p class="instructions__p">The ITSM module is your central hub for tracking IT issues, service requests, problems, and planned changes. Every piece of IT work that needs tracking gets a ticket with a unique number (e.g. <strong>TKT-000042</strong>).</p>

    <p class="section-label">Core concepts</p>
    <dl class="field-list">
        <div class="field-list__row">
            <dt>Ticket</dt>
            <dd>A record of any IT work item — an outage, a request, a bug, or a planned change. Tickets are the unit of work in the ITSM system.</dd>
        </div>
        <div class="field-list__row">
            <dt>Ticket Number</dt>
            <dd>Auto-assigned on creation (<code>TKT-000001</code> format). Immutable — use this to reference tickets in emails, chat, or meetings.</dd>
        </div>
        <div class="field-list__row">
            <dt>Assignee</dt>
            <dd>The team member responsible for resolving the ticket. A ticket without an assignee is unowned — assign it immediately.</dd>
        </div>
        <div class="field-list__row">
            <dt>Reporter</dt>
            <dd>The person who reported the issue. Can be external (a name + email is sufficient — they don't need a system account).</dd>
        </div>
        <div class="field-list__row">
            <dt>Owner</dt>
            <dd>The system user who created the ticket record. Distinct from the reporter and the assignee.</dd>
        </div>
    </dl>
</div>

<!-- ── Ticket Types ──────────────────────────────────────────────────────── -->
<div class="card profile-card mb-lg" id="ticket-types">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-shapes" aria-hidden="true"></i>
        Ticket Types
    </h2>
    <p class="instructions__p">Select the type that best describes the work. Type determines the appropriate playbook and response timeline.</p>

    <div class="field-list">
        <div class="field-list__row">
            <dt><span class="badge badge--warning">Incident</span></dt>
            <dd>
                <strong>An unplanned interruption or degradation of an IT service.</strong><br>
                Examples: server outage, application error affecting users, network connectivity failure, data loss event.<br>
                <span class="text-muted">Goal: restore service as quickly as possible.</span>
            </dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--info">Service Request</span></dt>
            <dd>
                <strong>A formal request for a new service, access, or standard change.</strong><br>
                Examples: new user account, software installation, access permission, equipment request.<br>
                <span class="text-muted">Goal: fulfil the request within agreed SLA.</span>
            </dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--neutral">Problem</span></dt>
            <dd>
                <strong>A root-cause investigation for a recurring incident or major outage.</strong><br>
                Examples: repeated database crashes, recurring VPN drops, pattern of failed logins.<br>
                <span class="text-muted">Goal: identify and eliminate the root cause permanently.</span>
            </dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--neutral">Change</span></dt>
            <dd>
                <strong>A planned modification to the IT environment.</strong><br>
                Examples: OS patch deployment, firewall rule update, infrastructure upgrade, software release.<br>
                <span class="text-muted">Goal: implement the change with minimal risk and disruption.</span>
            </dd>
        </div>
    </div>
</div>

<!-- ── Priority Matrix ───────────────────────────────────────────────────── -->
<div class="card profile-card mb-lg" id="priority-matrix">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
        Priority Matrix
    </h2>
    <p class="instructions__p">Priority is determined by <strong>impact</strong> (how many users are affected) × <strong>urgency</strong> (how quickly the business is harmed). Set the highest applicable priority.</p>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>Impact</th>
                    <th>Urgency</th>
                    <th>Response Target</th>
                    <th>Resolution Target</th>
                    <th>Examples</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge--warning">Critical</span></td>
                    <td>All / Multiple business units</td>
                    <td>Business stopped</td>
                    <td>15 minutes</td>
                    <td>4 hours</td>
                    <td>Total outage, data breach, security compromise</td>
                </tr>
                <tr>
                    <td><span class="badge badge--info">High</span></td>
                    <td>Many users or a key system</td>
                    <td>Business severely impacted</td>
                    <td>1 hour</td>
                    <td>8 hours</td>
                    <td>Core system degraded, key team blocked</td>
                </tr>
                <tr>
                    <td><span class="badge badge--neutral">Medium</span></td>
                    <td>Some users, workaround available</td>
                    <td>Business impacted but functioning</td>
                    <td>4 hours</td>
                    <td>2 business days</td>
                    <td>Feature broken, partial outage, slow performance</td>
                </tr>
                <tr>
                    <td><span class="badge badge--neutral">Low</span></td>
                    <td>One user or cosmetic issue</td>
                    <td>Minimal business impact</td>
                    <td>1 business day</td>
                    <td>5 business days</td>
                    <td>Single-user issues, cosmetic bugs, minor requests</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="section-label section-label--mt">Priority override rule</p>
    <p class="instructions__p">A Critical ticket <strong>always takes precedence</strong> over anything else. If a ticket is escalated to Critical, the assignee must acknowledge within 15 minutes or the ticket is escalated to the next person in the chain.</p>
</div>

<!-- ── Ticket Lifecycle ──────────────────────────────────────────────────── -->
<div class="card profile-card mb-lg" id="ticket-lifecycle">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>
        Ticket Lifecycle
    </h2>
    <p class="instructions__p">Every ticket moves through five stages. The current stage is shown by the progress bar at the top of the ticket detail page.</p>

    <!-- Visual step-progress bar -->
    <div class="step-progress mb-lg">
        <div class="step-progress__step is-done">
            <div class="step-progress__node"><i class="fa-solid fa-check" aria-hidden="true"></i></div>
            <span class="step-progress__label">New</span>
        </div>
        <div class="step-progress__connector is-done"></div>
        <div class="step-progress__step is-done">
            <div class="step-progress__node"><i class="fa-solid fa-check" aria-hidden="true"></i></div>
            <span class="step-progress__label">In Progress</span>
        </div>
        <div class="step-progress__connector is-done"></div>
        <div class="step-progress__step is-active">
            <div class="step-progress__node">3</div>
            <span class="step-progress__label">Pending</span>
        </div>
        <div class="step-progress__connector"></div>
        <div class="step-progress__step">
            <div class="step-progress__node">4</div>
            <span class="step-progress__label">Resolved</span>
        </div>
        <div class="step-progress__connector"></div>
        <div class="step-progress__step">
            <div class="step-progress__node"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
            <span class="step-progress__label">Closed</span>
        </div>
    </div>

    <dl class="field-list">
        <div class="field-list__row">
            <dt><span class="badge badge--neutral">New</span></dt>
            <dd>Ticket has been created and is awaiting triage. Assign it and set the priority immediately. A ticket should not remain in New for longer than the response target for its priority.</dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--info">In Progress</span></dt>
            <dd>The assignee is actively working on the ticket. Update the description with progress notes as work proceeds.</dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--warning">Pending</span></dt>
            <dd>Work is blocked — waiting on a third party, a vendor response, user confirmation, or a dependent change. Always add a note explaining what is being waited on.</dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--success">Resolved</span></dt>
            <dd>The issue has been addressed. Resolution notes <strong>must be completed</strong> before marking resolved. The system records the resolved timestamp automatically. The reporter should be notified.</dd>
        </div>
        <div class="field-list__row">
            <dt><span class="badge badge--neutral">Closed</span></dt>
            <dd>The ticket is complete and verified. No further action is needed. Closed tickets are immutable records. If the issue recurs, open a new ticket referencing this one.</dd>
        </div>
    </dl>

    <p class="section-label section-label--mt">Re-opening a ticket</p>
    <p class="instructions__p">If a resolved issue recurs before the ticket is closed, set the status back to <strong>In Progress</strong>. The system will clear the resolved timestamp automatically. If it recurs after closure, open a new ticket — do not edit a Closed ticket.</p>
</div>

<!-- ── Creating & Managing Tickets ──────────────────────────────────────── -->
<div class="card profile-card mb-lg" id="creating-tickets">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-plus-circle" aria-hidden="true"></i>
        Creating &amp; Managing Tickets
    </h2>

    <p class="section-label">How to create a ticket</p>
    <ol class="instructions__list">
        <li>Click <strong>New Ticket</strong> on the Tickets list page.</li>
        <li>Enter a clear, specific <strong>title</strong> — one sentence describing the issue (e.g. "Email server not accepting connections from mobile clients").</li>
        <li>Fill in the <strong>description</strong> with full details: what happened, when it started, who is affected, any error messages, and steps already taken.</li>
        <li>Set the <strong>type</strong> (Incident / Service Request / Problem / Change) and <strong>priority</strong>.</li>
        <li>Select a <strong>category</strong> to help with routing and reporting.</li>
        <li>Assign the ticket to the responsible team member. If unsure, assign to the team lead.</li>
        <li>Enter the <strong>reporter's name and email</strong> if they are an external user.</li>
        <li>Click <strong>Create Ticket</strong>. The system auto-generates the ticket number.</li>
    </ol>

    <p class="section-label section-label--mt">Writing a good title</p>
    <dl class="field-list">
        <div class="field-list__row"><dt><span class="badge badge--success">Good</span></dt><dd>"VPN connection drops every 30 minutes for London office users"</dd></div>
        <div class="field-list__row"><dt><span class="badge badge--warning">Poor</span></dt><dd>"VPN broken" / "IT issue" / "URGENT HELP"</dd></div>
    </dl>

    <p class="section-label section-label--mt">Updating a ticket</p>
    <ol class="instructions__list">
        <li>Open the ticket and click <strong>Edit</strong>.</li>
        <li>Update the status, assignee, priority, or any other field as work progresses.</li>
        <li>When resolving, fill in the <strong>Resolution Notes</strong> field — describe what was done and why the fix is expected to hold.</li>
        <li>Click <strong>Save Changes</strong>. All changes are tracked by the updated timestamp.</li>
    </ol>

    <p class="section-label section-label--mt">Using search and filters</p>
    <p class="instructions__p">The ticket list can be filtered by status, priority, and type simultaneously. Use the search box to find tickets by title or ticket number. Combine filters to view your queue (e.g. High priority + In Progress).</p>
</div>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- PLAYBOOKS                                                              -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<h2 class="migration-heading mb-lg">
    <i class="fa-solid fa-book-open" aria-hidden="true"></i>
    Response Playbooks
</h2>

<!-- Playbook: Incident Response -->
<div class="card profile-card mb-lg" id="playbook-incident">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        Playbook — Incident Response
    </h2>
    <p class="instructions__p">An incident is an unplanned event that disrupts or degrades service. Speed and communication are the top priorities.</p>

    <p class="section-label">Step 1 — Detect &amp; Log (0–5 min)</p>
    <ol class="instructions__list">
        <li>Create a ticket immediately — type: <strong>Incident</strong>.</li>
        <li>Set priority based on the Priority Matrix above.</li>
        <li>Write a clear title and initial description with what is known so far.</li>
        <li>Assign to the on-call engineer or team lead.</li>
    </ol>

    <p class="section-label section-label--mt">Step 2 — Classify &amp; Communicate (5–15 min)</p>
    <ol class="instructions__list">
        <li>Confirm the scope: how many users are affected? Which systems?</li>
        <li>Upgrade to <strong>Critical</strong> if scope is broader than initially assessed.</li>
        <li>For Critical incidents: notify stakeholders immediately via your standard channel (Slack, email, call).</li>
        <li>Set ticket status to <strong>In Progress</strong>.</li>
    </ol>

    <p class="section-label section-label--mt">Step 3 — Investigate &amp; Contain (ongoing)</p>
    <ol class="instructions__list">
        <li>Identify the affected component (network, server, application, database, etc.).</li>
        <li>Isolate or contain the issue to prevent further spread (e.g. disable affected service, roll back a deployment).</li>
        <li>Check recent changes — deployments, config updates, or infrastructure changes in the last 24 hours are common root causes.</li>
        <li>Update the ticket description as new information is found.</li>
    </ol>

    <p class="section-label section-label--mt">Step 4 — Restore Service</p>
    <ol class="instructions__list">
        <li>Apply the fastest available fix — even a temporary workaround is acceptable to restore service.</li>
        <li>Confirm service is restored with the affected users or by testing.</li>
        <li>Set status to <strong>Resolved</strong> and complete Resolution Notes:</li>
    </ol>
    <div class="card" style="margin: 0.5rem 0 1rem; padding: 0.75rem 1rem; background: var(--color-bg-subtle, #f9fafb);">
        <p class="instructions__p" style="margin:0;"><strong>Resolution Note template:</strong><br>
        <em>Root cause: [what failed and why]<br>
        Fix applied: [what was done to restore service]<br>
        Verification: [how restoration was confirmed]<br>
        Follow-up: [permanent fix or Problem ticket if root cause not fully resolved]</em></p>
    </div>

    <p class="section-label">Step 5 — Post-Incident Review (for Critical/High)</p>
    <ol class="instructions__list">
        <li>Within 24–48 hours of resolution, hold a brief post-incident review.</li>
        <li>If root cause was not fully resolved, open a <strong>Problem ticket</strong> linked to this incident.</li>
        <li>Set ticket status to <strong>Closed</strong> once the review is complete.</li>
    </ol>
</div>

<!-- Playbook: Service Requests -->
<div class="card profile-card mb-lg" id="playbook-service-request">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-hand-holding" aria-hidden="true"></i>
        Playbook — Service Requests
    </h2>
    <p class="instructions__p">Service requests are pre-approved, routine IT tasks. They follow a predictable workflow with a defined fulfilment timeline.</p>

    <p class="section-label">Common service request categories</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Access</dt><dd>New user onboarding, permission grants, password resets, MFA setup, account unlocks</dd></div>
        <div class="field-list__row"><dt>Software</dt><dd>Application installation, licence allocation, software version upgrade</dd></div>
        <div class="field-list__row"><dt>Hardware</dt><dd>New device provisioning, peripheral requests, replacement equipment</dd></div>
        <div class="field-list__row"><dt>Email</dt><dd>Mailbox creation, alias setup, distribution list changes, email rule configuration</dd></div>
        <div class="field-list__row"><dt>Network</dt><dd>VPN access, Wi-Fi credentials, printer setup, port opening requests</dd></div>
    </dl>

    <p class="section-label section-label--mt">Fulfilment steps</p>
    <ol class="instructions__list">
        <li>Triage the request: confirm it is a standard (pre-approved) change, not an incident.</li>
        <li>Set priority to <strong>Medium</strong> or <strong>Low</strong> unless the request is blocking work.</li>
        <li>Assign to the appropriate team member or queue.</li>
        <li>Set status to <strong>In Progress</strong> when work begins.</li>
        <li>If waiting for user confirmation, approval, or a delivery: set to <strong>Pending</strong> with a note.</li>
        <li>On completion, set to <strong>Resolved</strong> and document what was done and how to verify it.</li>
        <li>Confirm with the reporter before closing.</li>
    </ol>

    <p class="section-label section-label--mt">Requests that require approval</p>
    <p class="instructions__p">Some requests require manager or security team approval before fulfilment (e.g. elevated access, external sharing, new software). Set the ticket to <strong>Pending</strong> while approval is being obtained and note who has been asked to approve.</p>
</div>

<!-- Playbook: Change Management -->
<div class="card profile-card mb-lg" id="playbook-change">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-code-branch" aria-hidden="true"></i>
        Playbook — Change Management
    </h2>
    <p class="instructions__p">Changes are planned modifications. The goal is to implement improvements while protecting service stability.</p>

    <p class="section-label">Change types</p>
    <dl class="field-list">
        <div class="field-list__row">
            <dt>Standard Change</dt>
            <dd>Pre-approved, low-risk, routine (e.g. monthly patch Tuesday). Use a <strong>Service Request</strong> ticket type for these.</dd>
        </div>
        <div class="field-list__row">
            <dt>Normal Change</dt>
            <dd>Requires a change ticket, risk assessment, and approval before implementation.</dd>
        </div>
        <div class="field-list__row">
            <dt>Emergency Change</dt>
            <dd>A critical fix required outside the normal window. Requires retrospective documentation immediately after implementation.</dd>
        </div>
    </dl>

    <p class="section-label section-label--mt">Normal change process</p>
    <ol class="instructions__list">
        <li>Create a <strong>Change</strong> ticket before any work begins.</li>
        <li>In the description, include:
            <ul style="margin-top: 0.35rem; padding-left: 1.25rem;">
                <li>What is being changed and why</li>
                <li>Affected systems and estimated impact</li>
                <li>Implementation steps</li>
                <li>Rollback plan if the change fails</li>
                <li>Scheduled maintenance window</li>
            </ul>
        </li>
        <li>Get approval from the relevant stakeholder. Set ticket to <strong>Pending</strong> while awaiting approval.</li>
        <li>On approval, set to <strong>In Progress</strong> and proceed during the approved window.</li>
        <li>After implementation, verify the change and its impact on dependent systems.</li>
        <li>Set to <strong>Resolved</strong> with notes confirming successful completion or any deviations from the plan.</li>
        <li>If the change caused an incident, create a linked Incident ticket immediately.</li>
    </ol>

    <p class="section-label section-label--mt">Change review rule</p>
    <p class="instructions__p"><strong>No production changes without a ticket.</strong> If work was done without a ticket, create one retrospectively with full documentation and flag it for review. This maintains the audit trail and enables incident correlation.</p>
</div>

<!-- ── Escalation ────────────────────────────────────────────────────────── -->
<div class="card profile-card mb-lg" id="escalation">
    <h2 class="profile-card__title">
        <i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i>
        Escalation Procedures
    </h2>

    <p class="section-label">When to escalate</p>
    <dl class="field-list">
        <div class="field-list__row"><dt>Immediately</dt><dd>Priority is Critical and there is no immediate path to resolution</dd></div>
        <div class="field-list__row"><dt>Response target missed</dt><dd>Ticket has not been acknowledged within the response target for its priority</dd></div>
        <div class="field-list__row"><dt>Resolution target at risk</dt><dd>Ticket resolution is going to miss its target and the reporter has not been notified</dd></div>
        <div class="field-list__row"><dt>Scope expanding</dt><dd>An incident is spreading to additional systems or users beyond the original assessment</dd></div>
        <div class="field-list__row"><dt>Stalled</dt><dd>Ticket has been in Pending for more than 24 hours with no update</dd></div>
    </dl>

    <p class="section-label section-label--mt">How to escalate</p>
    <ol class="instructions__list">
        <li>Upgrade the ticket priority if warranted (e.g. Medium → High).</li>
        <li>Re-assign to the team lead or escalation contact.</li>
        <li>Update the description with a note: current status, what has been tried, why escalation is needed, and the time escalated.</li>
        <li>Notify the escalation contact directly — do not rely on the ticket notification alone for Critical issues.</li>
    </ol>

    <p class="section-label section-label--mt">Escalation is not failure</p>
    <p class="instructions__p">Escalating a ticket at the right time is the correct action. Holding a ticket that needs escalation to avoid embarrassment wastes time and worsens impact. When in doubt, escalate early.</p>
</div>
