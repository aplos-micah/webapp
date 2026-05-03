<?php

class Router
{
    /**
     * Optional router-level page overrides.
     *
     * Pages do not need to be listed here. Any page with a folder under
     * Application/Pages/ will be routed automatically using defaults.
     *
     * Only add an entry here to set overrides when no per-page config.php exists:
     *   'template'      — filename inside Application/Templates/ (default: 'default.php')
     *   'requiresLogin' — require an authenticated session (default: false)
     */
    private static array $pages = [
        'login' => [
            'template'      => 'default.php',
            'requiresLogin' => false,
        ],
    ];

    /**
     * Dispatch the current request:
     *   1. Resolve slug from URI
     *   2. Load per-page config.php (overrides defaults)
     *   3. Auth gate
     *   4. Load controller.php (business logic, sets $data)
     *   5. Capture view.php output into $content
     *   6. Render $content inside the template
     */
    public static function dispatch(): void
    {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $slug = strtolower(trim($uri, '/')) ?: 'home';

        // OAuth well-known discovery — RFC 8414, no auth required
        if ($slug === '.well-known/oauth-authorization-server') {
            self::dispatchOAuthMeta();
            return;
        }

        // OAuth protected resource metadata — RFC 9728, tells clients where the auth server is
        if ($slug === '.well-known/oauth-protected-resource'
            || str_starts_with($slug, '.well-known/oauth-protected-resource/')) {
            $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'] ?? '';
            $baseUrl = rtrim(getenv('APP_URL') ?: "{$scheme}://{$host}", '/');
            Response::json([
                'resource'              => $baseUrl,
                'authorization_servers' => [$baseUrl],
            ])->send();
        }

        // OAuth authorize — user-facing consent page, no auth gate (handles it internally)
        if ($slug === 'authorize') {
            self::dispatchOAuthAuthorize();
            return;
        }

        // OAuth token exchange — JSON only, no session required
        // Also handle /token (Claude.ai MCP connector uses this path)
        if ($slug === 'oauth/token' || $slug === 'token') {
            self::dispatchOAuthToken();
            return;
        }

        // API v2 discovery — GET /api_v2
        if ($slug === 'api_v2') {
            if (!self::isLoggedIn()) {
                Response::json(['ok' => false, 'error' => 'Unauthorized.'], 401)->send();
            }
            $response = require __DIR__ . '/Api_v2/controller.php';
            if ($response instanceof Response) {
                $response->send();
            }
            Response::json(['ok' => false, 'error' => 'No response from API discovery.'], 500)->send();
        }

        // API v2 track — module-scoped: api_v2/{module}/{endpoint}
        if (str_starts_with($slug, 'api_v2/')) {
            if (!self::isLoggedIn()) {
                Response::json(['ok' => false, 'error' => 'Unauthorized.'], 401)->send();
            }
            self::dispatchApiV2($slug);
        }

        // MCP v2 — dynamic module-scoped MCP
        if ($slug === 'api/mcp_v2') {
            self::dispatchMcpV2();
            return;
        }

        $pageDir      = self::resolvePageDir($slug);
        $pageConfig   = self::pageConfig($slug, $pageDir);
        $templateFile = __DIR__ . '/Templates/' . $pageConfig['template'];

        // Auth gate — login required
        if ($pageConfig['requiresLogin'] && !self::isLoggedIn()) {
            self::redirect('/login');
        }

        // Auth gate — specific user type required
        if ($pageConfig['requiresUserType'] !== null
            && self::userType() !== $pageConfig['requiresUserType']) {
            Logger::getInstance()->warning('Authorization denied', [
                'slug'     => $slug,
                'required' => $pageConfig['requiresUserType'],
                'actual'   => self::userType(),
                'user_id'  => $_SESSION['user_id'] ?? null,
            ]);
            self::setFlash('error', 'You do not have permission to access that page.');
            self::redirect('/home');
        }

        // 404 if the page folder doesn't exist
        if (!is_dir($pageDir)) {
            Logger::getInstance()->warning('Page not found', [
                'slug' => $slug,
                'uri'  => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            self::load404($templateFile);
            return;
        }

        // 1. Controller — business logic, may define $data for the view
        $controllerFile = $pageDir . '/controller.php';
        $data = [];
        if (file_exists($controllerFile)) {
            $response = require $controllerFile;
            if ($response instanceof Response) {
                $response->send();
            }
        }

        // 2. View — capture output into $content
        $viewFile = $pageDir . '/view.php';
        if (!file_exists($viewFile)) {
            Logger::getInstance()->warning('View not found', ['slug' => $slug]);
            self::load404($templateFile);
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // 3. Render into template
        require $templateFile;
    }

    /**
     * Resolve the page directory with a case-insensitive folder match.
     *
     * Single-segment slugs (e.g. "home") resolve to Application/Pages/{Page}/.
     * Multi-segment slugs resolve to Application/Module/{Module}/Pages/{...}/.
     *   Two segments:   admin/debug_currentuser → Module/Admin/Pages/Debug_CurrentUser/
     *   Three segments: crm/accounts/list       → Module/CRM/Pages/Accounts/List/
     */
    private static function resolvePageDir(string $slug): string
    {
        if (!str_contains($slug, '/')) {
            // Standard page
            $pagesRoot = __DIR__ . '/Pages';
            foreach (scandir($pagesRoot) as $entry) {
                if (strtolower($entry) === $slug) {
                    return $pagesRoot . '/' . $entry;
                }
            }
            return $pagesRoot . '/' . $slug;
        }

        // Module page: first segment = module, remainder = nested page path
        [$moduleName, $pagePath] = explode('/', $slug, 2);
        $modulesRoot = __DIR__ . '/Module';
        if (is_dir($modulesRoot)) {
            foreach (scandir($modulesRoot) as $moduleEntry) {
                if (strtolower($moduleEntry) === $moduleName) {
                    $pagesRoot = $modulesRoot . '/' . $moduleEntry . '/Pages';
                    if (is_dir($pagesRoot)) {
                        $resolved = self::resolveNestedPath($pagesRoot, explode('/', $pagePath));
                        if ($resolved !== null) {
                            return $resolved;
                        }
                    }
                    break;
                }
            }
        }

        // No match — return a path that won't exist so the 404 branch fires.
        return __DIR__ . '/Pages/' . $slug;
    }

    /**
     * Recursively resolve a case-insensitive path of segments within $base.
     * Returns the resolved absolute path, or null if any segment is not found.
     */
    private static function resolveNestedPath(string $base, array $segments): ?string
    {
        if (empty($segments)) {
            return null;
        }
        $target = strtolower(array_shift($segments));
        foreach (scandir($base) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (strtolower($entry) === $target) {
                $path = $base . '/' . $entry;
                if (empty($segments)) {
                    return $path;
                }
                if (is_dir($path)) {
                    return self::resolveNestedPath($path, $segments);
                }
            }
        }
        return null;
    }

    private static function pageConfig(string $slug, string $pageDir): array
    {
        $defaults = [
            'template'         => 'default.php',
            'requiresLogin'    => false,
            'requiresUserType' => null,
        ];

        // For module pages, load the module-level config first
        $moduleConfig = [];
        if (str_contains($slug, '/')) {
            [$moduleName] = explode('/', $slug, 2);
            $modulesRoot  = __DIR__ . '/Module';
            if (is_dir($modulesRoot)) {
                foreach (scandir($modulesRoot) as $moduleEntry) {
                    if (strtolower($moduleEntry) === $moduleName) {
                        $moduleConfigFile = $modulesRoot . '/' . $moduleEntry . '/module.php';
                        if (file_exists($moduleConfigFile)) {
                            $moduleConfig = require $moduleConfigFile;
                        }
                        break;
                    }
                }
            }
        }

        $pageConfigFile = $pageDir . '/config.php';
        $pageOverrides  = file_exists($pageConfigFile) ? (require $pageConfigFile) : [];

        return array_merge($defaults, self::$pages[$slug] ?? [], $moduleConfig, $pageOverrides);
    }

    private static function dispatchApiV2(string $slug): never
    {
        // Require both module and endpoint segments: api_v2/{module}/{endpoint}
        $parts = explode('/', substr($slug, 7), 2);
        if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
            Response::json(['ok' => false, 'error' => 'Endpoint not found.'], 404)->send();
        }
        [$moduleName, $endpointName] = $parts;

        // Case-insensitive module lookup
        $modulesRoot = __DIR__ . '/Module';
        $moduleDir   = null;
        foreach (scandir($modulesRoot) as $entry) {
            if ($entry !== '.' && $entry !== '..' && strtolower($entry) === $moduleName && is_dir($modulesRoot . '/' . $entry)) {
                $moduleDir = $modulesRoot . '/' . $entry;
                break;
            }
        }
        if ($moduleDir === null) {
            Response::json(['ok' => false, 'error' => 'Endpoint not found.'], 404)->send();
        }

        // Enforce module access rules from module.php
        $moduleConfigFile = $moduleDir . '/module.php';
        if (file_exists($moduleConfigFile)) {
            $config = require $moduleConfigFile;
            if (!empty($config['requiresUserType']) && self::userType() !== $config['requiresUserType']) {
                Response::json(['ok' => false, 'error' => 'Unauthorized.'], 401)->send();
            }
            foreach ($config as $key => $val) {
                if (str_starts_with($key, 'requiresModule') && $val === true) {
                    $sessionKey = 'module_' . strtolower(substr($key, 14));
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    if (empty($_SESSION[$sessionKey])) {
                        Response::json(['ok' => false, 'error' => 'Unauthorized.'], 401)->send();
                    }
                }
            }
        }

        // Case-insensitive endpoint lookup in Module/{Name}/Api/
        $apiRoot     = $moduleDir . '/Api';
        $endpointDir = null;
        if (is_dir($apiRoot)) {
            foreach (scandir($apiRoot) as $entry) {
                if ($entry !== '.' && $entry !== '..' && strtolower($entry) === $endpointName && is_dir($apiRoot . '/' . $entry)) {
                    $endpointDir = $apiRoot . '/' . $entry;
                    break;
                }
            }
        }

        if ($endpointDir === null || !file_exists($endpointDir . '/controller.php')) {
            Response::json(['ok' => false, 'error' => 'Endpoint not found.'], 404)->send();
        }

        $response = require $endpointDir . '/controller.php';
        if ($response instanceof Response) {
            $response->send();
        }

        Response::json(['ok' => false, 'error' => 'No response from endpoint.'], 500)->send();
    }

    private static function dispatchMcpV2(): never
    {
        $controllerFile = __DIR__ . '/Mcp_v2/controller.php';
        $response = require $controllerFile;
        if ($response instanceof Response) {
            $response->send();
        }

        Response::json(['error' => 'No response from MCP v2 endpoint.'], 500)->send();
    }

    private static function dispatchOAuthMeta(): never
    {
        $response = require __DIR__ . '/Pages/Authorize/meta.php';
        if ($response instanceof Response) {
            $response->send();
        }
        Response::json(['error' => 'No response from OAuth meta endpoint.'], 500)->send();
    }

    private static function dispatchOAuthAuthorize(): void
    {
        $controllerFile = __DIR__ . '/Pages/Authorize/controller.php';
        $data = [];
        if (file_exists($controllerFile)) {
            $response = require $controllerFile;
            if ($response instanceof Response) {
                $response->send();
            }
        }
        $viewFile     = __DIR__ . '/Pages/Authorize/view.php';
        $templateFile = __DIR__ . '/Templates/default.php';
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require $templateFile;
    }

    private static function dispatchOAuthToken(): never
    {
        $response = require __DIR__ . '/Pages/Authorize/token.php';
        if ($response instanceof Response) {
            $response->send();
        }
        Response::json(['error' => 'invalid_request', 'error_description' => 'No response.'], 500)->send();
    }

    private static function load404(string $templateFile): void
    {
        if (self::isLoggedIn()) {
            self::setFlash('error', 'The page you were looking for could not be found.');
            self::redirect('/home');
        }

        self::redirect('/login');
    }

    private static function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
    }

    private static function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['user_id']);
    }

    private static function userType(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_type'] ?? null;
    }

    private static function redirect(string $path): never
    {
        Response::redirect($path)->send();
    }
}
