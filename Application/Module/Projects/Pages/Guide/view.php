<?php $pageTitle = 'Projects User Guide'; ?>

<div class="dash-header">
    <div>
        <p class="eyebrow">Projects</p>
        <h1 class="dash-header__title">User Guide</h1>
        <p class="dash-header__sub">Project lifecycle, phases, and best practices</p>
    </div>
</div>

<hr class="divider--green mb-xl">

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Overview
    </h2>
    <div class="prose-body">
        <p>The Projects module tracks work from initial idea through completion using a standard five-phase lifecycle based on widely-adopted project management practices (PMBOK / PMI). Each project has an owner, a status, a current phase, and optional schedule and budget information.</p>
    </div>
</div>

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-arrows-turn-right" aria-hidden="true"></i>
        The Five Phases
    </h2>
    <div class="prose-body">
        <table class="data-table mb-md">
            <thead>
                <tr><th>Phase</th><th>Purpose</th><th>Key activities</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Initiation</strong></td>
                    <td>Define the project at a high level and get approval to proceed</td>
                    <td>Write the project brief, identify stakeholders, confirm viability</td>
                </tr>
                <tr>
                    <td><strong>Planning</strong></td>
                    <td>Define scope, schedule, budget, and risks in detail</td>
                    <td>Set start/due dates, allocate budget, break down work, identify dependencies</td>
                </tr>
                <tr>
                    <td><strong>Execution</strong></td>
                    <td>Do the work</td>
                    <td>Assign tasks, track progress, manage team, deliver outputs</td>
                </tr>
                <tr>
                    <td><strong>Monitoring</strong></td>
                    <td>Track against plan and adjust as needed</td>
                    <td>Compare actuals to plan, manage scope changes, update stakeholders</td>
                </tr>
                <tr>
                    <td><strong>Closure</strong></td>
                    <td>Formally complete the project and capture lessons learned</td>
                    <td>Confirm deliverables accepted, release resources, document retrospective</td>
                </tr>
            </tbody>
        </table>
        <p>Move the phase forward as work progresses. Phases do not have to be strictly sequential — a project can return to Planning from Execution if scope changes significantly.</p>
    </div>
</div>

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
        Project Statuses
    </h2>
    <div class="prose-body">
        <table class="data-table">
            <thead>
                <tr><th>Status</th><th>Meaning</th></tr>
            </thead>
            <tbody>
                <tr><td><span class="badge badge--neutral">Draft</span></td><td>Being defined — not yet approved or started</td></tr>
                <tr><td><span class="badge badge--success">Active</span></td><td>Approved and in progress</td></tr>
                <tr><td><span class="badge badge--warning">On Hold</span></td><td>Paused — waiting on a dependency, decision, or resource</td></tr>
                <tr><td><span class="badge badge--info">Completed</span></td><td>All deliverables accepted and closure complete</td></tr>
                <tr><td><span class="badge badge--neutral">Cancelled</span></td><td>Stopped before completion — retained for record purposes</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
        Priority
    </h2>
    <div class="prose-body">
        <p>Priority reflects the business impact if the project is delayed or fails to deliver. Set it at Initiation and revisit it at each phase transition.</p>
        <table class="data-table">
            <thead><tr><th>Priority</th><th>When to use</th></tr></thead>
            <tbody>
                <tr><td><span class="badge badge--warning">Critical</span></td><td>Business stops or regulatory breach if not delivered on time</td></tr>
                <tr><td><span class="badge badge--info">High</span></td><td>Significant business impact — senior stakeholder dependency</td></tr>
                <tr><td><span class="badge badge--neutral">Medium</span></td><td>Important but not urgent — can absorb minor delays</td></tr>
                <tr><td><span class="badge badge--neutral">Low</span></td><td>Nice to have — can be deprioritised if capacity is constrained</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-rocket" aria-hidden="true"></i>
        Playbook: Starting a New Project
    </h2>
    <div class="prose-body">
        <ol class="prose-list">
            <li>Create the project with status <strong>Draft</strong> and phase <strong>Initiation</strong>. Fill in name, description, and priority.</li>
            <li>Assign a <strong>Project Owner</strong> — the person accountable for delivery.</li>
            <li>Set a <strong>Start Date</strong> and <strong>Due Date</strong> as soon as they are agreed.</li>
            <li>When the project is approved to proceed, change status to <strong>Active</strong> and advance phase to <strong>Planning</strong>.</li>
            <li>Record the budget if known. Update it when actuals diverge from the estimate.</li>
            <li>Move through phases as milestones are reached.</li>
            <li>When all deliverables are accepted, advance to phase <strong>Closure</strong> and then set status to <strong>Completed</strong>. The completed date is set automatically.</li>
        </ol>
    </div>
</div>

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-pause-circle" aria-hidden="true"></i>
        Playbook: Putting a Project On Hold
    </h2>
    <div class="prose-body">
        <ol class="prose-list">
            <li>Change status to <strong>On Hold</strong>.</li>
            <li>Update the <strong>Notes</strong> field with the reason and the expected resume date.</li>
            <li>Do not change the due date unless the hold is confirmed to extend beyond it — update it only when the new timeline is agreed.</li>
            <li>When the blocker is resolved, return status to <strong>Active</strong> and update the due date if it has slipped.</li>
        </ol>
    </div>
</div>

<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
        Managing Overdue Projects
    </h2>
    <div class="prose-body">
        <p>The Dashboard highlights projects whose due date has passed and are not Completed or Cancelled. Review the overdue list weekly:</p>
        <ol class="prose-list">
            <li>If the project is still active and deliverable — update the due date to a realistic new date and note the reason for the slip.</li>
            <li>If the project is blocked — change status to <strong>On Hold</strong> until the blocker is resolved.</li>
            <li>If the project will not be completed — change status to <strong>Cancelled</strong> and add a note explaining the decision. Do not delete it.</li>
        </ol>
    </div>
</div>
