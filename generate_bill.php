<?php
include("auth.php");
include("db_connect.php");

$customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
$consumption = mysqli_real_escape_string($conn, $_POST['consumption']);

$rate = 50;
$amount = $consumption * $rate;

$sql = "INSERT INTO bills (customer_id, consumption, amount, billing_date) VALUES ('$customer_id','$consumption','$amount',CURDATE())";
mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bill Generated</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Bill Generated Successfully</h2>
    <p>Customer ID: <?php echo htmlspecialchars($customer_id); ?></p>
    <p>Consumption: <?php echo htmlspecialchars($consumption); ?> Units</p>
    <p>Amount: Ksh <?php echo htmlspecialchars($amount); ?></p>
    <div class="action-links">
        <a href="billing.php">Generate Another Bill</a>
        <a href="dashboard.php">Dashboard</a>
    </div>
</div>
</body>
</html>