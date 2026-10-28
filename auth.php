<?php

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL ?: '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function require_role(string $role): void
{
    if (($_SESSION['role'] ?? '') === $role) return;
    header('Location: ' . BASE_URL . '/' . $role . '/');
    exit;
}

function login_user(string $role, string $username, string $password): ?string
{
    $statement = db()->prepare('SELECT id, name, role, password FROM users WHERE username = ? AND role = ? LIMIT 1');
    $statement->execute([$username, $role]);
    $user = $statement->fetch();
    if (!$user) return 'Incorrect username or password for this portal.';
    if (!password_verify($password, $user['password'])) return 'Incorrect username or password for this portal.';

    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    return null;
}
