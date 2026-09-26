<?php
// Health check for uptime pingers (UptimeRobot / cron-job.org).
// Served at /health on Render (see Dockerfile) and /health.php on XAMPP.
// Each ping keeps the Render instance awake and the Supabase project active.
header('Content-Type: text/plain');
header('Cache-Control: no-store');

// config.php exits with die() if the database is unreachable, so report failure
// by default and only switch to 200 once the connection and query succeed
http_response_code(503);
require_once __DIR__ . '/includes/config.php';

try {
    $pdo->query('SELECT 1');
    http_response_code(200);
    echo 'OK';
} catch (PDOException $e) {
    echo 'Database error';
}
