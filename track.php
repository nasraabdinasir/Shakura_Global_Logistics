<?php 
require_once 'includes/db.php';

$status = '';
$p = null;
$logs = [];
if (isset($_POST['track'])) {
    $tracking = trim($_POST['tracking_number']);
    $p = $conn->query("SELECT * FROM parcels WHERE tracking_number='$tracking'")->fetch_assoc();
    if ($p) {
        $logResult = $conn->query("SELECT * FROM parcel_logs WHERE parcel_id=".$p['id']." ORDER BY log_time ASC");
        $logs = [];
        if ($logResult && $logResult->num_rows > 0) {
            while($row = $logResult->fetch_assoc()) $logs[] = $row;
        }
        $status = "found";
    } else {
        $status = "notfound";
    }
}

// Status order - update as needed to match your workflow
$parcel_steps = [
    'Label Created',
    'We Have Your Package',
    'On the Way',
    'Out for Delivery',
    'Delivered'
];

// Map: status => log info if completed (to allow info per step)
$completed_map = [];
$step_to_index = [];
foreach ($parcel_steps as $i => $step) {
    $step_to_index[$step] = $i;
}
if (!empty($logs)) {
    foreach ($logs as $log) {
        $completed_map[$log['status']] = $log;
    }
}

// Find the latest completed step index
$latest_completed_idx = -1;
foreach ($parcel_steps as $idx => $step) {
    if (isset($completed_map[$step])) {
        $latest_completed_idx = $idx;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>Track Parcel</title>
    <style>
    .tracking-card {
        max-width: 550px;
        margin: 0 auto;
        border: 1.5px solid #e0e0e0;
        border-radius: 14px;
        box-shadow: 0 2px 18px rgba(80,70,30,0.06);
        background: #fff;
        padding: 30px 28px 20px 28px;
    }
    .tracking-header {
        border-bottom: 1.5px solid #e5e5e5;
        margin-bottom: 18px;
        padding-bottom: 12px;
    }
    .tracking-header h4 {
        color: #008060;
        font-weight: bold;
        font-size: 1.21rem;
        margin-bottom: 2px;
    }
    .tracking-header .delivered {
        color: #008060;
        font-weight: 700;
        font-size: 1.17rem;
        margin-bottom: 3px;
    }
    .tracking-summary {
        display: flex; justify-content: space-between;
        margin-bottom: 13px;
        font-size: 0.98em;
    }
    .timeline {
        position: relative;
        padding-left: 32px;
        margin-left: 7px;
        margin-top: 7px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 10px;
        left: 11px;
        width: 3px;
        height: calc(100% - 25px);
        background: #d0d0d0;
        z-index: 0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 24px;
        z-index: 1;
        min-height: 32px;
    }
    .timeline-item:last-child {
        margin-bottom: 2px;
    }
    .timeline-marker {
        position: absolute;
        left: -5px;
        top: 4px;
        width: 21px;
        height: 21px;
        border-radius: 50%;
        background: #fff;
        border: 2.5px solid #008060;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 3;
        color: #fff;
    }
    .timeline-marker.completed {
        background: #008060;
        border-color: #008060;
        color: #fff;
    }
    .timeline-marker.current {
        background: #fff;
        border-color: #008060;
        color: #008060;
        box-shadow: 0 0 0 2.5px #c6f3df;
    }
    .timeline-marker.delivered {
        background: #008060;
        border-color: #008060;
        color: #fff;
        box-shadow: 0 0 0 3px #f3faf7;
    }
    .timeline-marker.inactive {
        border-color: #b9b9b9;
        color: #b9b9b9;
        background: #fff;
    }
    .timeline-content {
        margin-left: 30px;
        padding-top: 1px;
    }
    .timeline-title {
        font-weight: 600;
        color: #232323;
        font-size: 1.04em;
    }
    .timeline-date {
        color: #676767;
        font-size: 0.99em;
        margin-top: 1px;
    }
    .timeline-location {
        color: #7d7d7d;
        font-size: 0.98em;
    }
    .timeline-remarks {
        color: #2e5a2e;
        font-size: 0.97em;
        margin-top: 1px;
    }
    </style>
</head>
<body>
<a href="index.html" class="btn" style="position:fixed;top:24px;left:24px;z-index:1000;background:#c49b53;color:#fff;font-weight:600;border-radius:24px;padding:10px 22px;border:none;box-shadow:0 2px 8px rgba(180,140,60,0.09);">
        <i class="bi bi-arrow-left"></i> Back to Website
    </a>
<div class="container mt-5">
   
 <div class="container mt-5">
    <h2 class="text-center mb-4" style="color:#a87f51;font-weight:800;letter-spacing:1px;">
        <i class="bi bi-search"></i> Track Your Parcel
    </h2>
    <form method="post" class="track-form-elegant mb-4 p-4 mx-auto" style="max-width: 470px;">
        <div class="mb-3">
            <label for="tracking_number" class="form-label" style="color:#a87f51;font-weight:600;">
                Tracking Number
            </label>
            <div class="input-group input-group-lg">
                <input type="text"
                       id="tracking_number"
                       name="tracking_number"
                       class="form-control"
                       placeholder="e.g. TRKABC123456"
                       required
                       style="border-top-left-radius:18px;border-bottom-left-radius:18px;background:#fffdf7;border-color:#e3cda5;font-size:1.17em;">
                <button class="btn btn-brown"
                        name="track"
                        style="border-top-right-radius:18px;border-bottom-right-radius:18px;background:#a87f51;font-weight:700;font-size:1.15em;">
                    <i class="bi bi-arrow-right-circle-fill me-1"></i> Track
                </button>
            </div>
        </div>
        <div class="text-muted mt-2" style="font-size:0.96em;">
            Enter your shipment tracking number above to see the latest delivery updates.
        </div>
    </form>


    <?php if(isset($_POST['track'])): ?>
        <?php if($status=='found'): ?>
            <div class="tracking-card mb-5">
                <div class="tracking-header">
                    <div style="font-size: 1.1em;color:#606060;">
                        Your shipment<br>
                        <b><?= htmlspecialchars($p['tracking_number']) ?></b>
                    </div>
                    <?php
                    // Get delivery info (latest log with Delivered)
                    $delivered_log = null;
                    foreach(array_reverse($logs) as $log) {
                        if(strtolower($log['status']) == 'delivered') { $delivered_log = $log; break; }
                    }
                    ?>
                    <?php if($delivered_log): ?>
                        <div class="delivered">
                            <i class="bi bi-check-circle-fill"></i>
                            Delivered On <span style="font-weight:700;"><?= date("l, M d", strtotime($delivered_log['log_time'])) ?> at <?= date("h:i A", strtotime($delivered_log['log_time'])) ?></span> at <?= htmlspecialchars($delivered_log['location']) ?>
                        </div>
                    <?php else: ?>
                        <h4>
                            <i class="bi bi-truck"></i> In Transit
                        </h4>
                    <?php endif; ?>
                    <div class="tracking-summary">
                        <div>
                            <strong>Destination</strong><br>
                            <?= htmlspecialchars($p['delivery_country']) ?>
                        </div>
                        <div>
                            <strong>Origin</strong><br>
                            <?= htmlspecialchars($p['dispatch_origin']) ?>
                        </div>
                    </div>
                </div>
                <div class="timeline">
                    <?php
                    foreach($parcel_steps as $idx => $step):
                        $completed = isset($completed_map[$step]);
                        // Determine marker class:
                        // - completed = all before current
                        // - current = latest completed (or only completed)
                        // - delivered = delivered completed
                        // - inactive = future
                        $markerClass = 'timeline-marker';
                        if ($completed) {
                            if ($idx < $latest_completed_idx) {
                                $markerClass .= ' completed';
                            } else if ($idx == $latest_completed_idx) {
                                $markerClass .= (strtolower($step) == 'delivered') ? ' delivered' : ' current';
                            }
                        } else {
                            $markerClass .= ' inactive';
                        }
                    ?>
                        <div class="timeline-item">
                            <div class="<?= $markerClass ?>">
                                <?php if($completed && $idx <= $latest_completed_idx): ?>
                                    <i class="bi bi-check-circle-fill" style="font-size:1.15em"></i>
                                <?php else: ?>
                                    <i class="bi bi-circle" style="font-size:1.1em"></i>
                                <?php endif; ?>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title"><?= htmlspecialchars($step) ?></div>
                                <?php if($completed && isset($completed_map[$step])): ?>
                                    <div class="timeline-date"><?= htmlspecialchars($completed_map[$step]['location'] ?? '-') ?>
                                    <span style="color:#888;font-size:0.97em;"> | <?= date("m/d/Y, h:i A", strtotime($completed_map[$step]['log_time'])) ?></span></div>
                                    <?php if(!empty($completed_map[$step]['remarks'])): ?>
                                        <div class="timeline-remarks"><?= htmlspecialchars($completed_map[$step]['remarks']) ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif($status=='notfound'): ?>
            <div class="alert alert-danger">Tracking number not found!</div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
