<?php
include("auth.php");
include("db_connect.php");

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billId = (int)($_POST['bill_id'] ?? 0);
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
    $paymentDate = trim($_POST['payment_date'] ?? '');

    if ($paymentDate === '') {
        $paymentDate = date('Y-m-d');
    }

    if ($billId > 0) {
        $stmt = $conn->prepare("UPDATE bills SET status = 'paid', payment_date = ?, payment_method = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $paymentDate, $paymentMethod, $billId);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "Payment recorded successfully.";
                } else {
                    $message = "No matching bill was found to update.";
                }
            } else {
                $message = "Failed to record payment: " . htmlspecialchars($stmt->error);
            }
        } else {
            $message = "Failed to prepare payment update: " . htmlspecialchars($conn->error);
        }
    } else {
        $message = "Invalid bill selected.";
    }
}

$sql = "SELECT b.id, b.customer_id, c.customer_name, b.amount, b.billing_date, b.status, b.payment_date, b.payment_method
        FROM bills b
        LEFT JOIN customers c ON c.id = b.customer_id
        ORDER BY b.billing_date DESC, b.id DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    $message = "Unable to load bills: " . htmlspecialchars(mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Record Payment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Record Payments</h2>
    <?php if ($message !== ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <table border="1">
        <tr>
            <th>Bill ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Billing Date</th>
            <th>Status</th>
            <th>Payment Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo (int)$row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Unknown'); ?></td>
                <td>Ksh <?php echo number_format((float)$row['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['billing_date']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td><?php echo !empty($row['payment_date']) ? htmlspecialchars(date('d M Y', strtotime($row['payment_date']))) : 'Pending'; ?></td>
                <td>
                    <?php if ($row['status'] === 'paid'): ?>
                        Paid via <?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="bill_id" value="<?php echo (int)$row['id']; ?>">
                            <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                            <select name="payment_method">
                                <option value="Cash">Cash</option>
                                <option value="Mpesa">M-Pesa</option>
                                <option value="Bank">Bank</option>
                            </select>
                            <input type="submit" value="Mark Paid">
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
