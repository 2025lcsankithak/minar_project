<?php

require_once __DIR__ . '/auth.php';

$role = $_POST['role'] ?? '';
$allowedRoles = ['admin', 'mentor', 'student'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($role, $allowedRoles, true)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = login_user($role, trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
if ($error) {
    $_SESSION['login_error'] = $error;
    header('Location: ' . BASE_URL . '/' . $role . '/');
    exit;
}

header('Location: ' . BASE_URL . '/' . $role . '.php');
exit;
