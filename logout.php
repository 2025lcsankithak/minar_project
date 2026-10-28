<?php

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
verify_csrf_token();

$loginPages = [
    'student' => '/student/index.php',
    'mentor' => '/mentor/index.php',
    'admin' => '/admin/index.php',
];
$role = $_SESSION['role'] ?? '';
$redirectPath = $loginPages[$role] ?? '/index.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}
session_destroy();
header('Location: ' . BASE_URL . $redirectPath);
exit;
