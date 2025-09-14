<?php
session_start();
if (isset($_SESSION['role'])) {
    header("Location: /dashboards/".$_SESSION['role']."_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shakura Global Express | Shipment Tracking System</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg,#f5eee6 0%,#fffaf4 80%);
            min-height: 100vh;
        }
        .brand-card {
            background: #fffaf4;
            border-radius: 32px;
            box-shadow: 0 6px 32px rgba(168,127,81,0.13);
            border: 1.5px solid #e4d5c5;
            padding: 48px 32px 36px 32px;
            max-width: 560px;
            margin: 62px auto 0 auto;
        }
        .brand-logo {
            width: 88px; height: 88px;
            border-radius: 18px;
            margin-bottom: 18px;
            box-shadow: 0 1px 7px rgba(180,140,70,0.06);
        }
        .brand-title {
            color: #a87f51;
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: 2px;
        }
        .brand-sub {
            color: #75552d;
            font-size: 1.19em;
            margin-bottom: 18px;
            font-weight: 500;
        }
        .btn-brown {
            background: #a87f51;
            color: #fff;
            border: 1.7px solid #8a5c22;
            font-weight: 700;
            font-size: 1.19em;
            padding: 10px 34px;
            border-radius: 14px;
            box-shadow: 0 2px 9px #efeadc41;
            margin-right: 12px;
            transition: background 0.12s;
        }
        .btn-brown:hover, .btn-brown:focus {
            background: #895c23;
            color: #fff;
        }
        .btn-outline-brown {
            border: 1.7px solid #a87f51;
            color: #a87f51;
            background: #fff;
            font-weight: 700;
            font-size: 1.15em;
            padding: 10px 32px;
            border-radius: 14px;
            margin-left: 0px;
            margin-right: 0px;
            box-shadow: 0 1px 6px #efeadc25;
        }
        .btn-outline-brown:hover, .btn-outline-brown:focus {
            background: #f8ecd9;
            color: #7c572a;
            border-color: #a87f51;
        }
        .quick-link {
            color: #a87f51;
            margin-top: 26px;
            font-size: 1.04em;
            text-decoration: underline dotted;
        }
        .quick-link:hover { color: #895c23; }
        @media (max-width: 576px) {
            .brand-card { padding: 26px 7vw 24px 7vw; }
            .brand-title { font-size: 2rem; }
            .btn-brown, .btn-outline-brown { width: 100%; margin: 0 0 10px 0; }
        }
    </style>
</head>
<body>
    <div class="brand-card text-center">
        <img src="assets/images/logo.jpeg" alt="Shakura Logo" class="brand-logo" />
        <div class="brand-title mb-2">Shakura Global Express</div>
        <div class="brand-sub">
            Fast, reliable, and secure international shipping.<br>
            Track, send, and manage your parcels with confidence.<br>
        </div>
        <div class="mb-4 mt-4">
            <a href="auth/login.php" class="btn btn-brown me-2">Login</a>
            <a href="auth/register.php" class="btn btn-outline-brown ms-1">Register as Customer</a>
        </div>
        <hr style="margin:18px 0 13px 0;">
        <div>
            <a href="track.php" class="quick-link"><i class="bi bi-search"></i> Track Parcel (Public)</a>
        </div>
        <div>
            <a href="index.html" class="quick-link"></i> Back to website</a>
        </div>
        <div class="text-muted mt-3" style="font-size:0.96em;">
            &copy; <?= date('Y') ?> Shakura Global Express Ltd. All rights reserved.
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
