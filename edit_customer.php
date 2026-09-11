<?php
include("auth.php");
include("db_connect.php");

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo 'No customer ID provided.';
    exit;
}

$customer_id = mysqli_real_escape_string($conn, $_GET['id']);
$sql = "SELECT * FROM customers WHERE id = '$customer_id'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo 'Customer not found.';
    exit;
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Edit Customer</h2>

<form action="update_customer.php" method="POST">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">

    Customer Name:<br>
    <input type="text" name="customer_name" value="<?php echo htmlspecialchars($row['customer_name']); ?>" required><br><br>

    Meter Number:<br>
    <input type="text" name="meter_number" value="<?php echo htmlspecialchars($row['meter_number']); ?>" required><br><br>

    Phone Number:<br>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required><br><br>

    Location:<br>
    <input type="text" name="location" value="<?php echo htmlspecialchars($row['location']); ?>" required><br><br>

    <input type="submit" value="Update Customer">
</form>

<br>
<a href="view_customer.php?id=<?php echo urlencode($row['id']); ?>">Back to Customer Details</a><br>
<a href="customers_list.php">Back to Customers List</a>
</div>
</body>
</html>
