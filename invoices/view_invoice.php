<?php
require_once '../includes/auth.php';
include '../includes/db.php';
include '../includes/sidebar.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch all invoice + parcel details at once (no N+1 queries)
if ($role == 'admin') {
    $invoices = $conn->query("SELECT i.*, p.tracking_number, p.dispatch_origin, p.delivery_country, p.fragile 
        FROM invoices i 
        JOIN parcels p ON i.parcel_id = p.id 
        ORDER BY i.invoice_date DESC");
} elseif ($role == 'staff') {
    $invoices = $conn->query("SELECT i.*, p.tracking_number, p.dispatch_origin, p.delivery_country, p.fragile 
        FROM invoices i 
        JOIN parcels p ON i.parcel_id = p.id 
        WHERE p.assigned_staff=$user_id 
        ORDER BY i.invoice_date DESC");
} else {
    $invoices = $conn->query("SELECT i.*, p.tracking_number, p.dispatch_origin, p.delivery_country, p.fragile 
        FROM invoices i 
        JOIN parcels p ON i.parcel_id = p.id 
        WHERE p.sender_id=$user_id 
        ORDER BY i.invoice_date DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>Invoices</title>
    <style>
        body { background: #fcf6ed; }
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .btn-brown { background: #a87f51; color: #fff; border: 1px solid #8a5c22; }
        .btn-brown:hover { background: #8a5c22; }
        .invoice-header { color: #a87f51; font-weight: 700; font-size:1.8em; margin-bottom:1em; }
        .search-bar { margin-bottom: 1em; }
        .fragile-yes { color: #d00; font-weight: bold; }
        .fragile-no { color: #444; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <div class="invoice-header"><i class="bi bi-file-earmark-text"></i> Invoices</div>
        <div class="row search-bar">
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by Invoice #, Parcel, Origin, Destination...">
            </div>
        </div>
        <div class="table-responsive">
        <table class="table table-brown align-middle" id="invoiceTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice #</th>
                    <th>Tracking #</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Fragile</th>
                    <th>Amount ($)</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rownum = 1;
                while($i = $invoices->fetch_assoc()):
                    // Fragile logic supports 1 or 'Yes' (case-insensitive)
                    $fragile = ($i['fragile'] == 1 || strtolower($i['fragile']) === 'yes');
                ?>
                <tr>
                    <td><?= $rownum++; ?></td>
                    <td><?= htmlspecialchars($i['invoice_number']) ?></td>
                    <td><?= htmlspecialchars($i['tracking_number']) ?></td>
                    <td><?= htmlspecialchars($i['dispatch_origin']) ?></td>
                    <td><?= htmlspecialchars($i['delivery_country']) ?></td>
                    <td>
                        <?php if ($fragile): ?>
                            <span class="fragile-yes">Yes</span>
                        <?php else: ?>
                            <span class="fragile-no">No</span>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format($i['amount'],2) ?></td>
                    <td><?= date('d-m-Y', strtotime($i['invoice_date'] ?? $i['created_at'] ?? '')) ?></td>
                    <td>
                        <a href="generate_invoice.php?parcel_id=<?= $i['parcel_id'] ?>" class="btn btn-sm btn-brown" target="_blank">
                            <i class="bi bi-printer"></i> View/Print
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<script>
    // Simple live search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#invoiceTable tbody tr');
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(value) > -1 ? '' : 'none';
        });
    });
</script>
</body>
</html>
