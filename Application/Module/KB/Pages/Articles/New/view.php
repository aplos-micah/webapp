<?php
$pageTitle = 'New Article';
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$old = fn(string $k) => $e($_POST[$k] ?? '');
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Knowledge Base</p>
        <h1 class="dash-header__title">New Article</h1>
    </div>
    <div>
        <a href="/kb/articles/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Articles
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($error): ?>
<div class="alert alert--error mb-md"><?= $e($error) ?></div>
<?php endif; ?>

<form method="POST" action="/kb/articles/new">

    <div class="content-panels mb-xl">

        <!-- Article metadata -->
        <div class="card content-panel">
            <h2 class="content-panel__title">
                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                Article
            </h2>

            <div class="form-group">
                <label class="label" for="title">Title <span class="required-star">*</span></label>
                <input type="text" id="title" name="title" class="input" value="<?= $old('title') ?>" required autocomplete="off">
            </div>

            <div class="form-row">
                <div class="form-group form-group--half">
                    <label class="label" for="category">Category</label>
                    <select id="category" name="category" class="input">
                        <option value="">— Select —</option>
                        <?php foreach (Article::CATEGORIES as $c): ?>
                        <option value="<?= $e($c) ?>"<?= $old('category') === $c ? ' selected' : '' ?>><?= $e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-group--half">
                    <label class="label" for="status">Status</label>
                    <select id="status" name="status" class="input">
                        <?php foreach (Article::STATUSES as $s): ?>
                        <option value="<?= $e($s) ?>"<?= ($old('status') ?: 'Draft') === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="label" for="tags">Tags</label>
                <input type="text" id="tags" name="tags" class="input" value="<?= $old('tags') ?>" autocomplete="off" placeholder="Comma-separated, e.g. wifi, password reset, vpn">
            </div>
        </div>

        <!-- Content -->
        <div class="card content-panel">
            <h2 class="content-panel__title">
                <i class="fa-solid fa-align-left" aria-hidden="true"></i>
                Content
            </h2>

            <div class="form-group">
                <label class="label" for="content">Article Body</label>
                <textarea id="content" name="content" class="input textarea" rows="20" placeholder="Write the article here…"><?= $old('content') ?></textarea>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary">Save Article</button>
        <a href="/kb/articles/list" class="btn btn--ghost">Cancel</a>
    </div>

</form>
