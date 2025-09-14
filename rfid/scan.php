<?php
require_once '../includes/auth.php';
requireRole(['staff','admin']);
include '../includes/db.php';

// Statuses in order
$statuses = [
    'Label Created',
    'We Have Your Package',
    'On the Way',
    'Out for Delivery',
    'Delivered'
];
// Fetch parcels assigned to this staff/admin
$parcels = $conn->query(
    "SELECT id, tracking_number, dispatch_origin, delivery_country, status 
     FROM parcels WHERE assigned_staff=".$_SESSION['user_id']
);
$parcelStatusMap = [];
while($row = $parcels->fetch_assoc()) {
    $currentStatusIndex = array_search($row['status'], $statuses);
    $nextStatus = ($currentStatusIndex !== false && $currentStatusIndex < count($statuses)-1)
        ? $statuses[$currentStatusIndex+1] : null;
    $parcelStatusMap[$row['id']] = [
        'tracking_number'   => $row['tracking_number'],
        'dispatch_origin'   => $row['dispatch_origin'],
        'delivery_country'  => $row['delivery_country'],
        'next_status'       => $nextStatus
    ];
}
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['parcel_id'], $_POST['status'], $_POST['location'])) {
    $parcel_id = intval($_POST['parcel_id']);
    $status = $_POST['status'];
    $location = $_POST['location'];
    $gps_lat = $_POST['gps_lat'] ?? '';
    $gps_long = $_POST['gps_long'] ?? '';
    $remarks = "RFID Checkpoint";
    if ($gps_lat && $gps_long) {
        $remarks .= " (GPS: $gps_lat,$gps_long)";
    }

    // Update parcel status and insert log
    $conn->query("UPDATE parcels SET status='$status' WHERE id=$parcel_id");
    $conn->query("INSERT INTO parcel_logs (parcel_id, status, location, remarks, log_type) VALUES ('$parcel_id','$status','$location','$remarks','rfid')");
    header("Location: ".$_SERVER['PHP_SELF']."?rfid=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>RFID Scan</title>
    <style>
        .card { border-radius: 14px; box-shadow: 0 3px 14px rgba(168,127,81,0.07);}
        .btn-brown { background: #a87f51; color: #fff; border: 1.5px solid #8a5c22; }
        .btn-brown:hover { background: #86561e; color: #fff; }
        label { color: #a87f51; font-weight: 500; }
    </style>
    <script>
    var parcelStatusMap = <?= json_encode($parcelStatusMap) ?>;
    function updateStatusDropdown() {
        var parcelId = document.getElementById('parcel_id').value;
        var statusSelect = document.getElementById('status');
        statusSelect.innerHTML = "";
        if (parcelStatusMap[parcelId] && parcelStatusMap[parcelId]['next_status']) {
            var opt = document.createElement("option");
            opt.value = parcelStatusMap[parcelId]['next_status'];
            opt.text = parcelStatusMap[parcelId]['next_status'];
            statusSelect.appendChild(opt);
            statusSelect.disabled = false;
        } else {
            var opt = document.createElement("option");
            opt.value = "";
            opt.text = "No further status";
            statusSelect.appendChild(opt);
            statusSelect.disabled = true;
        }
    }
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('gps_lat').value = position.coords.latitude;
                document.getElementById('gps_long').value = position.coords.longitude;
            });
        }
    }
    </script>
</head>
<body onload="getLocation();updateStatusDropdown();">
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container p-4">
        <h3 style="color:#a87f51;">Simulated RFID Checkpoint</h3>
        <?php if(isset($_GET['rfid'])): ?>
            <div class="alert alert-success mb-3">Parcel status updated successfully!</div>
        <?php endif; ?>
        <form method="post" class="card p-3 mt-2" style="max-width:430px;">
            <div class="mb-3">
                <label for="parcel_id">Parcel</label>
                <select name="parcel_id" class="form-control" id="parcel_id" required onchange="updateStatusDropdown()">
                    <?php foreach($parcelStatusMap as $id => $arr): ?>
                        <option value="<?= $id ?>">
                            <?= htmlspecialchars($arr['tracking_number']) ?>
                            &nbsp; | &nbsp; <?= htmlspecialchars($arr['dispatch_origin']) ?> → <?= htmlspecialchars($arr['delivery_country']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="status">Next Status</label>
                <select name="status" class="form-control" id="status" required></select>
            </div>
            <div class="mb-3">
                <label for="location">Current Location</label>
                <input type="text" name="location" class="form-control" id="location"  placeholder="Checkpoint, Airport, City...">
            </div>
            <input type="hidden" name="gps_lat" id="gps_lat">
            <input type="hidden" name="gps_long" id="gps_long">
            <button class="btn btn-brown mt-2 w-100"><i class="bi bi-broadcast"></i> Simulate Scan</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        updateStatusDropdown();
    });
</script>
</body>
</html>
