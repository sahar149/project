<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /local-services-platform/public/login.php');
        exit;
    }
}

function requireRole($role) {
    if (!isLoggedIn()) {
        $return_url = urlencode($_SERVER['REQUEST_URI']);
        header("Location: /local-services-platform/public/login.php?role=$role&return_url=$return_url");
        exit;
    }
    if ($_SESSION['user_role'] !== $role) {
        die("Access denied: You are not a $role");
    }
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? null;
}
?>