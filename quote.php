<?php
require_once 'includes/db.php';

// Fetch origins and countries from pricing table
$origins_res = $conn->query("SELECT DISTINCT origin FROM pricing ORDER BY origin");
$delivery_countries_res = $conn->query("SELECT DISTINCT country FROM pricing ORDER BY country");
$origins = [];
$delivery_countries = [];
while ($row = $origins_res->fetch_assoc()) $origins[] = $row['origin'];
while ($row = $delivery_countries_res->fetch_assoc()) $delivery_countries[] = $row['country'];

// Prepare JS price table from DB (origin -> country -> price)
$jsPriceMatrix = [];
$pq = $conn->query("SELECT * FROM pricing");
while ($pr = $pq->fetch_assoc()) {
    $jsPriceMatrix[$pr['origin']][$pr['country']] = $pr['price_per_kg'];
}

$quote_result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dispatch_origin = $_POST['dispatch_origin'] ?? '';
    $delivery_country = $_POST['delivery_country'] ?? '';
    $actual_weight = floatval($_POST['actual_weight'] ?? 0);
    $length_cm = floatval($_POST['length_cm'] ?? 0);
    $width_cm = floatval($_POST['width_cm'] ?? 0);
    $height_cm = floatval($_POST['height_cm'] ?? 0);
    $fragile = $_POST['fragile'] ?? 'No';

    // Dynamic price lookup
    $price_row = $conn->query("SELECT price_per_kg FROM pricing WHERE origin='$dispatch_origin' AND country='$delivery_country'")->fetch_assoc();
    $price_per_kg = $price_row ? $price_row['price_per_kg'] : 0;

    // Calculate
    $volumetric_weight = ($length_cm * $width_cm * $height_cm) / 6000;
    $chargeable_weight = max($actual_weight, $volumetric_weight);
    $price = $chargeable_weight * $price_per_kg;
    if ($fragile === 'Yes') $price += 100;

    $quote_result = [
        'dispatch_origin' => $dispatch_origin,
        'delivery_country' => $delivery_country,
        'actual_weight' => $actual_weight,
        'length_cm' => $length_cm,
        'width_cm' => $width_cm,
        'height_cm' => $height_cm,
        'volumetric_weight' => $volumetric_weight,
        'chargeable_weight' => $chargeable_weight,
        'fragile' => $fragile,
        'price_per_kg' => $price_per_kg,
        'price' => $price
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Get International Shipping Quote</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f5eee6; }
        .card-quote { max-width: 540px; margin: 40px auto 0 auto; border-radius: 13px; border: 1.5px solid #e4d5c5; box-shadow: 0 3px 18px rgba(120,80,30,0.08); background: #fff; padding: 30px 28px 28px 28px;}
        .quote-label { color: #a87f51; font-weight: 500;}
        .btn-brown { background: #a87f51; color: #fff; border: 1.5px solid #8a5c22;}
        .btn-brown:hover { background: #86561e; color: #fff;}
        .quote-result-card { border-left: 6px solid #a87f51; background: #f7f2ea; margin-top: 30px;}
        .quote-title { font-size: 2.0em; font-weight: 700; color: #a87f51;}
        .form-icon { color: #b09b7a; margin-right: 7px; font-size: 1.2em;}
        .preview-line { font-size:1.04em;color:#aa7f31;margin-top:7px;}
    </style>
    <script>
    const priceMatrix = <?php echo json_encode($jsPriceMatrix); ?>;
    function showPricePerKg() {
        let o = document.getElementById('dispatch_origin').value;
        let d = document.getElementById('delivery_country').value;
        let priceDisplay = document.getElementById('price_per_kg');
        let hidden = document.getElementById('price_kg_hidden');
        if(o && d && priceMatrix[o] && priceMatrix[o][d]) {
            priceDisplay.innerHTML = '$' + priceMatrix[o][d] + ' per kg';
            hidden.value = priceMatrix[o][d];
        } else {
            priceDisplay.innerHTML = '';
            hidden.value = '';
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
    <a href="index.html" class="btn" style="position:fixed;top:24px;left:24px;z-index:1000;background:#c49b53;color:#fff;font-weight:600;border-radius:24px;padding:10px 22px;border:none;box-shadow:0 2px 8px rgba(180,140,60,0.09);">
        <i class="bi bi-arrow-left"></i> Back to Website
    </a>
<div class="container">
    <div class="card-quote">
        <div class="quote-title mb-3"><i class="bi bi-calculator"></i> Get your Shipping Quotation here</div>
        <form method="post" autocomplete="off">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6 mb-2">
                    <label class="quote-label" for="dispatch_origin"><i class="bi bi-flag"></i> Dispatch Origin *</label>
                    <select name="dispatch_origin" id="dispatch_origin" class="form-select" required onchange="showPricePerKg()">
                        <option value="">Select Origin...</option>
                        <?php foreach($origins as $o): ?>
                        <option value="<?= $o ?>" <?= (isset($_POST['dispatch_origin']) && $_POST['dispatch_origin']==$o)?'selected':'' ?>><?= $o ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <label class="quote-label" for="delivery_country"><i class="bi bi-geo"></i> Delivery Country *</label>
                    <select name="delivery_country" id="delivery_country" class="form-select" required onchange="showPricePerKg()">
                        <option value="">Select Country...</option>
                        <?php foreach($delivery_countries as $c): ?>
                        <option value="<?= $c ?>" <?= (isset($_POST['delivery_country']) && $_POST['delivery_country']==$c)?'selected':'' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <label class="quote-label" for="actual_weight"><i class="bi bi-box"></i> Actual Weight (kg) *</label>
                    <input type="number" name="actual_weight" id="actual_weight" class="form-control" min="0.01" step="0.01" required value="<?= isset($_POST['actual_weight']) ? htmlspecialchars($_POST['actual_weight']) : '' ?>" onchange="estimateShipping()" oninput="estimateShipping()">
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label class="quote-label" for="length_cm"><i class="bi bi-rulers"></i> L (cm)</label>
                    <input type="number" name="length_cm" id="length_cm" class="form-control" min="0.01" step="0.01" required value="<?= isset($_POST['length_cm']) ? htmlspecialchars($_POST['length_cm']) : '' ?>" onchange="estimateShipping()" oninput="estimateShipping()">
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label class="quote-label" for="width_cm"><i class="bi bi-rulers"></i> W (cm)</label>
                    <input type="number" name="width_cm" id="width_cm" class="form-control" min="0.01" step="0.01" required value="<?= isset($_POST['width_cm']) ? htmlspecialchars($_POST['width_cm']) : '' ?>" onchange="estimateShipping()" oninput="estimateShipping()">
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label class="quote-label" for="height_cm"><i class="bi bi-rulers"></i> H (cm)</label>
                    <input type="number" name="height_cm" id="height_cm" class="form-control" min="0.01" step="0.01" required value="<?= isset($_POST['height_cm']) ? htmlspecialchars($_POST['height_cm']) : '' ?>" onchange="estimateShipping()" oninput="estimateShipping()">
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label class="quote-label" for="fragile"><i class="bi bi-exclamation-diamond"></i> Fragile?</label>
                    <select name="fragile" id="fragile" class="form-select" onchange="estimateShipping()">
                        <option value="No" <?= (isset($_POST['fragile']) && $_POST['fragile']=='No') ? 'selected' : '' ?>>No</option>
                        <option value="Yes" <?= (isset($_POST['fragile']) && $_POST['fragile']=='Yes') ? 'selected' : '' ?>>Yes (+$100)</option>
                    </select>
                </div>
            </div>
            <input type="hidden" id="price_kg_hidden" value="">
            <div class="row mt-2">
                <div class="col-12 preview-line">
                    <span><b>Price per kg:</b> <span id="price_per_kg"><?= isset($_POST['dispatch_origin'], $_POST['delivery_country']) && $_POST['dispatch_origin'] && $_POST['delivery_country'] && isset($jsPriceMatrix[$_POST['dispatch_origin']][$_POST['delivery_country']]) ? '$'.$jsPriceMatrix[$_POST['dispatch_origin']][$_POST['delivery_country']].' per kg' : '' ?></span></span>
                    <span style="margin-left:16px;"><b>Volumetric Weight:</b> <span id="vol_weight_show">--</span> kg</span>
                    <span style="margin-left:16px;"><b>Chargeable Weight:</b> <span id="chargeable_show">--</span> kg</span>
                    <span style="margin-left:16px;"><b>Estimated Shipping:</b> <span id="estimate_price">--</span></span>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-brown px-4" type="submit"><i class="bi bi-search"></i> Get Quote</button>
            </div>
        </form>

        <?php if($quote_result): ?>
        <div class="card quote-result-card p-4 mt-4">
            <div class="d-flex align-items-center mb-2">
            <i class="bi bi-receipt-cutoff" style="font-size:2em;color:#a87f51;margin-right:10px;"></i>
            <span style="font-size:1.35em;font-weight:600;color:#a87f51;">Your Shipping Estimate</span>
            </div>
            <div class="row mb-2">
            <div class="col-sm-6"><span class="quote-label">Dispatch Origin:</span> <?= htmlspecialchars($quote_result['dispatch_origin']) ?></div>
            <div class="col-sm-6"><span class="quote-label">Delivery Country:</span> <?= htmlspecialchars($quote_result['delivery_country']) ?></div>
            </div>
            <div class="row mb-2">
            <div class="col-sm-4"><span class="quote-label">Actual Weight:</span> <?= htmlspecialchars($quote_result['actual_weight']) ?> kg</div>
            <div class="col-sm-4"><span class="quote-label">Dimensions:</span> <?= htmlspecialchars($quote_result['length_cm']) ?> × <?= htmlspecialchars($quote_result['width_cm']) ?> × <?= htmlspecialchars($quote_result['height_cm']) ?> cm</div>
            <div class="col-sm-4"><span class="quote-label">Fragile:</span> <?= $quote_result['fragile']=='Yes' ? '<span class="text-danger">Yes</span>' : 'No' ?></div>
            </div>
            <div class="row mb-2">
            <div class="col-sm-6"><span class="quote-label">Volumetric Weight:</span> <?= number_format($quote_result['volumetric_weight'],2) ?> kg</div>
            <div class="col-sm-6"><span class="quote-label">Chargeable Weight:</span> <?= number_format($quote_result['chargeable_weight'],2) ?> kg</div>
            </div>
            <div class="mt-3" style="font-size:1.4em;">
            <strong>Estimated Shipping:</strong>
            <span style="color:#a87f51">$<?= number_format($quote_result['price'],2) ?></span>
            </div>
            <hr>
            <div class="text-muted" style="font-size:0.97em;">
            <i class="bi bi-info-circle"></i> <b>Note:</b> This is an instant estimate. Actual rates may vary based on real shipment details and logistics.
            </div>
            <div class="d-flex justify-content-end mt-4">
            <a href="auth/login.php" class="btn btn-brown px-4"><i class="bi bi-plus-circle"></i> Add Parcel</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
window.onload = function() {
    showPricePerKg();
    estimateShipping();
};
</script>
</body>
</html>
