<?php
include("auth.php");
include("db_connect.php");

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $customer_id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "DELETE FROM customers WHERE id = '$customer_id'";
    if (mysqli_query($conn, $sql)) {
        $message = 'Customer deleted successfully.';
    } else {
        $message = 'Unable to delete customer. Please try again.';
    }
} else {
    $message = 'No customer ID provided to delete.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Delete Customer</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <div class="action-links">
        <a href="customers_list.php">Back to Customers List</a>
        <a href="dashboard.php">Dashboard</a>
    </div>
</div>
</body>
</html>
