<?php
include("auth.php");
include("db.php");

$action = isset($_POST['action']) ? $_POST['action'] : null;
$message = '';
$error = '';

// Generate API Key for a customer
if ($action === 'generate_key') {
    $customer_id = intval($_POST['customer_id']);
    
    // Check if customer exists
    $check_sql = "SELECT id, meter_number FROM customers WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        
        // Generate unique API key
        $api_key = 'meter_' . bin2hex(random_bytes(20)) . '_' . time();
        
        // Update customer with API key
        $update_sql = "UPDATE customers SET api_key = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $api_key, $customer_id);
        
        if ($stmt->execute()) {
            $message = "API Key generated successfully: <strong>$api_key</strong>";
        } else {
            $error = "Failed to generate API key";
        }
    } else {
        $error = "Customer not found";
    }
}

// Enable/Disable automated billing
if ($action === 'toggle_auto_billing') {
    $customer_id = intval($_POST['customer_id']);
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    
    $update_sql = "UPDATE customers SET auto_billing_enabled = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $enabled, $customer_id);
    
    if ($stmt->execute()) {
        $status = $enabled ? 'enabled' : 'disabled';
        $message = "Automated billing $status for this customer";
    } else {
        $error = "Failed to update automated billing status";
    }
}

// Get all customers with their automation status
$customers_sql = "SELECT id, customer_name, meter_number, api_key, auto_billing_enabled, last_reading_date 
                  FROM customers ORDER BY customer_name";
$customers_result = $conn->query($customers_sql);

// Get automated readings log
$log_sql = "SELECT * FROM automated_readings_log ORDER BY captured_at DESC LIMIT 20";
$log_result = $conn->query($log_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Automated Meter Reading Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .automation-container {
            max-width: 1200px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        .section {
            background: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #4CAF50;
        }
        .section h3 {
            margin-top: 0;
            color: #333;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-danger:hover {
            background-color: #da190b;
        }
        .status-active {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-inactive {
            color: #f44336;
            font-weight: bold;
        }
        .api-key-display {
            font-family: monospace;
            background: #f0f0f0;
            padding: 8px;
            border-radius: 4px;
            word-break: break-all;
            font-size: 12px;
        }
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .tab-buttons button {
            padding: 10px 20px;
            border: none;
            background: #ddd;
            cursor: pointer;
            border-radius: 4px;
        }
        .tab-buttons button.active {
            background: #4CAF50;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="automation-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h1>⚙️ Automated Meter Reading Management</h1>

    <?php if (!empty($message)): ?>
        <div class="success">✓ <?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="tab-buttons">
        <button class="tab-button active" onclick="switchTab('customers')">📊 Customer Configuration</button>
        <button class="tab-button" onclick="switchTab('logs')">📝 Activity Log</button>
        <button class="tab-button" onclick="switchTab('setup')">⚡ Setup Guide</button>
        <a href="test_automated_system.php" style="text-decoration: none;">
            <button type="button" class="tab-button" style="background: #2196F3;">🧪 Test System</button>
        </a>
    </div>

    <!-- Customer Configuration Tab -->
    <div id="customers" class="tab-content active">
        <div class="section">
            <h3>Meter Configuration</h3>
            <p>Manage automated reading settings for each meter:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Meter Number</th>
                        <th>API Key</th>
                        <th>Auto Billing</th>
                        <th>Last Reading</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = $customers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['meter_number']); ?></td>
                            <td>
                                <?php if ($customer['api_key']): ?>
                                    <div class="api-key-display"><?php echo htmlspecialchars($customer['api_key']); ?></div>
                                <?php else: ?>
                                    <span style="color: #999;">Not generated</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?php echo $customer['auto_billing_enabled'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $customer['auto_billing_enabled'] ? '✓ Enabled' : '✗ Disabled'; ?>
                                </span>
                            </td>
                            <td><?php echo $customer['last_reading_date'] ? date('Y-m-d H:i', strtotime($customer['last_reading_date'])) : 'Never'; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="generate_key">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                    <button type="submit" class="btn"><?php echo $customer['api_key'] ? 'Regenerate' : 'Generate'; ?> Key</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Log Tab -->
    <div id="logs" class="tab-content">
        <div class="section">
            <h3>Automated Reading Capture Log</h3>
            <p>Recent automated meter readings and bill generation:</p>
            
            <?php if ($log_result && $log_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Meter Number</th>
                            <th>Previous</th>
                            <th>Current</th>
                            <th>Consumption</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $log_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($log['captured_at'])); ?></td>
                                <td><?php echo htmlspecialchars($log['meter_number']); ?></td>
                                <td><?php echo number_format($log['previous_reading'], 2); ?></td>
                                <td><?php echo number_format($log['current_reading'], 2); ?></td>
                                <td><?php echo number_format($log['consumption'], 2); ?> units</td>
                                <td>
                                    <span class="<?php echo $log['status'] === 'success' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo strtoupper($log['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #999;">No automated readings captured yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Setup Guide Tab -->
    <div id="setup" class="tab-content">
        <div class="section">
            <h3>Setup Instructions</h3>
            
            <h4>1️⃣ Generate API Keys</h4>
            <p>Click "Generate Key" for each meter in the Customer Configuration tab. Each meter will receive a unique API key.</p>

            <h4>2️⃣ Configure Meter Devices</h4>
            <p>Configure your smart meters to send readings via HTTP POST to:</p>
            <div class="api-key-display">
                <?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/auto_capture_reading.php"; ?>
            </div>

            <h4>3️⃣ Request Format</h4>
            <p>Meters should send JSON POST request with:</p>
            <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; font-family: monospace;">
{<br>
&nbsp;&nbsp;"meter_number": "M12345",<br>
&nbsp;&nbsp;"current_reading": 15500,<br>
&nbsp;&nbsp;"timestamp": "2026-07-19 14:30:00",<br>
&nbsp;&nbsp;"api_key": "meter_xxxxxxx"<br>
}
            </div>

            <h4>4️⃣ Setup Automated Billing (Cron Job)</h4>
            <p>Run the following command daily to auto-generate bills:</p>
            <div style="background: #f0f0f0; padding: 15px; border-radius: 4px; font-family: monospace;">
php <?php echo dirname(__FILE__) . "/process_automated_bills.php"; ?>
            </div>

            <h4>5️⃣ Monitor Automated Readings</h4>
            <p>Check the Activity Log tab to monitor all automated reading captures and errors.</p>

            <h4>📊 Features</h4>
            <ul>
                <li>✓ Real-time meter reading capture</li>
                <li>✓ Automatic bill generation</li>
                <li>✓ Anomaly detection (consumption > 1000 units)</li>
                <li>✓ Complete audit trail</li>
                <li>✓ Water consumption analytics</li>
                <li>✓ Secure API key authentication</li>
            </ul>
        </div>
    </div>

</div>

<script>
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}
</script>

</body>
</html>
