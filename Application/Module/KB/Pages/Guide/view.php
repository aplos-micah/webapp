<?php
$pageTitle = 'Knowledge Base User Guide';
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Knowledge Base</p>
        <h1 class="dash-header__title">User Guide</h1>
        <p class="dash-header__sub">Writing, publishing, and maintaining knowledge base articles</p>
    </div>
</div>

<hr class="divider--green mb-xl">

<!-- Overview -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Overview
    </h2>
    <div class="guide-body">
        <p>The Knowledge Base (KB) is a searchable library of internal articles covering procedures, troubleshooting steps, policies, and reference material. The goal is to reduce repeat support requests by capturing resolutions once and making them findable by everyone.</p>
        <p>Articles are authored by team members. Drafts are only visible to the author until published. Once published, all KB users can read and search for the article.</p>
    </div>
</div>

<!-- Article Statuses -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-circle-half-stroke" aria-hidden="true"></i>
        Article Statuses
    </h2>
    <div class="guide-body">
        <table class="data-table">
            <thead>
                <tr><th>Status</th><th>Meaning</th><th>Visible To</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge--neutral">Draft</span></td>
                    <td>Work in progress — not yet ready for publication</td>
                    <td>Author only</td>
                </tr>
                <tr>
                    <td><span class="badge badge--success">Published</span></td>
                    <td>Reviewed, accurate, and ready to read</td>
                    <td>All KB users</td>
                </tr>
                <tr>
                    <td><span class="badge badge--neutral">Archived</span></td>
                    <td>No longer current — kept for historical reference</td>
                    <td>Author and Admin only</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Categories -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-shapes" aria-hidden="true"></i>
        Categories
    </h2>
    <div class="guide-body">
        <table class="data-table">
            <thead>
                <tr><th>Category</th><th>When to use</th></tr>
            </thead>
            <tbody>
                <tr><td><strong>Procedure</strong></td><td>Step-by-step instructions for completing a task (e.g. "How to set up VPN")</td></tr>
                <tr><td><strong>Troubleshooting</strong></td><td>Diagnosis and resolution steps for a known problem (e.g. "Wi-Fi drops every hour")</td></tr>
                <tr><td><strong>FAQ</strong></td><td>Frequently asked questions with concise answers</td></tr>
                <tr><td><strong>Policy</strong></td><td>Rules, standards, or requirements the team must follow</td></tr>
                <tr><td><strong>Reference</strong></td><td>Reference tables, glossaries, or lookup material</td></tr>
                <tr><td><strong>Other</strong></td><td>Anything that does not fit another category</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Writing Good Articles -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
        Writing Good Articles
    </h2>
    <div class="guide-body">
        <ul class="guide-list">
            <li><strong>One topic per article.</strong> A focused article is easier to find and maintain than a mega-document.</li>
            <li><strong>Lead with the outcome.</strong> State what the reader will be able to do after reading (e.g. "After following these steps, you will be able to connect to the VPN from home").</li>
            <li><strong>Use numbered steps for procedures.</strong> Readers can track progress and resume at the correct step if interrupted.</li>
            <li><strong>Be specific about prerequisites.</strong> List what the reader needs before they start (software, access, credentials).</li>
            <li><strong>Avoid jargon without explanation.</strong> When you must use a technical term, briefly define it on first use.</li>
            <li><strong>Include the "why" for policy articles.</strong> Rules are followed more consistently when the reason is understood.</li>
        </ul>
    </div>
</div>

<!-- Tagging Conventions -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-tags" aria-hidden="true"></i>
        Tagging Conventions
    </h2>
    <div class="guide-body">
        <p>Tags improve search relevance. Apply 3–6 tags per article. Use lowercase, singular words or short phrases:</p>
        <ul class="guide-list">
            <li>Use the product or system name: <code>vpn</code>, <code>microsoft 365</code>, <code>slack</code></li>
            <li>Use the symptom or action: <code>password reset</code>, <code>printer offline</code>, <code>email bounce</code></li>
            <li>Use the audience if relevant: <code>new employee</code>, <code>manager</code></li>
            <li>Avoid duplicating the category as a tag (e.g. don't add <code>procedure</code> if the category is already Procedure)</li>
        </ul>
    </div>
</div>

<!-- Publishing Workflow -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
        Publishing Workflow
    </h2>
    <div class="guide-body">
        <ol class="guide-list">
            <li>Create the article with status <strong>Draft</strong>.</li>
            <li>Write the content and add tags.</li>
            <li>Review for accuracy — have a colleague test any procedure steps if possible.</li>
            <li>Change status to <strong>Published</strong>. The article is now searchable by all KB users.</li>
            <li>When the article becomes outdated, update it in place rather than creating a duplicate. If it is no longer relevant, set it to <strong>Archived</strong> rather than deleting.</li>
        </ol>
    </div>
</div>

<!-- KB and ITSM Integration -->
<div class="card content-panel mb-lg">
    <h2 class="content-panel__title">
        <i class="fa-solid fa-link" aria-hidden="true"></i>
        Using KB with ITSM
    </h2>
    <div class="guide-body">
        <p>When resolving an ITSM ticket for a recurring issue, check if a KB article exists. If not, write one — paste the resolution steps directly into a new Troubleshooting article. In the ticket resolution, reference the article title so future agents can find it.</p>
        <p>This transforms one-off ticket resolutions into reusable knowledge and reduces repeat ticket volume over time.</p>
    </div>
</div>
