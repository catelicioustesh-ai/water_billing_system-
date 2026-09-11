<?php
include("auth.php");
include("db_connect.php");

$customer_id = mysqli_real_escape_string($conn, $_POST['customer_id']);
$previous = mysqli_real_escape_string($conn, $_POST['previous_reading']);
$current = mysqli_real_escape_string($conn, $_POST['current_reading']);

if ($current < $previous) {
    $error = 'Error: Current Reading Cannot Be Less Than Previous Reading.';
}

$consumption = $current - $previous;
$rate = 50;
$amount = $consumption * $rate;

if (!isset($error)) {
    if ($consumption > 1000) {
        $error = 'Abnormal Consumption Detected. Reading Rejected.';
    } else {
        $bill_sql = "INSERT INTO bills (customer_id,consumption,amount,billing_date) VALUES ('$customer_id','$consumption','$amount',CURDATE())";
        mysqli_query($conn, $bill_sql);

        $sql = "INSERT INTO meter_readings (customer_id,previous_reading,current_reading,consumption,reading_date) VALUES ('$customer_id','$previous','$current','$consumption',CURDATE())";
        mysqli_query($conn, $sql);

        $message = "Reading Saved Successfully. Consumption = $consumption Units.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reading Saved</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Meter Reading Result</h2>
    <?php if (!empty($error)): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php else: ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <div class="action-links">
        <a href="readings.php">Capture Another Reading</a>
        <a href="dashboard.php">Dashboard</a>
    </div>
</div>
</body>
</html>
