<?php
/**
 * Database Setup for Automated Readings
 * Adds necessary tables and columns for automated meter reading system
 */

$conn = new mysqli("localhost", "root", "", "WATER_BILLING_PROJECT");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Setting up automated reading system...\n";

// 1. Add columns to customers table for automated readings
$checks = [
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS api_key VARCHAR(100) UNIQUE",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS meter_status VARCHAR(20) DEFAULT 'active'",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS last_reading_date DATETIME",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS auto_billing_enabled BOOLEAN DEFAULT TRUE",
];

foreach ($checks as $sql) {
    if ($conn->query($sql)) {
        echo "✓ " . substr($sql, 0, 50) . "...\n";
    } else {
        if (strpos($conn->error, "Duplicate column") === false) {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
}

// 2. Create automated readings log table
$log_table = "CREATE TABLE IF NOT EXISTS automated_readings_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    meter_number VARCHAR(50),
    previous_reading DECIMAL(10,2),
    current_reading DECIMAL(10,2),
    consumption DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'success',
    error_message TEXT,
    captured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_captured_at (captured_at)
)";

if ($conn->query($log_table)) {
    echo "✓ Created automated_readings_log table\n";
} else {
    if (strpos($conn->error, "already exists") === false) {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

// 3. Create meter configuration table
$config_table = "CREATE TABLE IF NOT EXISTS meter_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL UNIQUE,
    meter_number VARCHAR(50) NOT NULL UNIQUE,
    api_key VARCHAR(100) NOT NULL UNIQUE,
    billing_cycle INT DEFAULT 30,
    consumption_threshold INT DEFAULT 1000,
    rate_per_unit DECIMAL(10,2) DEFAULT 50,
    status VARCHAR(20) DEFAULT 'active',
    last_sync DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_meter_number (meter_number),
    INDEX idx_api_key (api_key)
)";

if ($conn->query($config_table)) {
    echo "✓ Created meter_config table\n";
} else {
    if (strpos($conn->error, "already exists") === false) {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

// 4. Create consumption alerts table
$alerts_table = "CREATE TABLE IF NOT EXISTS consumption_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    alert_type VARCHAR(50),
    consumption_value DECIMAL(10,2),
    alert_message TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_created_at (created_at)
)";

if ($conn->query($alerts_table)) {
    echo "✓ Created consumption_alerts table\n";
} else {
    if (strpos($conn->error, "already exists") === false) {
        echo "✗ Error: " . $conn->error . "\n";
    }
}

// 5. Add columns to bills table if needed
$bill_checks = [
    "ALTER TABLE bills ADD COLUMN IF NOT EXISTS auto_generated BOOLEAN DEFAULT FALSE",
    "ALTER TABLE bills ADD COLUMN IF NOT EXISTS reading_id INT",
];

foreach ($bill_checks as $sql) {
    if ($conn->query($sql)) {
        echo "✓ " . substr($sql, 0, 50) . "...\n";
    } else {
        if (strpos($conn->error, "Duplicate column") === false && strpos($conn->error, "already exists") === false) {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
}

echo "\n✓ Automated reading system setup complete!\n";
echo "\nNext steps:\n";
echo "1. Generate API keys for each meter\n";
echo "2. Configure meter devices to POST readings to: /auto_capture_reading.php\n";
echo "3. Set up cron job to run process_automated_bills.php daily\n";

$conn->close();
?>
