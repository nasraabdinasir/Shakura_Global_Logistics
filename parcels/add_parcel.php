<?php
require_once '../includes/auth.php';
include '../includes/db.php';

// Get current user info and role
$current_user_id = $_SESSION['user_id'];
$current_user = $conn->query("SELECT * FROM users WHERE id=$current_user_id")->fetch_assoc();
$user_role = $current_user['role'];

// Dropdowns
$users = $conn->query("SELECT id, full_name FROM users WHERE role='customer'");
$staffs = $conn->query("SELECT id, full_name FROM users WHERE role='staff'");

// Fetch origins and delivery countries from pricing table so only available combos show
$origins_res = $conn->query("SELECT DISTINCT origin FROM pricing ORDER BY origin");
$delivery_countries_res = $conn->query("SELECT DISTINCT country FROM pricing ORDER BY country");
$origins = [];
$delivery_countries = [];
while ($row = $origins_res->fetch_assoc()) $origins[] = $row['origin'];
while ($row = $delivery_countries_res->fetch_assoc()) $delivery_countries[] = $row['country'];

// Prepare JS price table (origin->country->price)
$jsPriceMatrix = [];
$pq = $conn->query("SELECT * FROM pricing");
while ($pr = $pq->fetch_assoc()) {
    $jsPriceMatrix[$pr['origin']][$pr['country']] = $pr['price_per_kg'];
}

