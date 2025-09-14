<?php 
require_once '../includes/auth.php';
include '../includes/db.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// For dropdowns (only for admin/staff)
$staffs = $conn->query("SELECT id, full_name FROM users WHERE role='staff'");

// Status list
$statuses = [
    'Label Created',
    'We Have Your Package',
    'On the Way',
    'Out for Delivery',
    'Delivered'
];

// Fetch available origins/countries from pricing table
$origins_res = $conn->query("SELECT DISTINCT origin FROM pricing ORDER BY origin");
$delivery_countries_res = $conn->query("SELECT DISTINCT country FROM pricing ORDER BY country");
$dispatch_origins = [];
$delivery_countries = [];
while ($row = $origins_res->fetch_assoc()) $dispatch_origins[] = $row['origin'];
while ($row = $delivery_countries_res->fetch_assoc()) $delivery_countries[] = $row['country'];

// Prepare JS price table for live preview
$jsPriceMatrix = [];
$pq = $conn->query("SELECT * FROM pricing");
while ($pr = $pq->fetch_assoc()) {
    $jsPriceMatrix[$pr['origin']][$pr['country']] = $pr['price_per_kg'];
}

// Fetch the parcel
$id = intval($_GET['id'] ?? 0);
$p = $conn->query("SELECT * FROM parcels WHERE id=$id")->fetch_assoc();
if(!$p) die("Parcel not found!");

// Block customers from editing after staff assignment
if ($role == 'customer') {
    if ($p['sender_id'] != $user_id) die('You are not allowed to edit this parcel.');
    if (!empty($p['assigned_staff'])) {
        // Redirect or error message
        header("Location: view_parcel.php?msg=not_editable");
        exit;
    }
}

