<?php
include("auth.php");
include("db_connect.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Registered Customers</h2>

    <table border="1">

<tr>
<th>ID</th>
<th>Name</th>
<th>Meter Number</th>
<th>Phone</th>
<th>Location</th>
<th>Latest Reading</th>
<th>Action</th>
</tr>

<?php

$sql = "SELECT * FROM customers";
$result = mysqli_query($conn,$sql);

while($row=mysqli_fetch_assoc($result))
{
?>

<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['customer_name']; ?></td>
<td><?php echo $row['meter_number']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['location']; ?></td>
<td class="latest-reading" data-customer-id="<?php echo $row['id']; ?>">Loading...</td>
<td><a href="view_customer.php?id=<?php echo $row['id']; ?>">View</a></td>
</tr>

<?php
}
?>

</table>

</div>

<script src="script.js"></script>
<script>
if (typeof initCustomersListAutoUpdate === 'function') {
    initCustomersListAutoUpdate();
}
</script>

</body>
</html>
