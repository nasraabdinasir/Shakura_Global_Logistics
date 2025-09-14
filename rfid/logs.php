<?php
require_once '../includes/auth.php';
requireRole(['admin','staff']);
include '../includes/db.php';
include '../includes/sidebar.php';

// New: Also fetch route info (dispatch_origin, delivery_country) for context
$logs = $conn->query("
    SELECT pl.*, 
        p.tracking_number, 
        p.dispatch_origin, 
        p.delivery_country
    FROM parcel_logs pl 
    JOIN parcels p ON pl.parcel_id = p.id 
    WHERE pl.log_type = 'rfid' 
    ORDER BY pl.log_time DESC LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>RFID Logs</title>
    <style>
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .badge-status { font-size: 1em; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <h3 style="color:#a87f51;">RFID Scan Logs</h3>
        <div class="table-responsive">
        <table class="table table-brown table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tracking No</th>
                    <th>Route</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>GPS (Lat, Long)</th>
                    <th>Time</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php $rownum=1; while($l = $logs->fetch_assoc()): 
                    // Try to extract GPS from remarks, else leave blank
                    $gps_lat = ''; $gps_long = '';
                    if (preg_match('/GPS:\s*([\-0-9.]+),\s*([\-0-9.]+)/', $l['remarks'], $matches)) {
                        $gps_lat = $matches[1];
                        $gps_long = $matches[2];
                    }
                ?>
                <tr>
                    <td><?= $rownum++ ?></td>
                    <td><?= htmlspecialchars($l['tracking_number']) ?></td>
                    <td><?= htmlspecialchars($l['dispatch_origin']) ?> → <?= htmlspecialchars($l['delivery_country']) ?></td>
                    <td>
                        <?php
                            $badge = 'secondary';
                            $status = $l['status'];
                            if ($status == 'Delivered') $badge = 'success';
                            elseif ($status == 'Label Created') $badge = 'warning';
                            elseif ($status == 'On the Way') $badge = 'info';
                            elseif ($status == 'Out for Delivery') $badge = 'primary';
                            elseif ($status == 'We Have Your Package') $badge = 'dark';
                        ?>
                        <span class="badge bg-<?= $badge ?> badge-status"><?= htmlspecialchars($status) ?></span>
                    </td>
                    <td><?= htmlspecialchars($l['location']) ?></td>
                    <td>
                        <?php if($gps_lat && $gps_long): ?>
                            <?= htmlspecialchars($gps_lat) ?>, <?= htmlspecialchars($gps_long) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($l['log_time'])) ?></td>
                    <td>
                        <?php 
                        // Show remarks without GPS string (for cleaner look)
                        $remark = preg_replace('/\(GPS:[^)]+\)/', '', $l['remarks']);
                        echo htmlspecialchars(trim($remark));
                        ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>
