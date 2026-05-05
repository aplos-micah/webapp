<?php

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    return Response::redirect('/admin/userlist');
}

$userObj = AdminContainer::get('admin_user');
$user    = $userObj->findById($id);

if (!$user) {
    $_SESSION['_flash'] = ['type' => 'error', 'message' => 'User not found.'];
    return Response::redirect('/admin/userlist');
}

$editMode  = isset($_GET['edit']);
$editError = null;
$adminId   = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_user') {
        $result = $userObj->updateByAdmin($id, $_POST);
        if ($result['ok']) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'User updated successfully.'];
            return Response::redirect('/admin/users/details?id=' . $id);
        }
        $editMode  = true;
        $editError = $result['error'];
        $user      = $userObj->findById($id);
    }

    if ($action === 'update_access') {
        $modules = $_POST['module_access'] ?? [];
        foreach ($modules as $module => $tier) {
            $userObj->setModuleAccess($id, $module, $tier, $adminId);
        }
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Module access updated.'];
        return Response::redirect('/admin/users/details?id=' . $id);
    }
}

// Discover all modules that declare requiresModule* access control
$modulesRoot    = __DIR__ . '/../../../../../Module';
$managedModules = [];
foreach (scandir($modulesRoot) as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $cfgFile = $modulesRoot . '/' . $entry . '/module.php';
    if (!file_exists($cfgFile)) continue;
    $cfg = require $cfgFile;
    foreach ($cfg as $key => $val) {
        if (str_starts_with($key, 'requiresModule') && $val === true) {
            $managedModules[$entry] = $cfg;
            break;
        }
    }
}
ksort($managedModules);

$moduleAccess = $userObj->getModuleAccess($id);
$pageTitle    = $user['name'];
