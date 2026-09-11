<?php
include("auth.php");
include("db_connect.php");

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$customers = [];
$message = '';

if ($search !== '') {
    $term = mysqli_real_escape_string($conn, $search);
    $sql = "SELECT * FROM customers WHERE customer_name LIKE '%$term%' OR meter_number LIKE '%$term%' OR phone LIKE '%$term%' OR location LIKE '%$term%' ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
    }
    $message = count($customers) > 0 ? 'Search results for "' . htmlspecialchars($search) . '"' : 'No customers found for "' . htmlspecialchars($search) . '"';
} else {
    $sql = "SELECT * FROM customers ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
    }
    $message = 'Showing all customers';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Customers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Search Customers</h2>

<form method="GET" action="search_customer.php">
    Search by name, meter number, phone, or location:
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter search term">
    <input type="submit" value="Search">
</form>

<p><?php echo htmlspecialchars($message); ?></p>

<?php if (count($customers) > 0): ?>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Meter Number</th>
            <th>Phone</th>
            <th>Location</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($customers as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['meter_number']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td>
                    <a href="view_customer.php?id=<?php echo urlencode($row['id']); ?>">View</a>
                    | <a href="edit_customer.php?id=<?php echo urlencode($row['id']); ?>">Edit</a>
                    | <a href="delete_customer.php?id=<?php echo urlencode($row['id']); ?>" onclick="return confirm('Delete this customer?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>No customers to display.</p>
<?php endif; ?>

<br>
<a href="customers_list.php">Back to Customers List</a>
</div>
</body>
</html>