// On submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tracking_number = uniqid('TRK');
    // For customers, sender_id is fixed to their ID
    if ($user_role === 'customer') {
        $sender_id = $current_user_id;
        $assigned_staff = NULL; // Staff assignment left blank
    } else {
        $sender_id = $_POST['sender_id'];
        $assigned_staff = $_POST['assigned_staff'];
    }
    $receiver_name = $_POST['receiver_name'];
    $receiver_phone = $_POST['receiver_phone'];
    $dispatch_origin = $_POST['dispatch_origin'];
    $delivery_country = $_POST['delivery_country'];
    $actual_weight = floatval($_POST['actual_weight']);
    $length_cm = floatval($_POST['length_cm']);
    $width_cm = floatval($_POST['width_cm']);
    $height_cm = floatval($_POST['height_cm']);
    $fragile = ($_POST['fragile'] === 'Yes') ? 1 : 0;

    // Dynamic price-per-kg lookup
    $price_row = $conn->query("SELECT price_per_kg FROM pricing WHERE origin='$dispatch_origin' AND country='$delivery_country'")->fetch_assoc();
    $price_per_kg = $price_row ? $price_row['price_per_kg'] : 0;

    // Volumetric, chargeable, price-per-kg, price
    $volumetric_weight = ($length_cm * $width_cm * $height_cm) / 6000;
    $chargeable_weight = max($actual_weight, $volumetric_weight);
    $price = $chargeable_weight * $price_per_kg;
    if ($fragile) $price += 100;

    // Save
    $stmt = $conn->prepare(
        "INSERT INTO parcels (
            tracking_number, sender_id, receiver_name, receiver_phone,
            dispatch_origin, delivery_country,
            actual_weight, length_cm, width_cm, height_cm,
            volumetric_weight, chargeable_weight, fragile,
            price_per_kg, price, assigned_staff
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        "sissssddddddsdii",
        $tracking_number,
        $sender_id,
        $receiver_name,
        $receiver_phone,
        $dispatch_origin,
        $delivery_country,
        $actual_weight,
        $length_cm,
        $width_cm,
        $height_cm,
        $volumetric_weight,
        $chargeable_weight,
        $fragile,
        $price_per_kg,
        $price,
        $assigned_staff
    );
    $stmt->execute();
    $stmt->close();

    $added_summary = [
        'Tracking #' => $tracking_number,
        'Chargeable Weight (kg)' => number_format($chargeable_weight,2),
        'Shipping Price ($)' => number_format($price,2)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Parcel</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f1e2; }
        .card-form { border-radius: 16px; box-shadow: 0 4px 22px rgba(160, 120, 67, 0.07); background: #fffaf4; }
        .section-title { color: #a87f51; font-weight: 700; font-size: 1.13em; margin-bottom:.3em;}
        .input-label { color: #a87f51; font-weight: 500; }
        .form-control:focus { border-color: #a87f51; box-shadow: 0 0 0 .13rem rgba(168,127,81,.13); }
        .btn-brown { background: #a87f51; color: #fff; border: 1.5px solid #8a5c22; font-weight: 600; }
        .btn-brown:hover { background: #895c23; color: #fff; }
        .row-divider { border-top:1.2px solid #e7dbbe; margin: 18px 0; }
        .result-table td { color:#a87f51; font-size:1.1em;}
    </style>
    <script>
    // Dynamic JS price matrix from backend
    const priceMatrix = <?php echo json_encode($jsPriceMatrix); ?>;
    function showPricePerKg() {
        let o = document.getElementById('dispatch_origin').value;
        let d = document.getElementById('delivery_country').value;
        let display = document.getElementById('price_per_kg');
        if(o && d && priceMatrix[o] && priceMatrix[o][d]) {
            display.innerHTML = '$' + priceMatrix[o][d] + ' per kg';
            document.getElementById('price_kg_hidden').value = priceMatrix[o][d];
        } else {
            display.innerHTML = '';
            document.getElementById('price_kg_hidden').value = '';
        }
        estimateShipping();
    }
    function estimateShipping() {
        let w = parseFloat(document.getElementById('actual_weight').value) || 0;
        let l = parseFloat(document.getElementById('length_cm').value) || 0;
        let wi = parseFloat(document.getElementById('width_cm').value) || 0;
        let h = parseFloat(document.getElementById('height_cm').value) || 0;
        let f = document.getElementById('fragile').value;
        let price_kg = parseFloat(document.getElementById('price_kg_hidden').value) || 0;
        let vol_weight = (l * wi * h) / 6000;
        let chargeable_weight = Math.max(w, vol_weight);
        let total = chargeable_weight * price_kg;
        if (f === 'Yes') total += 100;
        document.getElementById('vol_weight_show').innerText = (vol_weight ? vol_weight.toFixed(2) : '--');
        document.getElementById('chargeable_show').innerText = (chargeable_weight ? chargeable_weight.toFixed(2) : '--');
        document.getElementById('estimate_price').innerText = (chargeable_weight > 0 && price_kg > 0) ? '$' + total.toFixed(2) : '--';
    }
    </script>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container py-4">
        <div class="mx-auto" style="max-width:970px;">
            <h2 class="mb-4 d-flex align-items-center" style="color:#a87f51; font-weight:700;">
                <i class="bi bi-airplane-engines me-2"></i> Add International Parcel
            </h2>
            <?php if(!empty($added_summary)): ?>
                <div class="alert alert-success">
                    <b>Parcel added!</b>
                    <table class="result-table mt-2">
                        <?php foreach($added_summary as $k=>$v): ?>
                            <tr><td><?= htmlspecialchars($k) ?>:</td><td><?= htmlspecialchars($v) ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
            <form method="post" class="card card-form p-4">
                <!-- Sender & Receiver Info -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="section-title"><i class="bi bi-person-badge"></i> Sender (Customer)</label>
                        <?php if ($user_role === 'customer'): ?>
                            <input type="text" readonly class="form-control form-control-lg" value="<?= htmlspecialchars($current_user['full_name']) ?>">
                            <input type="hidden" name="sender_id" value="<?= $current_user_id ?>">
                        <?php else: ?>
                            <select name="sender_id" class="form-control form-control-lg" required>
                                <option value="">Select Customer</option>
                                <?php $users->data_seek(0); while($u = $users->fetch_assoc()): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-person"></i> Receiver Name</label>
                        <input type="text" name="receiver_name" class="form-control form-control-lg" required placeholder="Full name">
                    </div>
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-telephone"></i> Receiver Phone</label>
                        <input type="text" name="receiver_phone" class="form-control form-control-lg" required placeholder="Phone">
                    </div>
                </div>
                <div class="row-divider"></div>
                <!-- Shipment Route -->
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="section-title"><i class="bi bi-flag"></i> Dispatch Origin</label>
                        <select name="dispatch_origin" id="dispatch_origin" class="form-control form-control-lg" required onchange="showPricePerKg()">
                            <option value="">Select Origin</option>
                            <?php foreach($origins as $o): ?>
                                <option value="<?= $o ?>"><?= $o ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="section-title"><i class="bi bi-geo"></i> Delivery Country</label>
                        <select name="delivery_country" id="delivery_country" class="form-control form-control-lg" required onchange="showPricePerKg()">
                            <option value="">Select Country</option>
                            <?php foreach($delivery_countries as $d): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="section-title"><i class="bi bi-cash-coin"></i> Price per kg</div>
                        <span id="price_per_kg" style="font-size:1.15em;color:#c09148;"></span>
                        <input type="hidden" id="price_kg_hidden" name="price_per_kg" value="">
                    </div>
                </div>
                <div class="row-divider"></div>
                <!-- Parcel Details -->
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-capsule"></i> Actual Weight (kg)</label>
                        <input type="number" min="0" step="0.01" name="actual_weight" id="actual_weight" class="form-control form-control-lg" required onchange="estimateShipping()" oninput="estimateShipping()">
                    </div>
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-rulers"></i> Length (cm)</label>
                        <input type="number" min="0" step="0.1" name="length_cm" id="length_cm" class="form-control form-control-lg" required onchange="estimateShipping()" oninput="estimateShipping()">
                    </div>
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-rulers"></i> Width (cm)</label>
                        <input type="number" min="0" step="0.1" name="width_cm" id="width_cm" class="form-control form-control-lg" required onchange="estimateShipping()" oninput="estimateShipping()">
                    </div>
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-rulers"></i> Height (cm)</label>
                        <input type="number" min="0" step="0.1" name="height_cm" id="height_cm" class="form-control form-control-lg" required onchange="estimateShipping()" oninput="estimateShipping()">
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-exclamation-circle"></i> Fragile?</label>
                        <select name="fragile" id="fragile" class="form-control form-control-lg" onchange="estimateShipping()">
                            <option value="No">No</option>
                            <option value="Yes">Yes (+$100)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="section-title"><i class="bi bi-bar-chart"></i> Calculation Preview</label>
                        <div style="font-size:1.04em;">
                            Volumetric Weight: <b id="vol_weight_show">--</b> kg,
                            Chargeable Weight: <b id="chargeable_show">--</b> kg
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="section-title"><i class="bi bi-cash-coin"></i> Estimated Shipping</label>
                        <div class="form-control-plaintext" style="font-size:1.23em;color:#be983a;" id="estimate_price">--</div>
                    </div>
                </div>
                <div class="row-divider"></div>
                <!-- Staff Assign (admin/staff only) -->
                <?php if ($user_role !== 'customer'): ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="section-title"><i class="bi bi-people"></i> Assign Staff</label>
                        <select name="assigned_staff" class="form-control form-control-lg" required>
                            <option value="">Select Staff</option>
                            <?php $staffs->data_seek(0); while($s = $staffs->fetch_assoc()): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>
                <button class="btn btn-brown btn-lg mt-4 px-5 w-100" style="border-radius:10px;">
                    <i class="bi bi-box-arrow-down"></i>
                    <?= $user_role === 'customer' ? 'Send Parcel' : 'Add Parcel' ?>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
