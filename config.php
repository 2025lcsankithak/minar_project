<?php
// Update these values for your local MySQL installation.
define('DB_HOST', 'localhost');
define('DB_NAME', 'mentorship_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/minar_project');

function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function countOf(string $table, string $where = '1=1'): int
{
    return (int) db()->query("SELECT COUNT(*) FROM $table WHERE $where")->fetchColumn();
}
