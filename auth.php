<?php

require_once __DIR__ . '/config.php';

session_start();

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
    if (!$user || !password_verify($password, $user['password'])) return 'Incorrect username or password for this portal.';

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    return null;
}
