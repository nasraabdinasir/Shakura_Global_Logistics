<?php
require_once '../includes/auth.php';
requireRole(['admin']);
include '../includes/db.php';

// Stats
$parcels_count = $conn->query("SELECT COUNT(*) FROM parcels")->fetch_row()[0];
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$staff_count = $conn->query("SELECT COUNT(*) FROM users WHERE role='staff'")->fetch_row()[0];
$pending_count = $conn->query("SELECT COUNT(*) FROM parcels WHERE status != 'Delivered'")->fetch_row()[0];

// Status distribution for chart
$status_dist = [];
$result = $conn->query("SELECT status, COUNT(*) as c FROM parcels GROUP BY status");
while ($row = $result->fetch_assoc()) $status_dist[$row['status']] = $row['c'];

// User roles distribution for chart
$roles_dist = [];
$result = $conn->query("SELECT role, COUNT(*) as c FROM users GROUP BY role");
while ($row = $result->fetch_assoc()) $roles_dist[$row['role']] = $row['c'];

// Recent Parcels
$recent_parcels = $conn->query("SELECT * FROM parcels ORDER BY created_at DESC LIMIT 5");

// Recent Users
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Latest RFID logs: join parcel_logs with parcels for context, just like your logs.php
$rfid_logs = $conn->query("
    SELECT pl.*, 
        p.tracking_number, 
        p.dispatch_origin, 
        p.delivery_country
    FROM parcel_logs pl 
    JOIN parcels p ON pl.parcel_id = p.id 
    WHERE pl.log_type = 'rfid' 
    ORDER BY pl.log_time DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin Dashboard</title>
    <style>
        body { background: #f5eee6; }
        .dashboard-title { color: #a87f51; font-size: 2rem; font-weight: 700; }
        .card-stat { background: #fffaf3; border-radius: 18px; min-width: 200px; box-shadow: 0 3px 15px rgba(80,45,5,0.06);}
        .stats-icon { font-size: 2.1em; color: #a87f51;}
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .badge-status { font-size: 1em; }
        .quick-link { background: #fff; border-radius: 12px; border: 1.5px solid #e4d5c5; padding: 18px 0; text-align: center; font-weight: 600; color: #a87f51; text-decoration: none; display: block; transition: all 0.14s;}
        .quick-link:hover { background: #e6dbcf; color: #64421d;}
        .quick-link i { font-size: 1.6em; display: block; margin-bottom: 6px;}
        .btn-outline-brown { color: #a87f51; border: 1px solid #a87f51; background: #fff;}
        .btn-outline-brown:hover { background: #e6dbcf; color: #64421d;}
        .dashboard-charts { background: #fffaf3; border-radius: 16px; box-shadow: 0 3px 15px rgba(80,45,5,0.07);}
        .bg-brown { background: #a87f51 !important; }
        .text-white { color: #fff !important; }
        .rfid-card-table th, .rfid-card-table td { font-size: 0.99em; }
    </style>
</head>
<body>
<?php include('../includes/sidebar.php'); ?>
<div class="d-flex">
    <div class="container-fluid p-4">
        <div class="dashboard-title mb-4"><i class="bi bi-speedometer2"></i> Admin Dashboard</div>
        
        <!-- Quick Links -->
        <div class="row quick-links mb-4">
            <div class="col-6 col-md-2"><a href="../parcels/add_parcel.php" class="quick-link"><i class="bi bi-plus-circle"></i>Add Parcel</a></div>
            <div class="col-6 col-md-2"><a href="../users/manage.php" class="quick-link"><i class="bi bi-people"></i>Users</a></div>
            <div class="col-6 col-md-2"><a href="../pricing/set_pricing.php" class="quick-link"><i class="bi bi-cash-coin"></i>Pricing</a></div>
            <div class="col-6 col-md-2"><a href="../rfid/logs.php" class="quick-link"><i class="bi bi-broadcast"></i>RFID Logs</a></div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4 g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-box-seam"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#a87f51;"><?= $parcels_count ?></div>
                    <div class="text-muted">Total Parcels</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-person-lines-fill"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#b98540;"><?= $users_count ?></div>
                    <div class="text-muted">Total Users</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-people"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#c3b081;"><?= $staff_count ?></div>
                    <div class="text-muted">Staff Accounts</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat text-center p-3">
                    <div class="stats-icon mb-2"><i class="bi bi-clock-history"></i></div>
                    <div style="font-size:2em; font-weight:700; color:#44a84b;"><?= $pending_count ?></div>
                    <div class="text-muted">Pending Deliveries</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-5 g-4">
            <div class="col-md-6">
                <div class="dashboard-charts p-4">
                    <h5 style="color:#a87f51;"><i class="bi bi-graph-up"></i> Parcel Status Distribution</h5>
                    <canvas id="statusChart" height="180"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-charts p-4">
                    <h5 style="color:#a87f51;"><i class="bi bi-person-badge"></i> User Roles</h5>
                    <canvas id="rolesChart" height="180"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Parcels -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-brown text-white"><b><i class="bi bi-truck"></i> Recent Parcels</b></div>
                    <div class="card-body p-0">
                        <table class="table table-brown table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Tracking #</th>
                                    <th>Receiver</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($p = $recent_parcels->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['tracking_number']) ?></td>
                                    <td><?= htmlspecialchars($p['receiver_name']) ?></td>
                                    <td><?= htmlspecialchars($p['status']) ?></td>
                                    <td><?= htmlspecialchars(date("M d", strtotime($p['created_at']))) ?></td>
                                    <td>
                                        <a href="../parcels/view_parcel.php" class="btn btn-sm btn-outline-brown">View</a>
                                        <a href="../parcels/edit_parcel.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-brown">Edit</a>
                                        <a href="../invoices/generate_invoice.php?parcel_id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">Invoice</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Recent Users -->
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-brown text-white"><b><i class="bi bi-person-lines-fill"></i> Recent Users</b></div>
                    <div class="card-body p-0">
                        <table class="table table-brown table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($u = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                                    <td>
                                        <a href="../users/edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-brown">Edit</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- RFID Logs -->
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-brown text-white"><b><i class="bi bi-broadcast"></i> Latest RFID Logs</b></div>
                    <div class="card-body p-0" style="max-height:325px; overflow-y:auto;">
                        <table class="table table-brown table-hover align-middle rfid-card-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tracking No</th>
                                    <th>Route</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>GPS</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rownum=1; while($l = $rfid_logs->fetch_assoc()): 
                                    // Extract GPS from remarks if present
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
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div class="text-end mt-2">
                            <a href="../rfid/logs.php" class="btn btn-sm btn-outline-brown">See all RFID logs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js Scripts -->
<script>
const statusChart = document.getElementById('statusChart').getContext('2d');
new Chart(statusChart, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($status_dist)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($status_dist)) ?>,
            backgroundColor: [
                '#a87f51', '#ecd9bc', '#b4a07a', '#44a84b', '#c68756', '#f7be81', '#7b6954'
            ],
            borderWidth: 1
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});

const rolesChart = document.getElementById('rolesChart').getContext('2d');
new Chart(rolesChart, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map('ucfirst', array_keys($roles_dist))) ?>,
        datasets: [{
            data: <?= json_encode(array_values($roles_dist)) ?>,
            backgroundColor: ['#a87f51','#b98540','#ecd9bc','#44a84b','#c3b081'],
            borderWidth: 1
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
</body>
</html>
