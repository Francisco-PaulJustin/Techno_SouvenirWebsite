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

if (!DB_HOST || !DB_USER || !DB_PASS) {
    die('Database is not configured. Set DB_HOST, DB_USER and DB_PASS, or create includes/config.local.php.');
}

// Site configuration
define('SITE_NAME', 'MemoCraft');
define('SITE_URL', 'http://localhost/techno_website/souvenir_shop');

// Database connection
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
    
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
    die("Database connection failed: " . $e->getMessage());
}
?>
