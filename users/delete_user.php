<?php
require_once '../includes/auth.php';
requireRole(['admin']);
include '../includes/db.php';

$id = intval($_GET['id']);
if ($id != $_SESSION['user_id']) { // Prevent admin deleting themselves
    $conn->query("DELETE FROM users WHERE id=$id");
}
header("Location: manage.php?deleted=1");
exit;
