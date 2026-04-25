<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$oauth = Container::get('oauth');

// ── POST: user submitted the consent form ─────────────────────────────────────
// Handle before GET validation — POST params come from the form body, not the URL.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $_POST is not reliably populated on this server — read directly from php://input.
    parse_str(file_get_contents('php://input'), $post);

    $action        = $post['_action']         ?? '';
    $postClient    = $post['client_id']       ?? '';
    $postRedirect  = $post['redirect_uri']    ?? '';
    $postChallenge = $post['code_challenge']  ?? '';
    $postState     = $post['state']           ?? '';
    $userId        = $_SESSION['user_id']     ?? null;

    if (!$userId) {
        return Response::redirect('/login');
    }

    $postOauth = $oauth->findClient($postClient, $postRedirect);
    if (!$postOauth) {
        http_response_code(400);
        $data['oauth_error'] = 'Invalid consent submission.';
        return;
    }

    if ($action === 'deny') {
        $sep = str_contains($postRedirect, '?') ? '&' : '?';
        return Response::redirect($postRedirect . $sep . 'error=access_denied&state=' . urlencode($postState));
    }

    if ($action === 'allow') {
        $code = $oauth->createCode($postClient, (int) $userId, $postRedirect, $postChallenge);
        $sep  = str_contains($postRedirect, '?') ? '&' : '?';
        return Response::redirect($postRedirect . $sep . 'code=' . urlencode($code) . '&state=' . urlencode($postState));
    }

    http_response_code(400);
    $data['oauth_error'] = 'Unknown action.';
    return;
}

// ── GET: read and validate OAuth parameters ───────────────────────────────────

// After redirecting through /login, params arrive from session, not the URL.
// QUERY_STRING is not populated on this cPanel/Apache setup — extract from REQUEST_URI.
$pending = $_SESSION['oauth_pending'] ?? [];

$_uri = $_SERVER['REQUEST_URI'] ?? '';
$_pos = strpos($_uri, '?');
parse_str($_pos !== false ? substr($_uri, $_pos + 1) : '', $qs);

$clientId            = trim($qs['client_id']             ?? $pending['client_id']             ?? '');
$redirectUri         = trim($qs['redirect_uri']          ?? $pending['redirect_uri']          ?? '');
$responseType        = trim($qs['response_type']         ?? $pending['response_type']         ?? '');
$codeChallenge       = trim($qs['code_challenge']        ?? $pending['code_challenge']        ?? '');
$codeChallengeMethod = trim($qs['code_challenge_method'] ?? $pending['code_challenge_method'] ?? '');
$state               = trim($qs['state']                 ?? $pending['state']                 ?? '');

if (!empty($pending)) {
    unset($_SESSION['oauth_pending']);
}

if ($responseType !== 'code' || $codeChallenge === '' || $codeChallengeMethod !== 'S256') {
    http_response_code(400);
    $data['oauth_error'] = 'Invalid or missing OAuth parameters.';
    return;
}

$client = $oauth->findClient($clientId, $redirectUri);

if (!$client) {
    http_response_code(400);
    $data['oauth_error'] = 'Unknown client or redirect URI not allowed.';
    return;
}

// Login gate — store params in session and redirect if not authenticated
if (empty($_SESSION['user_id'])) {
    $_SESSION['oauth_pending'] = [
        'client_id'             => $clientId,
        'redirect_uri'          => $redirectUri,
        'response_type'         => $responseType,
        'code_challenge'        => $codeChallenge,
        'code_challenge_method' => $codeChallengeMethod,
        'state'                 => $state,
    ];
    return Response::redirect('/login');
}

// Render consent view
$data = [
    'client_name'    => $client['name'],
    'client_id'      => $clientId,
    'redirect_uri'   => $redirectUri,
    'code_challenge' => $codeChallenge,
    'state'          => $state,
];
