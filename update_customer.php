<?php
include("auth.php");
include("db_connect.php");

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo 'Missing customer ID for update.';
    exit;
}

$customer_id = mysqli_real_escape_string($conn, $_POST['id']);
$name = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
$meter = mysqli_real_escape_string($conn, trim($_POST['meter_number']));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
$location = mysqli_real_escape_string($conn, trim($_POST['location']));

$sql = "UPDATE customers SET customer_name = '$name', meter_number = '$meter', phone = '$phone', location = '$location' WHERE id = '$customer_id'";
if (mysqli_query($conn, $sql)) {
    $message = 'Customer updated successfully.';
} else {
    $message = 'Unable to update customer. Please try again.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Update Customer</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <div class="action-links">
        <a href="view_customer.php?id=<?php echo urlencode($customer_id); ?>">View Updated Customer</a>
        <a href="customers_list.php">Back to Customers List</a>
    </div>
</div>
</body>
</html>
