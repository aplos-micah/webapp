<?php

/**
 * Response
 *
 * Represents an HTTP response. Controllers return a Response instead of calling
 * header() / exit directly. The router calls send() once after the controller
 * returns, which keeps controllers testable (no side effects during execution).
 *
 * Usage:
 *   return Response::redirect('/some/path');
 *   return Response::json(['ok' => true]);
 */
class Response
{
    private function __construct(
        private readonly int    $status,
        private readonly array  $headers,
        private readonly string $body = '',
    ) {}

    public static function redirect(string $url, int $status = 302): self
    {
        return new self($status, ['Location' => $url]);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }

    public function send(): never
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        if ($this->body !== '') {
            echo $this->body;
        }
        exit;
    }

    // ── Inspection helpers (used in tests) ───────────────────────────────────

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
