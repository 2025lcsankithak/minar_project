<?php

require_once __DIR__ . '/auth.php';

$loginPages = [
    'student' => '/student/index.php',
    'mentor' => '/mentor/index.php',
    'admin' => '/admin/index.php',
];
$role = $_SESSION['role'] ?? '';
$redirectPath = $loginPages[$role] ?? '/index.php';

$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . $redirectPath);
exit;
