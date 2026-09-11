<?php
/**
 * Automated Billing Calculation & Processing
 * This script processes all pending readings and generates bills
 * Can be run via cron job or scheduler
 */

$conn = new mysqli("localhost", "root", "", "WATER_BILLING_PROJECT");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Log start
echo "[" . date('Y-m-d H:i:s') . "] Starting automated billing process...\n";

// Get all customers with readings from today
$query = "SELECT DISTINCT mr.customer_id, c.customer_name, c.meter_number, 
          mr.current_reading, mr.previous_reading, mr.consumption, mr.reading_date
          FROM meter_readings mr
          JOIN customers c ON mr.customer_id = c.id
          WHERE DATE(mr.reading_date) = CURDATE()
          AND mr.customer_id NOT IN (
              SELECT customer_id FROM bills WHERE DATE(billing_date) = CURDATE()
          )";

$result = $conn->query($query);
$processed = 0;
$errors = 0;

while ($row = $result->fetch_assoc()) {
    $customer_id = $row['customer_id'];
    $consumption = floatval($row['consumption']);
    $rate = 50; // Per unit rate
    $amount = $consumption * $rate;
    $billing_date = date('Y-m-d');

    // Insert bill
    $bill_query = "INSERT INTO bills (customer_id, consumption, amount, billing_date, status) 
                   VALUES (?, ?, ?, ?, 'unpaid')";
    $stmt = $conn->prepare($bill_query);
    $stmt->bind_param("idds", $customer_id, $consumption, $amount, $billing_date);

    if ($stmt->execute()) {
        echo "✓ Bill generated for {$row['customer_name']} | Consumption: {$consumption} units | Amount: Rs. {$amount}\n";
        $processed++;
    } else {
        echo "✗ Error generating bill for Customer ID: {$customer_id}\n";
        $errors++;
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Billing process complete.\n";
echo "Processed: $processed | Errors: $errors\n";

$conn->close();
?>
