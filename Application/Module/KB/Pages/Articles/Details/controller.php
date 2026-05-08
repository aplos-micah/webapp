<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    return Response::redirect('/kb/articles/list');
}

$articleObj  = KBContainer::get('article');
$article     = $articleObj->findById($id);
$currentUser = (int) ($_SESSION['user_id'] ?? 0);

if (!$article) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'Article not found.'];
    return Response::redirect('/kb/articles/list');
}

$editMode  = isset($_GET['edit']);
$editError = null;

// Determine edit permission: author or admin
$canEdit = $currentUser > 0 && (
    (int) $article['author_id'] === $currentUser
    || ($_SESSION['user_type'] ?? '') === 'Admin'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
    $data = [
        'title'    => trim($_POST['title']    ?? ''),
        'category' => trim($_POST['category'] ?? '') ?: null,
        'status'   => $_POST['status']         ?? $article['status'],
        'tags'     => trim($_POST['tags']      ?? '') ?: null,
        'content'  => trim($_POST['content']   ?? '') ?: null,
    ];

    $result = $articleObj->update($id, $data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Article updated.'];
        return Response::redirect('/kb/articles/details?id=' . $id);
    }
    $editMode  = true;
    $editError = $result['error'];
    $article   = $articleObj->findById($id);
}

// Increment view count on every GET (not during edit POST)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$editMode) {
    $articleObj->incrementViewCount($id);
}

$pageTitle = $article['title'];
