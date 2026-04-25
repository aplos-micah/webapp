<?php

header('Content-Type: application/json');
header('Cache-Control: no-store');

// $_POST is not reliably populated on this server — read directly from php://input.
$rawBody     = file_get_contents('php://input');
$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

if (str_contains($contentType, 'application/json')) {
    $params = json_decode($rawBody, true) ?? [];
} else {
    parse_str($rawBody, $params);
}

$grantType    = $params['grant_type']    ?? '';
$code         = $params['code']          ?? '';
$codeVerifier = $params['code_verifier'] ?? '';
$clientId     = $params['client_id']     ?? '';
$redirectUri  = $params['redirect_uri']  ?? '';

if ($grantType !== 'authorization_code') {
    Logger::getInstance()->warning('OAuth token: unsupported grant type', ['grant_type' => $grantType]);
    return Response::json(['error' => 'unsupported_grant_type'], 400);
}

if ($code === '' || $codeVerifier === '' || $clientId === '' || $redirectUri === '') {
    Logger::getInstance()->warning('OAuth token: missing parameters', [
        'grant_type' => $grantType, 'code_empty' => $code === '',
        'verifier_empty' => $codeVerifier === '', 'client_empty' => $clientId === '',
        'redirect_empty' => $redirectUri === '', 'content_type' => $contentType,
    ]);
    return Response::json(['error' => 'invalid_request', 'error_description' => 'Missing required parameters.'], 400);
}

$oauth  = Container::get('oauth');
$client = $oauth->findClient($clientId, $redirectUri);
if (!$client) {
    return Response::json(['error' => 'invalid_client'], 401);
}

$token = $oauth->exchangeCode($code, $codeVerifier, $clientId, $redirectUri);
if (!$token) {
    return Response::json(['error' => 'invalid_grant', 'error_description' => 'Code is invalid, expired, or already used.'], 400);
}

return Response::json($token);
