<?php
include("auth.php");
include("db_connect.php");

$summarySql = "SELECT
    COUNT(*) AS total_bills,
    SUM(amount) AS total_amount,
    SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS total_paid,
    SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END) AS total_unpaid,
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
    SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) AS unpaid_count
    FROM bills";
$summaryResult = mysqli_query($conn, $summarySql);
$summary = mysqli_fetch_assoc($summaryResult);

$billSql = "SELECT b.id, b.customer_id, c.customer_name, b.consumption, b.amount, b.billing_date, b.status, b.payment_date, b.payment_method
    FROM bills b
    LEFT JOIN customers c ON c.id = b.customer_id
    ORDER BY b.billing_date DESC, b.id DESC";
$billResult = mysqli_query($conn, $billSql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Water Billing Reports</h2>

    <p><a href="pay_bill.php">Record a payment</a></p>

    <h3>Summary</h3>
    <ul>
        <li>Total bills: <?php echo (int)$summary['total_bills']; ?></li>
        <li>Total billed amount: Ksh <?php echo number_format((float)$summary['total_amount'], 2); ?></li>
        <li>Paid bills: <?php echo (int)$summary['paid_count']; ?> (Ksh <?php echo number_format((float)$summary['total_paid'], 2); ?>)</li>
        <li>Unpaid bills: <?php echo (int)$summary['unpaid_count']; ?> (Ksh <?php echo number_format((float)$summary['total_unpaid'], 2); ?>)</li>
    </ul>

    <table border="1">
        <tr>
            <th>Bill ID</th>
            <th>Customer</th>
            <th>Consumption</th>
            <th>Amount</th>
            <th>Billing Date</th>
            <th>Status</th>
            <th>Payment Date</th>
            <th>Payment Method</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($billResult)): ?>
            <tr>
                <td><?php echo (int)$row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars($row['consumption']); ?></td>
                <td>Ksh <?php echo number_format((float)$row['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['billing_date']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td><?php echo !empty($row['payment_date']) ? htmlspecialchars($row['payment_date']) : 'Pending'; ?></td>
                <td><?php echo !empty($row['payment_method']) ? htmlspecialchars($row['payment_method']) : 'Pending'; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

</div>

</body>
</html>