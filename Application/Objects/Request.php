<?php

/**
 * Request
 *
 * Helpers for reading the incoming request body. $_POST is not reliably
 * populated on this server, so mutation endpoints read php://input directly.
 */
class Request
{
    /**
     * Decode the request body as JSON (or form-encoded as a fallback) into
     * an associative array. Returns [] on an empty or unparseable body.
     */
    public static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            return json_decode($raw, true) ?? [];
        }

        parse_str($raw, $params);
        return $params;
    }
}