// Status logic for next steps (for staff/admin)
$currentStatusIndex = array_search($p['status'], $statuses);
$nextStatuses = [];
if ($currentStatusIndex !== false && $currentStatusIndex < count($statuses)-1) {
    $nextStatuses[] = $statuses[$currentStatusIndex+1];
} elseif ($currentStatusIndex === false) {
    $nextStatuses[] = $statuses[0];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_name = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $dispatch_origin = $_POST['dispatch_origin'];
    $delivery_country = $_POST['delivery_country'];
    $actual_weight = floatval($_POST['actual_weight']);
    $length_cm = floatval($_POST['length_cm']);
    $width_cm = floatval($_POST['width_cm']);
    $height_cm = floatval($_POST['height_cm']);
    $fragile = ($_POST['fragile'] === 'Yes' || $_POST['fragile'] == 1) ? 1 : 0;

    // Dynamic price-per-kg lookup
    $price_row = $conn->query("SELECT price_per_kg FROM pricing WHERE origin='$dispatch_origin' AND country='$delivery_country'")->fetch_assoc();
    $price_per_kg = $price_row ? $price_row['price_per_kg'] : 0;

    // Business logic: recalculate weights and price
    $volumetric_weight = ($length_cm * $width_cm * $height_cm) / 6000;
    $chargeable_weight = max($actual_weight, $volumetric_weight);
    $price = $chargeable_weight * $price_per_kg;
    if ($fragile) $price += 100;

    // Only allow these for admin/staff
    if ($role == 'admin' || $role == 'staff') {
        $assigned_staff = $_POST['assigned_staff'];
        $status = $_POST['status'] ?? $p['status'];
    } else {
        $assigned_staff = $p['assigned_staff'];
        $status = $p['status'];
    }

    // Update DB
    $stmt = $conn->prepare("UPDATE parcels 
        SET receiver_name=?, receiver_phone=?, dispatch_origin=?, delivery_country=?, actual_weight=?, length_cm=?, width_cm=?, height_cm=?, volumetric_weight=?, chargeable_weight=?, fragile=?, price_per_kg=?, price=?, assigned_staff=?, status=?
        WHERE id=?");
    $stmt->bind_param(
        'ssssddddddiddssi',
        $receiver_name, $receiver_phone, $dispatch_origin, $delivery_country,
        $actual_weight, $length_cm, $width_cm, $height_cm,
        $volumetric_weight, $chargeable_weight, $fragile, $price_per_kg, $price,
        $assigned_staff, $status, $id
    );
    $stmt->execute();
    $stmt->close();

    // Parcel logs (optional: if status has changed, only admin/staff can do this)
    if (($role == 'admin' || $role == 'staff') && $status != $p['status']) {
        $location = $_POST['location'] ?? '';
        $remarks = $_POST['remarks'] ?? '';
        $conn->query("INSERT INTO parcel_logs (parcel_id, status, location, remarks, log_type) VALUES ('$id','$status','$location','$remarks','manual')");
    }
    header("Location: view_parcel.php?upd=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Parcel</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f1e2; }
        .card-form { border-radius: 18px; box-shadow: 0 6px 28px rgba(168, 127, 81, 0.08); background: #fffaf4; }
        .section-title { color: #a87f51; font-weight: 700; font-size: 1.12em; margin-bottom:.38em;}
        .form-control:focus { border-color: #a87f51; box-shadow: 0 0 0 .14rem rgba(168,127,81,.13); }
        .btn-brown { background: #a87f51; color: #fff; border: 1.5px solid #8a5c22; font-weight: 600; }
        .btn-brown:hover { background: #895c23; color: #fff; }
    </style>
    <script>
    const priceMatrix = <?php echo json_encode($jsPriceMatrix); ?>;
    function updatePreview() {
        let weight = parseFloat(document.getElementById('actual_weight').value) || 0;
        let length = parseFloat(document.getElementById('length_cm').value) || 0;
        let width = parseFloat(document.getElementById('width_cm').value) || 0;
        let height = parseFloat(document.getElementById('height_cm').value) || 0;
        let origin = document.getElementById('dispatch_origin').value;
        let dest = document.getElementById('delivery_country').value;
        let fragile = document.getElementById('fragile').value;
        let priceKg = (priceMatrix[origin] && priceMatrix[origin][dest]) ? priceMatrix[origin][dest] : 0;
        let volWeight = (length * width * height) / 6000;
        let chargeWeight = Math.max(weight, volWeight);
        let price = chargeWeight * priceKg;
        if (fragile === 'Yes' || fragile == 1) price += 100;
        document.getElementById('preview_volumetric').innerText = volWeight ? volWeight.toFixed(2) : '--';
        document.getElementById('preview_chargeable').innerText = chargeWeight ? chargeWeight.toFixed(2) : '--';
        document.getElementById('preview_price').innerText = (chargeWeight && priceKg) ? '$' + price.toFixed(2) : '--';
    }
    </script>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container py-4">
        <div class="mx-auto" style="max-width:950px;">
            <h3 class="mb-4" style="color:#a87f51; font-weight:700;"><i class="bi bi-pencil"></i> Edit Parcel (<?= htmlspecialchars($p['tracking_number']) ?>)</h3>
            <form method="post" class="card card-form p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="section-title">Receiver Name</label>
                        <input type="text" name="receiver_name" class="form-control" required value="<?= htmlspecialchars($p['receiver_name']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="section-title">Receiver Phone</label>
                        <input type="text" name="receiver_phone" class="form-control" required value="<?= htmlspecialchars($p['receiver_phone']) ?>">
                    </div>
                    <?php if ($role == 'admin' || $role == 'staff'): ?>
                    <div class="col-md-4">
                        <label class="section-title">Assign to Staff</label>
                        <select name="assigned_staff" class="form-control" required>
                            <option value="">Select Staff</option>
                            <?php $staffs->data_seek(0); while($s = $staffs->fetch_assoc()): ?>
                                <option value="<?= $s['id'] ?>" <?= $p['assigned_staff'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="section-title">Dispatch Origin</label>
                        <select name="dispatch_origin" id="dispatch_origin" class="form-control" required onchange="updatePreview()">
                            <?php foreach($dispatch_origins as $o): ?>
                                <option value="<?= $o ?>" <?= $p['dispatch_origin'] == $o ? 'selected' : '' ?>><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="section-title">Delivery Country</label>
                        <select name="delivery_country" id="delivery_country" class="form-control" required onchange="updatePreview()">
                            <?php foreach($delivery_countries as $d): ?>
                                <option value="<?= $d ?>" <?= $p['delivery_country'] == $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="section-title">Fragile</label>
                        <select name="fragile" id="fragile" class="form-control" onchange="updatePreview()">
                            <option value="0" <?= $p['fragile']==0?'selected':''; ?>>No</option>
                            <option value="1" <?= $p['fragile']==1?'selected':''; ?>>Yes (+$100)</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-2">
                        <label class="section-title">Actual Weight (kg)</label>
                        <input type="number" min="0" step="0.01" name="actual_weight" id="actual_weight" class="form-control" required value="<?= htmlspecialchars($p['actual_weight']) ?>" onchange="updatePreview()" oninput="updatePreview()">
                    </div>
                    <div class="col-md-2">
                        <label class="section-title">Length (cm)</label>
                        <input type="number" min="0" step="0.1" name="length_cm" id="length_cm" class="form-control" required value="<?= htmlspecialchars($p['length_cm']) ?>" onchange="updatePreview()" oninput="updatePreview()">
                    </div>
                    <div class="col-md-2">
                        <label class="section-title">Width (cm)</label>
                        <input type="number" min="0" step="0.1" name="width_cm" id="width_cm" class="form-control" required value="<?= htmlspecialchars($p['width_cm']) ?>" onchange="updatePreview()" oninput="updatePreview()">
                    </div>
                    <div class="col-md-2">
                        <label class="section-title">Height (cm)</label>
                        <input type="number" min="0" step="0.1" name="height_cm" id="height_cm" class="form-control" required value="<?= htmlspecialchars($p['height_cm']) ?>" onchange="updatePreview()" oninput="updatePreview()">
                    </div>
                    <?php if ($role == 'admin' || $role == 'staff'): ?>
                    <div class="col-md-2">
                        <label class="section-title">Status</label>
                        <select name="status" class="form-control" required>
                            <?php 
                            for($i = $currentStatusIndex; $i < count($statuses); $i++) {
                                $val = $statuses[$i];
                                $selected = ($p['status']==$val) ? 'selected' : '';
                                if($i == $currentStatusIndex || $i == $currentStatusIndex+1)
                                    echo "<option value=\"$val\" $selected>$val</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($role == 'admin' || $role == 'staff'): ?>
                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="section-title">Location (for log if status changed)</label>
                        <input type="text" name="location" class="form-control" placeholder="Current Location (optional)">
                    </div>
                    <div class="col-md-8">
                        <label class="section-title">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Remarks (optional)">
                    </div>
                </div>
                <?php endif; ?>
                <!-- Calculation Preview -->
                <div class="row mt-3">
                    <div class="col-md-4"><b>Volumetric Wt (kg):</b> <span id="preview_volumetric">--</span></div>
                    <div class="col-md-4"><b>Chargeable Wt (kg):</b> <span id="preview_chargeable">--</span></div>
                    <div class="col-md-4"><b>Estimated Shipping:</b> <span id="preview_price">--</span></div>
                </div>
                <button class="btn btn-brown btn-lg mt-4 px-5">Update Parcel</button>
            </form>
        </div>
    </div>
</div>
<script>
    // Trigger preview on load
    window.onload = updatePreview;
</script>
</body>
</html>
