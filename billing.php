<?php
include("auth.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generate Bill</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Generate Water Bill</h2>

    <form action="generate_bill.php" method="POST" class="form-card">
        <label>Customer ID</label>
        <input type="number" name="customer_id" required>

        <label>Consumption (Units)</label>
        <input type="number" name="consumption" required>

        <input type="submit" value="Generate Bill">
    </form>
</div>

</body>
</html>