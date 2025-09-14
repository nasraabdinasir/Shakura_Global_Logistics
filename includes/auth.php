<?php
session_start();
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireRole($roles = []) {
    if (!isLoggedIn() || !in_array($_SESSION['role'], $roles)) {
        header('Location: ../auth/login.php');
        exit();
    }
}

?>
