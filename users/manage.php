<?php
require_once '../includes/auth.php';
requireRole(['admin']);
include '../includes/db.php';

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .table-brown thead { background: #a87f51; color: #fff; }
        .table-brown tbody tr { border-bottom: 1.5px solid #e7dac6; }
        .btn-brown { background: #a87f51; color: #fff; border: 1px solid #8a5c22; }
        .btn-brown:hover { background: #8a5c22; }
        .dropdown-toggle::after { margin-left: 0.5em; }
        .address-info { font-size: 0.96em; color: #745c33; line-height:1.35; }
        @media (max-width: 900px) {
            .address-col { display: none; }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <h3>User Management</h3>
        <a href="add_user.php" class="btn btn-brown mb-3">Add User</a>
        <div class="table-responsive">
        <table class="table table-brown align-middle">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th class="address-col">Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($u = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= htmlspecialchars($u['phone']) ?></td>
                    <td class="address-col">
                        <div class="address-info">
                            <?= htmlspecialchars($u['address_line1'] ?? '') ?>
                            <?= !empty($u['address_line2']) ? '<br>' . htmlspecialchars($u['address_line2']) : '' ?>
                            <?php if(!empty($u['city'])): ?><br><?= htmlspecialchars($u['city']) ?><?php endif; ?>
                            <?php if(!empty($u['zip_code'])): ?>, <?= htmlspecialchars($u['zip_code']) ?><?php endif; ?>
                            <?php if(!empty($u['country'])): ?><br><?= htmlspecialchars($u['country']) ?><?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-brown dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="edit_user.php?id=<?= $u['id'] ?>">Edit</a>
                                </li>
                                <?php if($u['role'] != 'admin'): ?>
                                <li>
                                    <a class="dropdown-item text-danger" href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete user?');">Delete</a>
                                </li>
                                <?php endif; ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
