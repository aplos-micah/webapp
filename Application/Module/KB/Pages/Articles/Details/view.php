<?php
$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$statusBadge = [
    'Draft'     => 'badge--neutral',
    'Published' => 'badge--success',
    'Archived'  => 'badge--neutral',
];
?>

<!-- Page header -->
<div class="dash-header">
    <div>
        <p class="eyebrow">Knowledge Base</p>
        <h1 class="dash-header__title"><?= $e($article['title']) ?></h1>
        <p class="dash-header__sub">
            <?= $e($article['category'] ?? '') ?>
            <?php if (!empty($article['category'])): ?> · <?php endif; ?>
            <span class="badge <?= $statusBadge[$article['status']] ?? 'badge--neutral' ?>"><?= $e($article['status']) ?></span>
        </p>
    </div>
    <div class="dash-header__actions">
        <?php if ($canEdit && !$editMode): ?>
        <a href="?id=<?= (int) $article['id'] ?>&edit" class="btn btn--primary">
            <i class="fa-solid fa-pen" aria-hidden="true"></i> Edit
        </a>
        <?php endif; ?>
        <a href="/kb/articles/list" class="btn btn--ghost">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
        </a>
    </div>
</div>

<hr class="divider--green mb-xl">

<?php if ($editError): ?>
<div class="alert alert--error mb-md"><?= $e($editError) ?></div>
<?php endif; ?>

<?php if ($editMode && $canEdit): ?>
<form method="POST" action="/kb/articles/details?id=<?= (int) $article['id'] ?>">
<?php endif; ?>

<!-- Article content card -->
<div class="card profile-card mb-lg">
    <div class="profile-card__header">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-align-left" aria-hidden="true"></i>
            Article
        </h2>
    </div>
    <div class="profile-card__body">
        <?php if ($editMode && $canEdit): ?>

        <div class="form-group">
            <label class="label" for="title">Title <span class="required-star">*</span></label>
            <input type="text" id="title" name="title" class="input" value="<?= $e($article['title']) ?>" required autocomplete="off">
        </div>

        <div class="form-row">
            <div class="form-group form-group--half">
                <label class="label" for="category">Category</label>
                <select id="category" name="category" class="input">
                    <option value="">— Select —</option>
                    <?php foreach (Article::CATEGORIES as $c): ?>
                    <option value="<?= $e($c) ?>"<?= ($article['category'] ?? '') === $c ? ' selected' : '' ?>><?= $e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-group--half">
                <label class="label" for="status">Status</label>
                <select id="status" name="status" class="input">
                    <?php foreach (Article::STATUSES as $s): ?>
                    <option value="<?= $e($s) ?>"<?= $article['status'] === $s ? ' selected' : '' ?>><?= $e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="label" for="tags">Tags</label>
            <input type="text" id="tags" name="tags" class="input" value="<?= $e($article['tags'] ?? '') ?>" autocomplete="off" placeholder="Comma-separated">
        </div>

        <?= RichTextArea::render([
            'name'   => 'content',
            'label'  => 'Content',
            'value'  => $article['content'] ?? '',
            'rows'   => 20,
            'class'  => 'textarea',
            'preset' => 'full',
        ]) ?>

        <?php else: ?>

        <div class="article-body">
            <?= nl2br($e($article['content'] ?? '')) ?>
        </div>

        <?php if (!empty($article['tags'])): ?>
        <div class="article-tags mt-md">
            <?php foreach (array_map('trim', explode(',', $article['tags'])) as $tag):
                if ($tag === '') continue;
            ?>
            <span class="badge badge--neutral"><?= $e($tag) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- Metadata card -->
<div class="card profile-card mb-lg">
    <div class="profile-card__header">
        <h2 class="profile-card__title">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            Metadata
        </h2>
    </div>
    <div class="profile-card__body">
        <dl class="detail-list">
            <div class="detail-list__row">
                <dt>Author</dt>
                <dd><?= $e($article['author_name'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Category</dt>
                <dd><?= $e($article['category'] ?? '—') ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Status</dt>
                <dd><span class="badge <?= $statusBadge[$article['status']] ?? 'badge--neutral' ?>"><?= $e($article['status']) ?></span></dd>
            </div>
            <?php if (!empty($article['tags'])): ?>
            <div class="detail-list__row">
                <dt>Tags</dt>
                <dd><?= $e($article['tags']) ?></dd>
            </div>
            <?php endif; ?>
            <div class="detail-list__row">
                <dt>Views</dt>
                <dd><?= number_format((int) ($article['view_count'] ?? 0)) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Created</dt>
                <dd><?= $e(substr($article['created_at'] ?? '', 0, 10)) ?></dd>
            </div>
            <div class="detail-list__row">
                <dt>Last Updated</dt>
                <dd><?= $e(substr($article['updated_at'] ?? '', 0, 10)) ?></dd>
            </div>
        </dl>
    </div>
</div>

<?php if ($editMode && $canEdit): ?>
<div class="form-actions">
    <button type="submit" class="btn btn--primary">Save Changes</button>
    <a href="/kb/articles/details?id=<?= (int) $article['id'] ?>" class="btn btn--ghost">Cancel</a>
</div>
</form>
<?php endif; ?>
