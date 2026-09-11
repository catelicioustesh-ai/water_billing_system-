<?php
include("auth.php");
include("db_connect.php");

$name = mysqli_real_escape_string($conn, $_POST['customer_name']);
$meter = mysqli_real_escape_string($conn, $_POST['meter_number']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$location = mysqli_real_escape_string($conn, $_POST['location']);

$sql = "INSERT INTO customers(customer_name,meter_number,phone,location) VALUES('$name','$meter','$phone','$location')";
$message = mysqli_query($conn, $sql) ? 'Customer Saved Successfully.' : 'Unable to save customer. Please try again.';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Saved</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Customer Saved</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <div class="action-links">
        <a href="customers.php">Register Another Customer</a>
        <a href="customers_list.php">View Customers</a>
    </div>
</div>
</body>
</html>