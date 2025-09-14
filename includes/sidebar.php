<?php
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'] ?? null;
require_once '../includes/db.php';

// Mini-profile
$user_name = '';
//$user_email = '';
if ($user_id) {
    $user_res = $conn->query("SELECT username FROM users WHERE id=$user_id");
    if ($user_res && $user_res->num_rows) {
        $u = $user_res->fetch_assoc();
        $user_name = htmlspecialchars($u['username']);
        //$user_email = htmlspecialchars($u['email']);
    }
}
?>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<nav class="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/images/logo.jpeg" width="30" alt="Logo" style="border-radius:7px; background:#fff6eb; border:1.3px solid #ffe6bb;">
        <span>Shakura Express</span>
    </div>
    <div class="user-mini-profile mb-2">
        <i class="bi bi-person-circle"></i>
        <div class="user-details">
            <span><?= $user_name ?: ucfirst($role) ?></span>
        </div>
    </div>
    <ul class="nav flex-column">
        <?php if($role=='admin'): ?>
            <li><a href="../dashboards/admin_dashboard.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'admin_dashboard.php')!==false?' active':''?>"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="../users/profile.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'profile.php')!==false?' active':''?>"><i class="bi bi-person-circle"></i> My Profile</a></li>
            <hr>
            <li><a href="../parcels/add_parcel.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'add_parcel.php')!==false?' active':''?>"><i class="bi bi-plus-square"></i> Add Parcel</a></li>
            <li><a href="../parcels/view_parcel.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'view_parcel.php')!==false?' active':''?>"><i class="bi bi-box-seam"></i> View Parcels</a></li>
            <li><a href="../pricing/set_pricing.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'set_pricing.php')!==false?' active':''?>"><i class="bi bi-cash-coin"></i> Set Pricing</a></li>
            <li><a href="../invoices/view_invoice.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'view_invoice.php')!==false?' active':''?>"><i class="bi bi-file-earmark-text"></i> All Invoices</a></li>
            <hr>
            <li><a href="../rfid/logs.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'logs.php')!==false?' active':''?>"><i class="bi bi-broadcast"></i> RFID Logs</a></li>
            <li><a href="../users/manage.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'manage.php')!==false?' active':''?>"><i class="bi bi-people"></i> Manage Users</a></li>
        <?php elseif($role=='staff'): ?>
            <li><a href="../dashboards/staff_dashboard.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'staff_dashboard.php')!==false?' active':''?>"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="../parcels/view_parcel.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'view_parcel.php')!==false?' active':''?>"><i class="bi bi-box-seam"></i>Parcels</a></li>

            <li><a href="../rfid/scan.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'scan.php')!==false?' active':''?>"><i class="bi bi-broadcast"></i> RFID Scan</a></li>
            <li><a href="../rfid/logs.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'logs.php')!==false?' active':''?>"><i class="bi bi-clock-history"></i> RFID Logs</a></li>
            <li><a href="../users/profile.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'profile.php')!==false?' active':''?>"><i class="bi bi-person-circle"></i> My Profile</a></li>
        <?php else: ?>
            <li><a href="../dashboards/client_dashboard.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'client_dashboard.php')!==false?' active':''?>"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a href="../parcels/add_parcel.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'add_parcel.php')!==false?' active':''?>"><i class="bi bi-plus-square"></i> Add Parcel</a></li>
            <li><a href="../parcels/view_parcel.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'view_parcel.php')!==false?' active':''?>"><i class="bi bi-box-seam"></i> My Parcels</a></li>
            <li><a href="../invoices/view_invoice.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'view_invoice.php')!==false?' active':''?>"><i class="bi bi-file-earmark-text"></i> My Invoices</a></li>
            <li><a href="../users/profile.php" class="nav-link<?=strpos($_SERVER['PHP_SELF'],'profile.php')!==false?' active':''?>"><i class="bi bi-person-circle"></i> My Profile</a></li>
        <?php endif; ?>
        <hr>
        <li><a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
    </ul>
</nav>
