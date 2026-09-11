<?php
/**
 * Automated Reading System Tester
 * Use this to test automated reading capture without a physical meter
 */

include("auth.php");
include("db.php");

$test_result = '';
$test_status = '';

// Handle test request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_reading'])) {
    $customer_id = intval($_POST['customer_id']);
    
    // Get customer details
    $cust_query = "SELECT id, meter_number, api_key FROM customers WHERE id = ?";
    $stmt = $conn->prepare($cust_query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $cust_result = $stmt->get_result();
    
    if ($cust_result->num_rows === 0) {
        $test_status = 'error';
        $test_result = 'Customer not found';
    } else {
        $customer = $cust_result->fetch_assoc();
        $api_key = $customer['api_key'];
        $meter_number = $customer['meter_number'];
        
        if (!$api_key) {
            $test_status = 'error';
            $test_result = 'No API key generated for this meter. Generate one first!';
        } else {
            // Get last reading to calculate next reading
            $last_reading_query = "SELECT current_reading FROM meter_readings WHERE customer_id = ? ORDER BY reading_date DESC LIMIT 1";
            $stmt = $conn->prepare($last_reading_query);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $last_result = $stmt->get_result();
            
            $last_reading = 0;
            if ($last_result->num_rows > 0) {
                $last_row = $last_result->fetch_assoc();
                $last_reading = floatval($last_row['current_reading']);
            }
            
            // Generate test reading (add random consumption)
            $test_consumption = intval($_POST['test_consumption']) ?: rand(50, 200);
            $test_reading_value = $last_reading + $test_consumption;
            
            // Prepare API request
            $payload = [
                'meter_number' => $meter_number,
                'current_reading' => $test_reading_value,
                'timestamp' => date('Y-m-d H:i:s'),
                'api_key' => $api_key
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost/Water_Billing_Project/auto_capture_reading.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($curl_error) {
                $test_status = 'error';
                $test_result = 'CURL Error: ' . $curl_error;
            } else {
                $response_data = json_decode($response, true);
                
                if ($http_code === 200 && isset($response_data['success'])) {
                    $test_status = 'success';
                    $test_result = '<strong>✓ Test Successful!</strong><br><br>' .
                        '<strong>Response:</strong><pre>' . json_encode($response_data, JSON_PRETTY_PRINT) . '</pre>';
                } else {
                    $test_status = 'error';
                    $test_result = '<strong>✗ API Error (HTTP ' . $http_code . '):</strong><br>' .
                        '<pre>' . (isset($response_data['error']) ? $response_data['error'] : $response) . '</pre>';
                }
            }
        }
    }
}

// Get all customers for dropdown
$customers_query = "SELECT id, customer_name, meter_number, api_key FROM customers ORDER BY customer_name";
$customers_result = $conn->query($customers_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Automated Reading System Tester</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tester-container {
            max-width: 900px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        .test-form {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
        }
        .submit-btn {
            background-color: #2196F3;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .submit-btn:hover {
            background-color: #1976D2;
        }
        .test-result {
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #4CAF50;
        }
        .test-result.success {
            background-color: #d4edda;
            border-left-color: #4CAF50;
            color: #155724;
        }
        .test-result.error {
            background-color: #f8d7da;
            border-left-color: #f44336;
            color: #721c24;
        }
        .test-result pre {
            background: white;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0 0 0;
            font-size: 12px;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            color: #1565c0;
        }
        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }
        .customer-info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
            display: none;
        }
        .customer-info.show {
            display: block;
        }
        .steps {
            background: #fff8e1;
            border: 1px solid #ffeb3b;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .steps h3 {
            margin-top: 0;
            color: #f57f17;
        }
        .steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 8px 0;
            color: #333;
        }
    </style>
</head>
<body>

<div class="tester-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h1>🧪 Automated Reading System Tester</h1>

    <div class="info-box">
        <strong>ℹ️ About This Tool</strong>
        This tool allows you to test the automated reading capture system without needing a physical smart meter. 
        It simulates meter readings to verify that your API endpoint is working correctly.
    </div>

    <div class="steps">
        <h3>📋 How to Use:</h3>
        <ol>
            <li>Select a customer meter from the dropdown</li>
            <li>Enter test consumption value (or leave blank for random value)</li>
            <li>Click "Send Test Reading"</li>
            <li>View the API response to verify success</li>
        </ol>
    </div>

    <div class="test-form">
        <h3>Send Test Reading</h3>
        
        <form method="POST" onchange="updateCustomerInfo()">
            <div class="form-group">
                <label for="customer_id">Select Meter:</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">-- Choose a meter --</option>
                    <?php while ($customer = $customers_result->fetch_assoc()): ?>
                        <option value="<?php echo $customer['id']; ?>" 
                                data-meter="<?php echo $customer['meter_number']; ?>"
                                data-key="<?php echo $customer['api_key']; ?>">
                            <?php echo htmlspecialchars($customer['customer_name']) . ' (' . $customer['meter_number'] . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="customer-info" id="customerInfo">
                <strong>Meter Details:</strong>
                <div>Meter Number: <code id="meterNumber"></code></div>
                <div>API Key: <code id="apiKey" style="word-break: break-all; font-size: 11px;"></code></div>
            </div>

            <div class="form-group">
                <label for="test_consumption">Consumption (units):</label>
                <input type="number" id="test_consumption" name="test_consumption" min="1" max="1000" 
                       placeholder="Leave blank for random value (50-200)" step="1">
            </div>

            <button type="submit" name="test_reading" class="submit-btn">📤 Send Test Reading</button>
        </form>
    </div>

    <?php if (!empty($test_result)): ?>
        <div class="test-result <?php echo $test_status; ?>">
            <?php echo $test_result; ?>
        </div>
    <?php endif; ?>

    <div class="info-box" style="margin-top: 40px;">
        <strong>⚠️ Before Testing:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Ensure API keys are generated for all meters</li>
            <li>Make sure auto_capture_reading.php is accessible</li>
            <li>Verify your database tables are created (run setup_automated_readings.php)</li>
        </ul>
    </div>

    <div class="info-box">
        <strong>✅ What Happens When Test Succeeds:</strong>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>Reading is saved to meter_readings table</li>
            <li>Consumption is calculated automatically</li>
            <li>Bill is generated automatically</li>
            <li>Entry logged in automated_readings_log</li>
        </ul>
    </div>

</div>

<script>
function updateCustomerInfo() {
    const select = document.getElementById('customer_id');
    const info = document.getElementById('customerInfo');
    const meterNum = document.getElementById('meterNumber');
    const apiKey = document.getElementById('apiKey');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        meterNum.textContent = option.getAttribute('data-meter');
        apiKey.textContent = option.getAttribute('data-key');
        info.classList.add('show');
    } else {
        info.classList.remove('show');
    }
}
</script>

</body>
</html>
