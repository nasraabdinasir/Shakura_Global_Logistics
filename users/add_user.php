<?php
require_once '../includes/auth.php';
requireRole(['admin']);
include '../includes/db.php';

$countries = [
    "Kenya", "Uganda", "Tanzania", "Nigeria", "Ethiopia", "South Africa", "United States", "United Kingdom",
    "India", "China", "Canada", "Australia", "Germany", "France", "Netherlands", "Turkey", "Italy", "Spain",
    "United Arab Emirates", "Saudi Arabia", "Qatar", "Egypt", "Ghana", "Morocco", "Japan", "Brazil", "Singapore"
    // ... Add more as needed
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $zip_code = $_POST['zip_code'];

    $conn->query("INSERT INTO users 
        (username, password, email, role, full_name, phone, address_line1, address_line2, city, country, zip_code) 
        VALUES 
        ('$username','$password','$email','$role','$full_name','$phone','$address_line1','$address_line2','$city','$country','$zip_code')");
    header("Location: manage.php?added=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add User</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container p-4">
        <h3>Add New User</h3>
        <form method="post" class="card p-3">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="phone" class="form-control" placeholder="Phone">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="col-md-6 mb-2">
                    <select name="role" class="form-control" required>
                        <option value="staff">Staff</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2">
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="city" class="form-control" placeholder="City" required>
                </div>
                <div class="col-md-4 mb-2">
                    <select name="country" class="form-control" required>
                        <option value="">Select Country...</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <input type="text" name="zip_code" class="form-control" placeholder="Zip Code" required>
                </div>
            </div>
            <button class="btn btn-brown mt-2">Add User</button>
        </form>
    </div>
</div>
</body>
</html>
