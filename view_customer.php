<?php
include("auth.php");
include("db_connect.php");

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $customer_id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM customers WHERE id = '$customer_id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        echo "Customer not found.";
        exit;
    }
} else {
    echo "No customer ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Customer Details</h2>

    <table class="detail-table">
        <tr>
            <td><strong>ID:</strong></td>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
        </tr>
        <tr>
            <td><strong>Name:</strong></td>
            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
        </tr>
        <tr>
            <td><strong>Meter Number:</strong></td>
            <td><?php echo htmlspecialchars($row['meter_number']); ?></td>
        </tr>
        <tr>
            <td><strong>Phone:</strong></td>
            <td><?php echo htmlspecialchars($row['phone']); ?></td>
        </tr>
        <tr>
            <td><strong>Location:</strong></td>
            <td><?php echo htmlspecialchars($row['location']); ?></td>
        </tr>
    </table>

    <div class="action-links">
        <a href="customers_list.php">Back to Customers List</a>
        <a href="edit_customer.php?id=<?php echo urlencode($row['id']); ?>">Edit</a>
        <a href="delete_customer.php?id=<?php echo urlencode($row['id']); ?>" onclick="return confirm('Delete this customer?');">Delete</a>
        <a href="search_customer.php">Search Customers</a>
    </div>

    <h3>Readings History</h3>
    <canvas id="readingsChart" width="800" height="300"></canvas>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="script.js"></script>
<script>
const customerId = <?php echo json_encode($row['id']); ?>;
if (typeof initCustomerChart === 'function') initCustomerChart(customerId);
if (typeof initSSEForCustomers === 'function') initSSEForCustomers();
</script>

</body>
</html>
