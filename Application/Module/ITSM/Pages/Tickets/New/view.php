<?php
$pageTitle = 'New Ticket';
$e  = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$v  = fn(string $f, string $d = '') => $e($_POST[$f] ?? $d);
$sel = fn(string $f, string $opt) => (($_POST[$f] ?? '') === $opt) ? ' selected' : '';
?>

<div class="dash-header">
    <div>
        <p class="eyebrow">ITSM / <a href="/itsm/tickets/list">Tickets</a></p>
        <h1 class="dash-header__title">New Ticket</h1>
    </div>
    <div>
        <a href="/itsm/tickets/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Cancel
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($error): ?>
<div class="alert alert--warning mb-md" role="alert">
    <span class="alert__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
    <div class="alert__body"><?= $e($error) ?></div>
</div>
<?php endif; ?>

<form method="POST" action="/itsm/tickets/new" novalidate>

    <!-- Ticket Details -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-ticket" aria-hidden="true"></i>
            Ticket Details
        </h2>
        <div class="form-group">
            <label class="form-label" for="title">Title <span class="form-required">*</span></label>
            <input id="title" type="text" name="title" class="input"
                   value="<?= $v('title') ?>" placeholder="Brief description of the issue" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="input" rows="5"
                      placeholder="Detailed description, steps to reproduce, impact…"><?= $v('description') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="type">Type</label>
                <select id="type" name="type" class="input">
                    <?php foreach (Ticket::TYPES as $t): ?>
                    <option value="<?= $e($t) ?>"<?= $sel('type', $t) ?: ($t === 'Incident' && !isset($_POST['type']) ? ' selected' : '') ?>><?= $e($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="priority">Priority</label>
                <select id="priority" name="priority" class="input">
                    <?php foreach (Ticket::PRIORITIES as $p): ?>
                    <option value="<?= $e($p) ?>"<?= $sel('priority', $p) ?: ($p === 'Medium' && !isset($_POST['priority']) ? ' selected' : '') ?>><?= $e($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="category">Category</label>
                <select id="category" name="category" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (Ticket::CATEGORIES as $c): ?>
                    <option value="<?= $e($c) ?>"<?= $sel('category', $c) ?>><?= $e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Assignment -->
    <div class="card profile-card mb-lg">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
            Assignment &amp; Reporter
        </h2>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="assigned_to">Assign To</label>
                <select id="assigned_to" name="assigned_to" class="input">
                    <option value="">— Unassigned —</option>
                    <?php foreach ($assignableUsers as $u): ?>
                    <option value="<?= (int) $u['id'] ?>"<?= (($_POST['assigned_to'] ?? '') == $u['id']) ? ' selected' : '' ?>>
                        <?= $e($u['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-group--grow">
                <label class="form-label" for="reported_by_name">Reporter Name</label>
                <input id="reported_by_name" type="text" name="reported_by_name" class="input"
                       value="<?= $v('reported_by_name') ?>" placeholder="Who reported this issue?">
            </div>
            <div class="form-group form-group--grow">
                <label class="form-label" for="reported_by_email">Reporter Email</label>
                <input id="reported_by_email" type="email" name="reported_by_email" class="input"
                       value="<?= $v('reported_by_email') ?>" placeholder="reporter@example.com">
            </div>
        </div>
    </div>

    <div class="profile-card__footer">
        <span></span>
        <button type="submit" class="btn btn--primary">
            <i class="fa-solid fa-ticket" aria-hidden="true"></i>
            Create Ticket
        </button>
    </div>

</form>
