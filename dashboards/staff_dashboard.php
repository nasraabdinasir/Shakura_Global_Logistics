<?php
require_once '../includes/auth.php';
requireRole(['staff']);
include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Shipment status counts for dashboard cards
$total = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE assigned_staff='$user_id'")->fetch_assoc()['c'];
$delivered = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE assigned_staff='$user_id' AND status='Delivered'")->fetch_assoc()['c'];
$transit = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE assigned_staff='$user_id' AND status IN('On the Way','Out for Delivery','We Have Your Package')")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) as c FROM parcels WHERE assigned_staff='$user_id' AND status='Label Created'")->fetch_assoc()['c'];

$parcels = $conn->query("SELECT * FROM parcels WHERE assigned_staff='$user_id' ORDER BY created_at DESC LIMIT 10");

// Recent activity/timeline (from parcel_logs)
$logs = $conn->query(
    "SELECT pl.*, p.tracking_number, p.dispatch_origin, p.delivery_country FROM parcel_logs pl 
     JOIN parcels p ON pl.parcel_id = p.id 
     WHERE p.assigned_staff='$user_id'
     ORDER BY log_time DESC LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Staff Dashboard</title>
    <style>
        body { background: #f5eee6; }
        .dashboard-title { color: #a87f51; font-size: 2rem; font-weight: 700; }
        .card-stat { background: #fffaf3; border-radius: 15px; box-shadow: 0 3px 13px rgba(80,45,5,0.07);}
        .stats-icon { font-size: 2em; color: #a87f51;}
        .badge-status { font-size: 1em; }
        .timeline {
            border-left: 3px solid #a87f51;
            padding-left: 20px;
            margin-left: 12px;
        }
        .timeline-entry {
            margin-bottom: 1.2em;
            position: relative;
        }
        .timeline-entry:before {
            content: '';
            position: absolute;
            left: -28px;
            top: 3px;
            width: 16px; height: 16px;
            background: #a87f51;
            border-radius: 50%;
            border: 3px solid #fffaf3;
        }
        .timeline-entry .small { color: #a87f51; }
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .btn-outline-brown { color: #a87f51; border: 1px solid #a87f51; background: #fff;}
        .btn-outline-brown:hover { background: #e6dbcf; color: #64421d;}
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <div class="dashboard-title mb-4"><i class="bi bi-person-workspace"></i> Staff Dashboard</div>
        <div class="row mb-4 g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-box"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#a87f51;"><?= $total ?></div>
                    <div class="text-muted">Assigned Parcels</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-truck"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#b98540;"><?= $transit ?></div>
                    <div class="text-muted">In Transit</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-hourglass-split"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#c3b081;"><?= $pending ?></div>
                    <div class="text-muted">Label Created</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-check-circle"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#44a84b;"><?= $delivered ?></div>
                    <div class="text-muted">Delivered</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Parcel Table -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-brown text-white"><b><i class="bi bi-box-seam"></i> Assigned Parcels</b></div>
                    <div class="card-body p-0">
                        <table class="table table-brown mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Tracking No</th>
                                    <th>Receiver</th>
                                    <th>Dispatch Origin</th>
                                    <th>Delivery Country</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($p = $parcels->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['tracking_number']) ?></td>
                                    <td><?= htmlspecialchars($p['receiver_name']) ?></td>
                                    <td><?= htmlspecialchars($p['dispatch_origin']) ?></td>
                                    <td><?= htmlspecialchars($p['delivery_country']) ?></td>
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
                                    <td>
                                        <a href="../parcels/edit_parcel.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-brown">Update</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Activity Timeline -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-brown text-white"><b><i class="bi bi-clock-history"></i> Recent Activity</b></div>
                    <div class="card-body timeline">
                        <?php while($l = $logs->fetch_assoc()): ?>
                            <div class="timeline-entry">
                                <div><span class="small"><?= date('d M H:i', strtotime($l['log_time'])) ?></span></div>
                                <div>
                                    <span class="fw-bold text-brown"><?= htmlspecialchars($l['tracking_number']) ?></span>
                                    <span class="ms-1"><?= htmlspecialchars($l['status']) ?></span>
                                    <span class="ms-1">(
                                        <?= htmlspecialchars($l['dispatch_origin']) ?> → <?= htmlspecialchars($l['delivery_country']) ?>
                                    )</span>
                                </div>
                                <div class="text-muted"><?= htmlspecialchars($l['location']) ?> 
                                    <?php if(!empty($l['remarks'])): ?>
                                    | <small><?= htmlspecialchars($l['remarks']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <!-- Quick Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-brown text-white"><b><i class="bi bi-lightning-charge"></i> Quick Actions</b></div>
                    <div class="card-body text-center">
                        <a href="../rfid/scan.php" class="btn btn-brown mb-2 w-100"><i class="bi bi-broadcast"></i> RFID Scan</a>
                        <a href="../parcels/view_parcel.php" class="btn btn-outline-brown w-100"><i class="bi bi-box-seam"></i> All Parcels</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>
