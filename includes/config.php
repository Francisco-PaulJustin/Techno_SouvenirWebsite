<?php
// Database configuration (Supabase PostgreSQL)
// Credentials are never committed. They come from environment variables,
// or from includes/config.local.php (gitignored) - copy config.local.example.php to create it.
$localConfig = __DIR__ . '/config.local.php';
$local = file_exists($localConfig) ? require $localConfig : [];

function configValue($key, $local, $default = null) {
    $env = getenv($key);
    if ($env !== false && $env !== '') {
        return $env;
    }
    return $local[$key] ?? $default;
}

define('DB_HOST', configValue('DB_HOST', $local));
define('DB_PORT', configValue('DB_PORT', $local, '5432'));
define('DB_NAME', configValue('DB_NAME', $local, 'postgres'));
define('DB_USER', configValue('DB_USER', $local));
define('DB_PASS', configValue('DB_PASS', $local));
define('DB_SSLMODE', configValue('DB_SSLMODE', $local, 'require'));

if (!DB_HOST || !DB_USER || !DB_PASS) {
    die('Database is not configured. Set DB_HOST, DB_USER and DB_PASS, or create includes/config.local.php.');
}

// Site configuration
define('SITE_NAME', 'MemoCraft');

// Sessions: every page includes this file, so the session is started here with
// hardened cookie settings (pages must not call session_start() themselves)
if (!defined('SKIP_SESSION') && session_status() === PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'secure' => $is_https,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// CSRF protection: reject POST requests that come from another site.
// Browsers send Origin (or at least Referer) on form posts and fetch() calls.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
    if ($source !== '' && $source !== 'null') {
        $parts = parse_url($source);
        $source_host = ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (strcasecmp($source_host, $_SERVER['HTTP_HOST'] ?? '') !== 0) {
            http_response_code(403);
            die('Request blocked: it did not come from this site.');
        }
    }
}

// Database connection
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=" . DB_SSLMODE;

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('The shop is temporarily unavailable. Please try again in a moment.');
}
?>
