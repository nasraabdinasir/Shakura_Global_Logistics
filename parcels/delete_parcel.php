<?php
require_once '../includes/auth.php';
include '../includes/db.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

// Fetch parcel
$p = $conn->query("SELECT * FROM parcels WHERE id=$id")->fetch_assoc();
if (!$p) {
    header("Location: view_parcel.php?msg=notfound");
    exit;
}

// Only admin or parcel creator (customer) can delete (customer only if not assigned)
if ($role == 'admin') {
    // Admin can delete any
} elseif ($role == 'customer') {
    if ($p['sender_id'] != $user_id || !empty($p['assigned_staff'])) {
        // Not your parcel, or already assigned
        header("Location: view_parcel.php?msg=notallowed");
        exit;
    }
} else {
    // Staff/others can't delete
    header("Location: view_parcel.php?msg=notallowed");
    exit;
}

// First, delete invoices for this parcel
$conn->query("DELETE FROM invoices WHERE parcel_id = $id");

// Then, delete the parcel
$conn->query("DELETE FROM parcels WHERE id = $id");

// Optionally: Delete parcel logs (if you want to keep database tidy)
$conn->query("DELETE FROM parcel_logs WHERE parcel_id = $id");

header("Location: view_parcel.php?del=1");
exit;
?>
