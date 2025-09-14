<?php
require_once '../includes/auth.php';
include '../includes/db.php';

$countries = [
    "Kenya", "Uganda", "Tanzania", "Nigeria", "Ethiopia", "South Africa", "United States", "United Kingdom",
    "India", "China", "Canada", "Australia", "Germany", "France", "Netherlands", "Turkey", "Italy", "Spain",
    "United Arab Emirates", "Saudi Arabia", "Qatar", "Egypt", "Ghana", "Morocco", "Japan", "Brazil", "Singapore"
];

$self = false;
if (isset($_GET['profile']) || (!isset($_GET['id']))) {
    // User editing their own profile
    $id = $_SESSION['user_id'];
    $self = true;
} else {
    requireRole(['admin']);
    $id = intval($_GET['id']);
}

$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if (!$user) die("User not found!");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username= $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $zip_code = $_POST['zip_code'];
    $update = "UPDATE users SET username= '$username', full_name='$full_name', email='$email', phone='$phone', address_line1='$address_line1', address_line2='$address_line2', city='$city', country='$country', zip_code='$zip_code'";
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $update .= ", password='$password'";
    }
    if (!$self && isset($_POST['role'])) {
        $role = $_POST['role'];
        $update .= ", role='$role'";
    }
    $update .= " WHERE id=$id";
    $conn->query($update);
    if ($self) {
        $_SESSION['role'] = $role ?? $_SESSION['role'];
        header("Location: profile.php?updated=1");
    } else {
        header("Location: manage.php?updated=1");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $self ? 'Edit Profile' : 'Edit User' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container p-4">
        <h3><?= $self ? 'Edit Profile' : 'Edit User' ?></h3>
        <form method="post" class="card p-3">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="username" class="form-control" placeholder=" UserName" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="full_name" class="form-control" placeholder="Full Name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="password" name="password" class="form-control" placeholder="New Password (leave blank for no change)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1" value="<?= htmlspecialchars($user['address_line1'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2" value="<?= htmlspecialchars($user['address_line2'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="city" class="form-control" placeholder="City" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-2">
                    <select name="country" class="form-control" required>
                        <option value="">Select Country...</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= ($user['country'] ?? '') == $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="zip_code" class="form-control" placeholder="Zip Code" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>" required>
                </div>
                <?php if(!$self): ?>
                <div class="col-md-6 mb-2">
                    <select name="role" class="form-control" required>
                        <option value="staff" <?= $user['role']=='staff'?'selected':'' ?>>Staff</option>
                        <option value="customer" <?= $user['role']=='customer'?'selected':'' ?>>Customer</option>
                        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <button class="btn btn-brown mt-2">Update</button>
        </form>
    </div>
</div>
</body>
</html>
