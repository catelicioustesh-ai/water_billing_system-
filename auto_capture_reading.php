<?php
/**
 * Automated Meter Reading API Endpoint
 * Smart meters send readings to this endpoint
 * Request format: POST /auto_capture_reading.php
 * {
 *   "meter_number": "M12345",
 *   "current_reading": 15500,
 *   "timestamp": "2026-07-19 14:30:00",
 *   "api_key": "meter_api_key_here"
 * }
 */

header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "WATER_BILLING_PROJECT");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid JSON input']));
}

$meter_number = isset($input['meter_number']) ? trim($input['meter_number']) : null;
$current_reading = isset($input['current_reading']) ? floatval($input['current_reading']) : null;
$api_key = isset($input['api_key']) ? trim($input['api_key']) : null;

// Validate input
if (!$meter_number || $current_reading === null || !$api_key) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing required fields: meter_number, current_reading, api_key']));
}

// Verify meter exists and API key is valid
$meter_query = "SELECT id, customer_name FROM customers WHERE meter_number = ? AND api_key = ?";
$stmt = $conn->prepare($meter_query);
$stmt->bind_param("ss", $meter_number, $api_key);
$stmt->execute();
$meter_result = $stmt->get_result();

if ($meter_result->num_rows === 0) {
    http_response_code(401);
    die(json_encode(['error' => 'Invalid meter number or API key']));
}

$meter = $meter_result->fetch_assoc();
$customer_id = $meter['id'];

// Get previous reading
$prev_query = "SELECT current_reading FROM meter_readings WHERE customer_id = ? ORDER BY reading_date DESC LIMIT 1";
$stmt = $conn->prepare($prev_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$prev_result = $stmt->get_result();

if ($prev_result->num_rows === 0) {
    $previous_reading = 0; // First reading
} else {
    $prev_row = $prev_result->fetch_assoc();
    $previous_reading = floatval($prev_row['current_reading']);
}

// Validate reading
if ($current_reading < $previous_reading) {
    http_response_code(400);
    die(json_encode(['error' => 'Current reading cannot be less than previous reading']));
}

$consumption = $current_reading - $previous_reading;

// Check for abnormal consumption
if ($consumption > 1000) {
    http_response_code(400);
    die(json_encode([
        'error' => 'Abnormal consumption detected',
        'consumption' => $consumption,
        'action' => 'Reading rejected. Manual verification required.'
    ]));
}

// Save reading
$reading_date = date('Y-m-d H:i:s');
$insert_reading = "INSERT INTO meter_readings (customer_id, previous_reading, current_reading, consumption, reading_date) 
                    VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_reading);
$stmt->bind_param("iddds", $customer_id, $previous_reading, $current_reading, $consumption, $reading_date);

if (!$stmt->execute()) {
    http_response_code(500);
    die(json_encode(['error' => 'Failed to save reading']));
}

// Calculate bill
$rate = 50; // Per unit rate
$amount = $consumption * $rate;
$billing_date = date('Y-m-d');

// Insert bill
$insert_bill = "INSERT INTO bills (customer_id, consumption, amount, billing_date, status) 
                VALUES (?, ?, ?, ?, 'unpaid')";
$stmt = $conn->prepare($insert_bill);
$stmt->bind_param("idds", $customer_id, $consumption, $amount, $billing_date);

if (!$stmt->execute()) {
    http_response_code(500);
    die(json_encode(['error' => 'Failed to generate bill']));
}

// Log automated reading
$log_query = "INSERT INTO automated_readings_log (customer_id, meter_number, previous_reading, current_reading, consumption, status, captured_at) 
              VALUES (?, ?, ?, ?, ?, 'success', NOW())";
$stmt = $conn->prepare($log_query);
$status = 'success';
$stmt->bind_param("issdd", $customer_id, $meter_number, $previous_reading, $current_reading, $consumption);
$stmt->execute();

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Reading captured successfully',
    'customer_id' => $customer_id,
    'previous_reading' => $previous_reading,
    'current_reading' => $current_reading,
    'consumption' => $consumption,
    'amount' => $amount,
    'billing_date' => $billing_date
]);

$conn->close();
?>
