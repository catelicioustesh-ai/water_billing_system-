<?php
include("auth.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Water Billing</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-container">
    <div class="dashboard-hero">
        <div class="hero-copy">
            <span class="hero-badge">Water Billing</span>
            <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p>Manage customers, capture readings, generate bills, and view reports from one modern control center.</p>
            <div class="hero-actions">
                <a class="primary-cta" href="readings.php">Capture Reading</a>
                <a class="secondary-cta" href="reports.php">View Reports</a>
            </div>
        </div>
        <div class="hero-side">
            <div class="hero-highlights">
                <div><strong>Fast access</strong><span>Quick links for core tasks.</span></div>
                <div><strong>Clear flow</strong><span>Return to dashboard from every page.</span></div>
            </div>
            <div class="meter-card">
                <div class="meter-illustration" aria-hidden="true">
                    <svg viewBox="0 0 320 220" role="img">
                        <rect x="44" y="40" width="232" height="140" rx="28" fill="#0f172a" opacity="0.08"></rect>
                        <rect x="62" y="58" width="196" height="104" rx="20" fill="#f8fbff"></rect>
                        <path d="M112 148a68 68 0 1 1 96 0" stroke="#2d57d4" stroke-width="12" stroke-linecap="round" fill="none"></path>
                        <line x1="160" y1="90" x2="160" y2="132" stroke="#1f3fb2" stroke-width="10" stroke-linecap="round"></line>
                        <circle cx="160" cy="148" r="10" fill="#2d57d4"></circle>
                        <rect x="96" y="174" width="128" height="16" rx="8" fill="#dce7ff"></rect>
                        <text x="160" y="118" text-anchor="middle" font-size="24" font-family="Arial, sans-serif" fill="#1f3fb2">1,250L</text>
                    </svg>
                </div>
                <div class="meter-status">
                    <span class="status-dot"></span>
                    Billing flow is active
                </div>
            </div>
        </div>
    </div>

    <ul class="dashboard-actions">
        <li>
            <a href="customers.php">
                <span class="card-icon">👤</span>
                <span class="card-text">
                    <span class="card-title">Register Customer</span>
                    <span class="card-description">Create a new customer profile.</span>
                </span>
            </a>
        </li>
        <li>
            <a href="customers_list.php">
                <span class="card-icon">📋</span>
                <span class="card-text">
                    <span class="card-title">View Customers</span>
                    <span class="card-description">See all registered customers.</span>
                </span>
            </a>
        </li>
        <li>
            <a href="search_customer.php">
                <span class="card-icon">🔍</span>
                <span class="card-text">
                    <span class="card-title">Search Customer</span>
                    <span class="card-description">Find customers fast.</span>
                </span>
            </a>
        </li>
        <li>
            <a href="readings.php">
                <span class="card-icon">⚡</span>
                <span class="card-text">
                    <span class="card-title">Capture Meter Reading</span>
                    <span class="card-description">Record consumption details.</span>
                </span>
            </a>
        </li>
        <li>
            <a href="reports.php">
                <span class="card-icon">📊</span>
                <span class="card-text">
                    <span class="card-title">Billing Reports</span>
                    <span class="card-description">View billing summaries and payments.</span>
                </span>
            </a>
        </li>
        <li>
            <a href="pay_bill.php">
                <span class="card-icon">💳</span>
                <span class="card-text">
                    <span class="card-title">Record Payment</span>
                    <span class="card-description">Mark bills as paid and track payments.</span>
                </span>
            </a>
        </li>
        <li>
            <a href="logout.php">
                <span class="card-icon">🚪</span>
                <span class="card-text">
                    <span class="card-title">Logout</span>
                    <span class="card-description">Exit the dashboard safely.</span>
                </span>
            </a>
        </li>
    </ul>
</div>

</body>
</html>