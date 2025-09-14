<?php
require_once '../includes/db.php';
session_start();
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $res = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Fix: Map roles to the correct dashboard file
            $dashboard = "../dashboards/";
            if ($user['role'] == 'customer') {
                $dashboard .= "client_dashboard.php";
            } else {
                $dashboard .= $user['role'] . "_dashboard.php";
            }
            header("Location: $dashboard");
            exit;
        }
    }
    $error = "Invalid login";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Login - Shipment System</title>
    <style>
        body {
            background: linear-gradient(120deg, #fff7ec 0%, #eed8b3 100%);
            min-height: 100vh;
        }
        .login-card {
            border-radius: 17px;
            border: none;
            box-shadow: 0 4px 24px 0 rgba(164,120,67,0.08);
            background: #fff;
            padding: 2.8em 2.2em 2em 2.2em;
        }
        .brand-section {
            background: linear-gradient(120deg, #a87f51 40%, #bfa36c 100%);
            color: #fff;
            border-radius: 17px 0 0 17px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 380px;
            padding: 2em 1.3em;
        }
        .brand-section h2 { font-weight: 900; letter-spacing: 1px; }
        .brand-section p { font-size: 1.14em; font-weight: 400; margin-top: 0.8em;}
        .login-hero-icon {
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
            .login-card { border-radius: 0 0 17px 17px; }
        }
    </style>
</head>
<body>
<div class="container" style="min-height:100vh;">
    <div class="row justify-content-center align-items-center" style="min-height:100vh;">
        <div class="col-lg-8 col-md-10">
            <div class="row g-0">
                <!-- Brand/Info Section (left) -->
                <div class="col-md-5 d-none d-md-flex align-items-stretch">
                    <div class="brand-section text-center w-100">
                        <!-- Logo -->
                        <img src="../assets/images/logo.jpeg" alt="Shakura Express Logo" style="max-width: 90px; margin-bottom: 1em;">
                        <!-- <div class="login-hero-icon mb-3"><i class="bi bi-truck-front"></i></div> -->
                        <h2>Shakura Express</h2>
                        <p>Seamless tracking, transparent pricing, and world-class delivery.<br><b>Your shipment, our priority.</b></p>
                        <div class="mt-4 small" style="opacity:.8;">
                            <i class="bi bi-shield-lock"></i> Secure &amp; Reliable
                        </div>
                    </div>
                </div>
                <!-- Login Form (right) -->
                <div class="col-md-7 bg-light">
                    <div class="login-card h-100 d-flex flex-column justify-content-center">
                        <h3 class="mb-4 text-center text-brown fw-bold"><i class="bi bi-person-circle"></i> Log In</h3>
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="post" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label text-brown fw-semibold">Username</label>
                                <input type="text" name="username" id="username" class="form-control form-control-lg" placeholder="Enter username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label text-brown fw-semibold">Password</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Enter password" required>
                            </div>
                            <button class="btn btn-brown btn-lg w-100 mb-2" name="login" type="submit">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>
                        <div class="mt-3 text-center">
                            <span class="text-muted">Don't have an account?</span>
                            <a href="register.php" class="text-brown ms-1 fw-bold">Register as Customer</a>
                        </div>
                        <div class="mt-2 text-center">
                            <a href="../index.html" class="small text-brown text-decoration-underline"> Back to website</a>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div>
    </div>
</div>
</body>
</html>
