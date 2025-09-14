<?php
require_once '../includes/db.php';

$countries = [
    "Kenya", "Uganda", "Tanzania", "Nigeria", "Ethiopia", "South Africa", "United States", "United Kingdom",
    "India", "China", "Canada", "Australia", "Germany", "France", "Netherlands", "Turkey", "Italy", "Spain", "United Arab Emirates", "Saudi Arabia", "Qatar", "Egypt", "Ghana", "Morocco", "Japan", "Brazil", "Singapore"
    // ... Add more as needed
];

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $zip_code = $_POST['zip_code'];
    $role = 'customer';
    $conn->query("INSERT INTO users 
        (username, password, email, role, full_name, phone, address_line1, address_line2, city, country, zip_code) 
        VALUES 
        ('$username','$password','$email','$role','$full_name','$phone','$address_line1','$address_line2','$city','$country','$zip_code')");
    header("Location: login.php?reg=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Register - Shipment System</title>
    <style>
        body {
            background: linear-gradient(120deg, #fff7ec 0%, #eed8b3 100%);
            min-height: 100vh;
        }
        .register-card {
            border-radius: 17px;
            border: none;
            box-shadow: 0 4px 24px 0 rgba(164,120,67,0.08);
            background: #fff;
            padding: 2.6em 2.1em 2em 2.1em;
        }
        .brand-section {
            background: linear-gradient(120deg, #a87f51 40%, #bfa36c 100%);
            color: #fff;
            border-radius: 17px 0 0 17px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 500px;
            padding: 2em 1.3em;
        }
        .brand-section h2 { font-weight: 900; letter-spacing: 1px; }
        .brand-section p { font-size: 1.14em; font-weight: 400; margin-top: 0.8em;}
        .register-hero-icon {
            font-size: 3.2em;
            background: #fff3e3;
            border-radius: 50%;
            padding: 0.32em 0.4em;
            margin-bottom: 0.4em;
            color: #a87f51;
        }
        .btn-brown {
            background: #a87f51;
            color: #fff;
            border: 1.5px solid #8a5c22;
            font-weight: 600;
        }
        .btn-brown:hover {
            background: #895c23;
            color: #fff;
        }
        .text-brown { color: #a87f51; }
        .form-control:focus { border-color: #a87f51; box-shadow: 0 0 0 .15rem rgba(168,127,81,.14); }
        @media (max-width: 767px) {
            .brand-section { border-radius: 17px 17px 0 0; min-height: 180px; }
            .register-card { border-radius: 0 0 17px 17px; }
        }
    </style>
</head>
<body>
<div class="container" style="min-height:100vh;">
    <div class="row justify-content-center align-items-center" style="min-height:100vh;">
        <div class="col-lg-10 col-md-11">
            <div class="row g-0">
                <!-- Brand/Info Section (left) -->
                <div class="col-md-5 d-none d-md-flex align-items-stretch">
                    <div class="brand-section text-center w-100">
                        <img src="../assets/images/logo.jpeg" alt="Shakura Express Logo" style="max-width: 90px; margin-bottom: 1em;">
                        <h2>Shakura Express</h2>
                        <p>Register to manage your shipments, get instant quotes, and enjoy worry-free logistics.<br><b>Start your journey with us!</b></p>
                        <div class="mt-4 small" style="opacity:.8;">
                            <i class="bi bi-shield-lock"></i> Your data is always secure
                        </div>
                    </div>
                </div>
                <!-- Registration Form (right) -->
                <div class="col-md-7 bg-light">
                    <div class="register-card h-100 d-flex flex-column justify-content-center">
                        <h3 class="mb-4 text-center text-brown fw-bold"><i class="bi bi-person-plus"></i> Create Account</h3>
                        <form method="post" autocomplete="off">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="full_name" class="form-label text-brown fw-semibold">Full Name</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control form-control-lg" placeholder="Enter your full name" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="username" class="form-label text-brown fw-semibold">Username</label>
                                    <input type="text" name="username" id="username" class="form-control form-control-lg" placeholder="Choose a username" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label text-brown fw-semibold">Email</label>
                                    <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="phone" class="form-label text-brown fw-semibold">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control form-control-lg" placeholder="Phone number" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="address_line1" class="form-label text-brown fw-semibold">Address Line 1</label>
                                    <input type="text" name="address_line1" id="address_line1" class="form-control" placeholder="Street address, P.O. box" required>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="address_line2" class="form-label text-brown fw-semibold">Address Line 2</label>
                                    <input type="text" name="address_line2" id="address_line2" class="form-control" placeholder="Apartment, suite, unit etc. (optional)">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-5">
                                    <label for="city" class="form-label text-brown fw-semibold">City</label>
                                    <input type="text" name="city" id="city" class="form-control" placeholder="City or town" required>
                                </div>
                                <div class="mb-3 col-md-4">
                                    <label for="country" class="form-label text-brown fw-semibold">Country</label>
                                    <select name="country" id="country" class="form-select" required>
                                        <option value="">Select country...</option>
                                        <?php foreach ($countries as $c): ?>
                                            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="zip_code" class="form-label text-brown fw-semibold">Zip Code</label>
                                    <input type="text" name="zip_code" id="zip_code" class="form-control" placeholder="Zip / Postal code" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label text-brown fw-semibold">Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Create password" required>
                            </div>
                            <button class="btn btn-brown btn-lg w-100" name="register" type="submit">
                                <i class="bi bi-person-check"></i> Register
                            </button>
                        </form>
                        <div class="mt-3 text-center">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="text-brown ms-1 fw-bold">Login</a>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div>
    </div>
</div>
</body>
</html>
