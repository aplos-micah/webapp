<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

const TOKENS_PER_PAGE = 25;

$oauthObj = AdminContainer::get('admin_oauth');

// ── Handle POST actions ───────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? '';
    $tab    = $_POST['_tab']    ?? 'active';
    $ctab   = $_POST['_ctab']   ?? 'active';
    $page   = (int) ($_POST['_page'] ?? 1);

    if ($action === 'revoke_token') {
        $id = (int) ($_POST['token_id'] ?? 0);
        $oauthObj->revokeToken($id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Token revoked.'];

    } elseif ($action === 'update_token_expiry') {
        $id        = (int) ($_POST['token_id']   ?? 0);
        $expiresAt = trim($_POST['expires_at']   ?? '');
        if ($id > 0 && $expiresAt !== '') {
            // datetime-local gives Y-m-d\TH:i — normalise to Y-m-d H:i:s
            $expiresAt = date('Y-m-d H:i:s', strtotime($expiresAt));
            $oauthObj->updateTokenExpiry($id, $expiresAt);
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Expiry date updated.'];
        }

    } elseif ($action === 'disable_client') {
        $id = (int) ($_POST['client_db_id'] ?? 0);
        $oauthObj->disableClient($id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Client disabled. No new tokens will be issued.'];

    } elseif ($action === 'enable_client') {
        $id = (int) ($_POST['client_db_id'] ?? 0);
        $oauthObj->enableClient($id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Client re-enabled.'];

    } elseif ($action === 'delete_client') {
        $id = (int) ($_POST['client_db_id'] ?? 0);
        if ($oauthObj->deleteClient($id)) {
            $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Client permanently deleted.'];
        } else {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'Client could not be deleted. Disable it first.'];
        }

    } elseif ($action === 'revoke_client_tokens') {
        $clientId = trim($_POST['client_id'] ?? '');
        $count    = $oauthObj->revokeClientTokens($clientId);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => "Revoked {$count} token(s) for client '{$clientId}'."];

    } elseif ($action === 'register_client') {
        $clientId = trim($_POST['client_id']      ?? '');
        $name     = trim($_POST['name']           ?? '');
        $urisRaw  = trim($_POST['redirect_uris']  ?? '');
        $uris     = array_values(array_filter(array_map('trim', explode("\n", $urisRaw))));

        if ($clientId === '' || $name === '' || empty($uris)) {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => 'Client ID, name, and at least one redirect URI are required.'];
        } elseif ($oauthObj->clientExists($clientId)) {
            $_SESSION['_flash'] = ['type' => 'warning', 'message' => "A client with ID '{$clientId}' is already registered."];
        } else {
            $oauthObj->createClient($clientId, $name, json_encode($uris));
            $_SESSION['_flash'] = ['type' => 'success', 'message' => "Client '{$name}' registered."];
        }

    } elseif ($action === 'delete_code') {
        $id = (int) ($_POST['code_id'] ?? 0);
        $oauthObj->deleteCode($id);
        $_SESSION['_flash'] = ['type' => 'success', 'message' => 'Authorization code deleted.'];

    } elseif ($action === 'purge_codes') {
        $count = $oauthObj->purgeUsedCodes();
        $_SESSION['_flash'] = ['type' => 'success', 'message' => "Purged {$count} used/expired authorization code(s)."];

    } elseif ($action === 'purge_expired') {
        $tokens = $oauthObj->purgeExpiredTokens();
        $codes  = $oauthObj->purgeUsedCodes();
        $_SESSION['_flash'] = ['type' => 'success', 'message' => "Purged {$tokens} expired token(s) and {$codes} used/expired code(s)."];
    }

    return Response::redirect('/admin/manageoauth?tab=' . urlencode($tab) . '&ctab=' . urlencode($ctab ?? 'active') . '&page=' . $page);
}

// ── Resolve tabs and page from REQUEST_URI ────────────────────────────────────

$_uri = $_SERVER['REQUEST_URI'] ?? '';
$_pos = strpos($_uri, '?');
parse_str($_pos !== false ? substr($_uri, $_pos + 1) : '', $_qs);

$tab  = ($_qs['tab']  ?? '') === 'expired'  ? 'expired'  : 'active';
$ctab = ($_qs['ctab'] ?? '') === 'disabled' ? 'disabled' : 'active';

// ── Pagination ────────────────────────────────────────────────────────────────

$activeCount        = $oauthObj->countActiveTokens();
$expiredCount       = $oauthObj->countExpiredTokens();
$activeClientCount  = $oauthObj->countActiveClients();
$disabledClientCount = $oauthObj->countDisabledClients();

$totalCount   = $tab === 'expired' ? $expiredCount : $activeCount;
$totalPages   = max(1, (int) ceil($totalCount / TOKENS_PER_PAGE));
$currentPage  = max(1, min((int) ($_qs['page'] ?? 1), $totalPages));
$offset       = ($currentPage - 1) * TOKENS_PER_PAGE;

// ── Fetch data ────────────────────────────────────────────────────────────────

$clients = $ctab === 'disabled' ? $oauthObj->getDisabledClients() : $oauthObj->getActiveClients();
$activeTokens  = $tab === 'active'   ? $oauthObj->getActiveTokens(TOKENS_PER_PAGE, $offset)  : [];
$expiredTokens = $tab === 'expired'  ? $oauthObj->getExpiredTokens(TOKENS_PER_PAGE, $offset) : [];
$codes         = $oauthObj->getRecentCodes();
