<?php
include("auth.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Register Customer</h2>

    <form action="save_customer.php" method="POST" class="form-card">
        <label>Customer Name</label>
        <input type="text" name="customer_name" required>

        <label>Meter Number</label>
        <input type="text" name="meter_number" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Location</label>
        <input type="text" name="location" required>

        <input type="submit" value="Save Customer">
    </form>
</div>

</body>
</html>