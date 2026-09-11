<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Water Billing Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-shell">
    <div class="login-illustration" aria-hidden="true">
        <div class="illustration-card">
            <div class="wave-pill"></div>
            <svg viewBox="0 0 420 320" role="img">
                <rect x="44" y="46" width="332" height="228" rx="30" fill="#f6faff"></rect>
                <rect x="70" y="78" width="120" height="164" rx="20" fill="#e9f2ff"></rect>
                <rect x="220" y="96" width="110" height="128" rx="18" fill="#ffffff" stroke="#d0ddf7" stroke-width="2"></rect>
                <path d="M126 150c16-30 40-46 72-46 32 0 56 16 72 46" stroke="#2d57d4" stroke-width="12" stroke-linecap="round" fill="none"></path>
                <path d="M96 182h42" stroke="#5f85e9" stroke-width="10" stroke-linecap="round"></path>
                <path d="M276 142h56" stroke="#2d57d4" stroke-width="10" stroke-linecap="round"></path>
                <path d="M276 176h72" stroke="#7aa2ff" stroke-width="10" stroke-linecap="round"></path>
                <rect x="232" y="160" width="86" height="22" rx="11" fill="#dbe9ff"></rect>
                <circle cx="278" cy="171" r="8" fill="#2d57d4"></circle>
                <path d="M160 232h72" stroke="#2d57d4" stroke-width="10" stroke-linecap="round"></path>
                <path d="M150 250c18-12 26-18 42-18 16 0 24 6 42 18" stroke="#7aa2ff" stroke-width="8" stroke-linecap="round" fill="none"></path>
                <circle cx="315" cy="132" r="22" fill="#2d57d4"></circle>
                <path d="M315 118v28" stroke="#ffffff" stroke-width="8" stroke-linecap="round"></path>
                <path d="M301 132h28" stroke="#ffffff" stroke-width="8" stroke-linecap="round"></path>
            </svg>
            <div class="illustration-caption">
                <span class="status-dot"></span>
                Smart meter connected
            </div>
        </div>
    </div>

    <div class="page-container login-card">
        <span class="hero-badge">Automated Water Billing</span>
        <h2>Welcome Back</h2>
        <p>Sign in to manage meter readings, billing, and payments in one place.</p>
        <form action="process_login.php" method="POST" class="form-card">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</div>

</body>
</html>