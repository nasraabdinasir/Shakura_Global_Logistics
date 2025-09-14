<?php
require_once '../includes/auth.php';
requireRole(['admin']);
include '../includes/db.php';

// Country/Origin lists (customize as needed)
$origins = ['Europe','US', 'UK', 'China', 'Dubai', 'Netherlands'];
$countries = ['Kenya', 'Uganda', 'Tanzania'];

$successMsg = "";

// Handle add/update pricing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['origin']) && !isset($_POST['delete_id'])) {
    $origin = $conn->real_escape_string($_POST['origin']);
    $country = $conn->real_escape_string($_POST['country']);
    $price_per_kg = floatval($_POST['price_per_kg']);

    $existing = $conn->query("SELECT id FROM pricing WHERE origin='$origin' AND country='$country'")->fetch_assoc();
    if ($existing) {
        $conn->query("UPDATE pricing SET price_per_kg=$price_per_kg WHERE id=" . $existing['id']);
        $successMsg = "Pricing updated successfully!";
    } else {
        $conn->query("INSERT INTO pricing (origin, country, price_per_kg) VALUES ('$origin', '$country', $price_per_kg)");
        $successMsg = "Pricing added successfully!";
    }
}

// Handle delete via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM pricing WHERE id=$id");
    echo json_encode(['success'=>true]);
    exit;
}

// Fetch all pricing for display
$all = $conn->query("SELECT * FROM pricing ORDER BY origin, country");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Set Parcel Pricing</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5efe7; }
        .main-box {
            max-width: 900px;
            margin: 50px auto 0 auto;
            background: #fffaf4;
            border-radius: 22px;
            box-shadow: 0 6px 32px rgba(168,127,81,0.10);
            border: 1.2px solid #eed5b4;
            padding: 32px 30px 26px 30px;
        }
        .section-title {
            color: #a87f51;
            font-weight: bold;
            font-size: 1.34em;
            margin-bottom: 15px;
        }
        .input-label { color: #a87f51; font-weight: 500; }
        .form-control:focus { border-color: #a87f51; box-shadow: 0 0 0 .13rem rgba(168,127,81,.13); }
        .btn-brown { background: #a87f51; color: #fff; border: 1.5px solid #8a5c22; font-weight: 600; }
        .btn-brown:hover, .btn-brown:focus { background: #895c23; color: #fff; }
        .info-box {
            background: #f7e5c7;
            border-left: 4px solid #a87f51;
            border-radius: 7px;
            padding: 12px 16px;
            color: #795528;
            margin-bottom: 18px;
        }
        .table th, .table td { vertical-align: middle; }
        h2, .section-title, .input-label { color: #a87f51 !important; }
        .alert-success { font-size:1.11em; letter-spacing:.01em;}
        @media (max-width: 991px) {
            .main-box { padding: 18px 5px 14px 5px; }
        }
        .fade-out { transition: background 0.22s, opacity 0.32s; background: #ffe9d1 !important; opacity: 0; }
    </style>
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-box">
    <h2 class="section-title"><i class="bi bi-cash-coin"></i> Set Parcel Pricing</h2>
    <div id="successMsg">
        <?php if(!empty($successMsg)): ?>
            <div class="alert alert-success mb-3"><?= $successMsg ?></div>
        <?php endif; ?>
        <div id="ajaxMsg"></div>
    </div>
    <div class="pricing-card p-4 mb-4">
        <form method="post" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="input-label">Origin</label>
                <select name="origin" class="form-control" required>
                    <option value="">Select Origin</option>
                    <?php foreach ($origins as $o): ?>
                    <option value="<?= $o ?>"><?= $o ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="input-label">Destination Country</label>
                <select name="country" class="form-control" required>
                    <option value="">Select Country</option>
                    <?php foreach ($countries as $c): ?>
                    <option value="<?= $c ?>"><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="input-label">Price per KG ($)</label>
                <input type="number" step="0.01" min="0" name="price_per_kg" class="form-control" required>
            </div>
            <div class="col-md-1">
                <button class="btn btn-brown w-100">Save</button>
            </div>
        </form>
    </div>

    <div class="info-box">
        Set or update the shipping price per kg for each origin and destination country combination.<br>
        <b>To update:</b> select the same origin/country, enter new price, then Save.<br>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center bg-white" id="pricingTable">
            <thead style="background:#fdeac3;">
                <tr>
                    <th>Origin</th>
                    <th>Destination Country</th>
                    <th>Price per KG ($)</th>
                    <!-- <th style="width: 85px;">Actions</th> -->
                </tr>
            </thead>
            <tbody>
                <?php while($row = $all->fetch_assoc()): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row['origin']) ?></td>
                    <td><?= htmlspecialchars($row['country']) ?></td>
                    <td>$<?= number_format($row['price_per_kg'],2) ?></td>
                    <!-- <td>
                        <button type="button" class="btn btn-danger btn-sm del-btn" data-id="<?= $row['id'] ?>">Delete</button>
                    </td> -->
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function(){
    $('.del-btn').on('click', function() {
        let rowId = $(this).data('id');
        if (!confirm('Delete this pricing?')) return;
        $.ajax({
            url: 'setpricing.php',
            method: 'POST',
            data: { delete_id: rowId },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                try {
                    let json = JSON.parse(res);
                    if (json.success) {
                        $('#row-' + rowId).addClass('fade-out');
                        setTimeout(() => { $('#row-' + rowId).remove(); }, 350);
                        $('#ajaxMsg').html('<div class="alert alert-success mb-2">Pricing deleted successfully!</div>');
                    }
                } catch(e) {
                    $('#ajaxMsg').html('<div class="alert alert-danger mb-2">Could not delete. Try again.</div>');
                }
            }
        });
    });
});
</script>
</body>
</html>
