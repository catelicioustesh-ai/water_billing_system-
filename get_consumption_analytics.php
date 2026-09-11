<?php
/**
 * Water Consumption Analytics & Reports
 * Shows automated consumption tracking and trends
 */

include("auth.php");
include("db.php");

header('Content-Type: application/json');

$time_period = isset($_GET['period']) ? $_GET['period'] : 'monthly'; // daily, weekly, monthly
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;

if (!$customer_id) {
    die(json_encode(['error' => 'Customer ID required']));
}

// Build query based on time period
switch ($time_period) {
    case 'daily':
        $group_by = "DATE(reading_date)";
        $date_format = "DATE(reading_date)";
        break;
    case 'weekly':
        $group_by = "WEEK(reading_date)";
        $date_format = "DATE_SUB(reading_date, INTERVAL DAYOFWEEK(reading_date)-1 DAY)";
        break;
    case 'monthly':
    default:
        $group_by = "MONTH(reading_date), YEAR(reading_date)";
        $date_format = "DATE_TRUNC(reading_date, INTERVAL 1 MONTH)";
        break;
}

$analytics_query = "SELECT 
    $date_format as period,
    SUM(consumption) as total_consumption,
    AVG(consumption) as avg_consumption,
    MAX(consumption) as max_consumption,
    MIN(consumption) as min_consumption,
    COUNT(*) as reading_count
FROM meter_readings
WHERE customer_id = ?
GROUP BY $group_by
ORDER BY period DESC
LIMIT 12";

$stmt = $conn->prepare($analytics_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$analytics = [];
while ($row = $result->fetch_assoc()) {
    $analytics[] = [
        'period' => $row['period'],
        'total_consumption' => floatval($row['total_consumption']),
        'avg_consumption' => floatval($row['avg_consumption']),
        'max_consumption' => floatval($row['max_consumption']),
        'min_consumption' => floatval($row['min_consumption']),
        'reading_count' => intval($row['reading_count'])
    ];
}

echo json_encode([
    'success' => true,
    'period' => $time_period,
    'analytics' => $analytics,
    'timestamp' => date('Y-m-d H:i:s')
]);

$conn->close();
?>
