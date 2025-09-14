<?php 
require_once '../includes/auth.php';
include '../includes/db.php';
include '../includes/sidebar.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Filtering logic for admin
$assigned_filter = '';
if ($role == 'admin') {
    if (isset($_GET['assigned'])) {
        if ($_GET['assigned'] === 'assigned') {
            $assigned_filter = "WHERE assigned_staff IS NOT NULL AND assigned_staff != 0";
        } elseif ($_GET['assigned'] === 'unassigned') {
            $assigned_filter = "WHERE assigned_staff IS NULL OR assigned_staff = 0";
        }
    }
    $parcels = $conn->query("SELECT * FROM parcels $assigned_filter ORDER BY created_at DESC");
} elseif ($role == 'staff') {
    $parcels = $conn->query("SELECT * FROM parcels WHERE assigned_staff='$user_id'");
} else {
    // Customer: only see their own parcels
    $parcels = $conn->query("SELECT * FROM parcels WHERE sender_id='$user_id'");
    // Fetch customer username for display
    $user_info = $conn->query("SELECT full_name, username FROM users WHERE id='$user_id'")->fetch_assoc();
    $customer_display_name = $user_info ? htmlspecialchars($user_info['full_name'] ?? $user_info['username']) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <title>Parcels</title>
    <style>
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .btn-brown { background: #a87f51; color: #fff; border: 1px solid #8a5c22; }
        .btn-brown:hover { background: #8a5c22; }
        .search-bar, .filter-bar { margin-bottom: 1.2em; }
        .form-select:focus { border-color: #a87f51; }
        .parcel-badge { font-size:0.95em; }
        .nowrap, .nowrap th, .nowrap td { white-space: nowrap !important; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container py-4">
        <h3 style="color:#a87f51;">Parcels List</h3>
        <?php if ($role == 'customer'): ?>
            
        <?php endif; ?>
        <div class="row mb-3">
            <div class="col-md-4 search-bar">
                <input type="text" id="searchInput" class="form-control" placeholder="Search parcels...">
            </div>
            <?php if ($role == 'admin'): ?>
            <div class="col-md-3 filter-bar">
                <form method="get" id="assignedFilterForm">
                    <select id="assignedFilter" name="assigned" class="form-select" onchange="document.getElementById('assignedFilterForm').submit();">
                        <option value="">All Parcels</option>
                        <option value="assigned" <?= (isset($_GET['assigned']) && $_GET['assigned']=='assigned') ? 'selected' : '' ?>>Assigned Parcels</option>
                        <option value="unassigned" <?= (isset($_GET['assigned']) && $_GET['assigned']=='unassigned') ? 'selected' : '' ?>>Unassigned Parcels</option>
                    </select>
                </form>
            </div>
            <?php endif; ?>
            <div class="col-md-3 filter-bar">
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Label Created">Label Created</option>
                    <option value="We Have Your Package">We Have Your Package</option>
                    <option value="On the Way">On the Way</option>
                    <option value="Out for Delivery">Out for Delivery</option>
                    <option value="Delivered">Delivered</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
        <table class="table table-brown table-hover align-middle nowrap" id="parcelTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tracking #</th>
                    <th>Receiver</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Chargeable Wt (kg)</th>
                    <th>Fragile</th>
                    <th>Price ($)</th>
                    <th>Status</th>
                    <th>Assigned Staff</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rownum = 1;
                while($p = $parcels->fetch_assoc()):
                    // Fetch staff name if assigned
                    $staff_name = '';
                    if ($p['assigned_staff']) {
                        $staff_res = $conn->query("SELECT full_name FROM users WHERE id='{$p['assigned_staff']}'");
                        if ($staff = $staff_res->fetch_assoc()) {
                            $staff_name = htmlspecialchars($staff['full_name']);
                        } else {
                            $staff_name = 'Unknown';
                        }
                    }
                ?>
                <tr>
                    <td><?= $rownum++; ?></td>
                    <td><?= htmlspecialchars($p['tracking_number']) ?></td>
                    <td><?= htmlspecialchars($p['receiver_name']) ?></td>
                    <td><?= htmlspecialchars($p['dispatch_origin']) ?></td>
                    <td><?= htmlspecialchars($p['delivery_country']) ?></td>
                    <td><?= htmlspecialchars($p['chargeable_weight']) ?></td>
                   <td>
    <?php
        // Works for int(1/0) and string Yes/No
        if ($p['fragile'] == 1 || strtolower($p['fragile']) === 'yes') {
            echo '<span class="badge bg-danger parcel-badge">Yes</span>';
        } else {
            echo '<span class="badge bg-secondary parcel-badge">No</span>';
        }
    ?>
</td>

                    <td><?= htmlspecialchars($p['price']) ?></td>
                    <td>
                        <?php
                            $badge = 'secondary';
                            if ($p['status'] == 'Delivered') $badge = 'success';
                            elseif ($p['status'] == 'Label Created') $badge = 'warning';
                            elseif ($p['status'] == 'On the Way') $badge = 'info';
                            elseif ($p['status'] == 'Out for Delivery') $badge = 'primary';
                            elseif ($p['status'] == 'We Have Your Package') $badge = 'dark';
                        ?>
                        <span class="badge bg-<?= $badge ?> parcel-badge"><?= htmlspecialchars($p['status']) ?></span>
                    </td>
                    <td>
                        <?= $staff_name ? $staff_name : '<span class="badge bg-warning text-dark">Unassigned</span>' ?>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-brown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($role == 'admin' || $role == 'staff'): ?>
                                    <li>
                                        <a class="dropdown-item" href="edit_parcel.php?id=<?= $p['id'] ?>">Edit</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($role == 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item text-danger" href="delete_parcel.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete this parcel?');">Delete</a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($role == 'customer'): ?>
                                    <?php if (empty($p['assigned_staff']) || $p['assigned_staff'] == 0): ?>
                                        <li>
                                            <a class="dropdown-item" href="edit_parcel.php?id=<?= $p['id'] ?>">Edit</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="delete_parcel.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete this parcel?');">Delete</a>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="../invoices/generate_invoice.php?parcel_id=<?= $p['id'] ?>">Invoice</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<!-- Bootstrap 5 JS for dropdown functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Live search
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#parcelTable tbody tr');
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(value) > -1 ? '' : 'none';
        });
    });
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        let value = this.value;
        let rows = document.querySelectorAll('#parcelTable tbody tr');
        rows.forEach(row => {
            let status = row.cells[8].innerText.trim();
            row.style.display = value === "" || status === value ? '' : 'none';
        });
    });
</script>
</body>
</html>
