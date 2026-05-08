<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$articleObj = KBContainer::get('article');
$error      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'     => trim($_POST['title']    ?? ''),
        'category'  => trim($_POST['category'] ?? '') ?: null,
        'status'    => $_POST['status']         ?? 'Draft',
        'tags'      => trim($_POST['tags']      ?? '') ?: null,
        'content'   => trim($_POST['content']   ?? '') ?: null,
        'author_id' => (int) ($_SESSION['user_id'] ?? 0) ?: null,
    ];

    $result = $articleObj->create($data);
    if ($result['ok']) {
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Article created successfully.'];
        return Response::redirect('/kb/articles/details?id=' . $result['id']);
    }
    $error = $result['error'];
}
