<?php

class Logger
{
    private static ?self $instance = null;

    private function __construct()
    {
        $dir = self::logDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function logDir(): string
    {
        return __DIR__ . '/../../storage/logs';
    }

    public static function logFile(): string
    {
        return self::logDir() . '/app.log';
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $entry = json_encode([
            'ts'      => date('c'),
            'level'   => $level,
            'message' => $message,
            'context' => $context ?: null,
            'request' => $this->requestContext(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        @file_put_contents(self::logFile(), $entry, FILE_APPEND | LOCK_EX);
    }

    private function requestContext(): array
    {
        $ctx = [
            'method'  => $_SERVER['REQUEST_METHOD']  ?? null,
            'uri'     => $_SERVER['REQUEST_URI']      ?? null,
            'ip'      => $_SERVER['HTTP_X_FORWARDED_FOR']
                            ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
                            : ($_SERVER['REMOTE_ADDR'] ?? null),
            'ua'      => $_SERVER['HTTP_USER_AGENT']  ?? null,
            'browser' => $this->detectBrowser($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'referer' => $_SERVER['HTTP_REFERER']     ?? null,
        ];

        // Only read session data if a session is already active — never start one here.
        if (session_status() === PHP_SESSION_ACTIVE) {
            $ctx['user_id']   = $_SESSION['user_id']   ?? null;
            $ctx['user_name'] = $_SESSION['user_name'] ?? null;
            $ctx['user_type'] = $_SESSION['user_type'] ?? null;
        }

        return array_filter($ctx, fn($v) => $v !== null && $v !== '');
    }

    private function detectBrowser(string $ua): string
    {
        if ($ua === '') return 'Unknown';
        if (str_contains($ua, 'Edg/'))    return 'Edge';
        if (str_contains($ua, 'OPR/'))    return 'Opera';
        if (str_contains($ua, 'Chrome/')) return 'Chrome';
        if (str_contains($ua, 'Firefox/')) return 'Firefox';
        if (str_contains($ua, 'Safari/')) return 'Safari';
        if (str_contains($ua, 'curl/'))   return 'curl';
        if (stripos($ua, 'bot') !== false) return 'Bot';
        return 'Unknown';
    }
}
