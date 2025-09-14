<?php
require_once '../includes/auth.php';
requireRole(['customer']);
include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$parcels = $conn->query("SELECT * FROM parcels WHERE sender_id='$user_id' ORDER BY created_at DESC");

// Shipment stats for cards
$total = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE sender_id='$user_id'")->fetch_assoc()['c'];
$delivered = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE sender_id='$user_id' AND status='Delivered'")->fetch_assoc()['c'];
$in_transit = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE sender_id='$user_id' AND status NOT IN('Delivered','Label Created')")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE sender_id='$user_id' AND status='Label Created'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Client Dashboard</title>
    <style>
        body { background: #f5eee6; }
        .dashboard-title { color: #a87f51; font-size: 2rem; font-weight: 700; }
        .card-stats { background: #fffaf3; border-radius: 16px; box-shadow: 0 3px 15px rgba(80,45,5,0.07);}
        .stats-icon { font-size: 2.1em; color: #a87f51;}
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .badge-status { font-size: 1em; }
        .quick-link { background: #fff; border-radius: 12px; border: 1.5px solid #e4d5c5; padding: 18px 0; text-align: center; font-weight: 600; color: #a87f51; text-decoration: none; display: block; transition: all 0.14s;}
        .quick-link:hover { background: #e6dbcf; color: #64421d;}
        .quick-link i { font-size: 1.6em; display: block; margin-bottom: 6px;}
        .btn-outline-brown { color: #a87f51; border: 1px solid #a87f51; background: #fff; }
        .btn-outline-brown:hover { background: #e6dbcf; color: #64421d; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-12 col-md-7">
                <div class="dashboard-title mb-2"><i class="bi bi-truck"></i> My Shipments</div>
                <p class="mb-0 text-muted">Track, quote, and manage all your parcel deliveries in one place.</p>
            </div>
            <div class="col-12 col-md-5 d-flex justify-content-md-end align-items-end mt-2 mt-md-0">
                <div class="row w-100 gx-2">
                    <!-- <div class="col-6 col-md-3">
                        <a href="../tracking/track.php" class="quick-link"><i class="bi bi-search"></i>Track Parcel</a>
                    </div>-->
                    <div class="col-6 col-md-3">
                        <a href="../parcels/view_parcel.php" class="quick-link"><i class="bi bi-bell"></i>Notifications</a>
                    </div> 
                    <div class="col-6 col-md-3">
                        <a href="../invoices/view_invoice.php" class="quick-link"><i class="bi bi-receipt"></i>Invoices</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4 g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-box-seam"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#a87f51;"><?= $total ?></div>
                    <div class="text-muted">Total Parcels</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-truck"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#b98540;"><?= $in_transit ?></div>
                    <div class="text-muted">In Transit</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-clock-history"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#c3b081;"><?= $pending ?></div>
                    <div class="text-muted">Pending Pickup</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stats text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-check-circle"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#44a84b;"><?= $delivered ?></div>
                    <div class="text-muted">Delivered</div>
                </div>
            </div>
        </div>

        <!-- Shipments Table -->
         <!-- <h4 class="mb-3" style="color:#a87f51; font-weight:600;">Notifications</h4> -->
        <div class="card mt-3 mb-5 shadow-sm">
            <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-brown mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Tracking No</th>
                                <th>Receiver</th>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Actual Wt (kg)</th>
                                <th>Chargeable Wt (kg)</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $parcels->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['tracking_number']) ?></td>
                                <td><?= htmlspecialchars($p['receiver_name']) ?></td>
                                <td><?= htmlspecialchars($p['dispatch_origin']) ?></td>
                                <td><?= htmlspecialchars($p['delivery_country']) ?></td>
                                <td><?= htmlspecialchars(number_format($p['actual_weight'],2)) ?></td>
                                <td><?= htmlspecialchars(number_format($p['chargeable_weight'],2)) ?></td>
                                <td>
                                    <?php
                                    $badge = 'secondary';
                                    $status = $p['status'];
                                    if($status == 'Delivered') $badge = 'success';
                                    elseif($status == 'Label Created') $badge = 'warning';
                                    elseif($status == 'On the Way') $badge = 'info';
                                    elseif($status == 'Out for Delivery') $badge = 'primary';
                                    elseif($status == 'We Have Your Package') $badge = 'dark';
                                    ?>
                                    <span class="badge bg-<?= $badge ?> badge-status"><?= htmlspecialchars($status) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($p['delivered_at'] ?? $p['created_at'])) ?></td>
                                <td>
                                    <a href="../tracking/track.php?track=<?= urlencode($p['tracking_number']) ?>" class="btn btn-sm btn-outline-brown" title="Track">
                                        <i class="bi bi-search"></i>
                                    </a>
                                    <a href="../invoices/generate_invoice.php?parcel_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-brown" title="Invoice" target="_blank">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
