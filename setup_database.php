<?php
/**
 * Database Setup Script
 * Creates WATER_BILLING_PROJECT database and all required tables
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'WATER_BILLING_PROJECT';

$mysqli = new mysqli($host, $user, $pass);

if ($mysqli->connect_error) {
    die('❌ Database connection failed: ' . $mysqli->connect_error);
}

echo "<h2>🗄️ Database Setup</h2>";
echo "<hr>";

// Create Database
echo "<p><strong>Step 1: Creating Database...</strong></p>";
if ($mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname`") === TRUE) {
    echo "✅ Database '$dbname' created/verified<br>";
} else {
    die('❌ Failed to create database: ' . $mysqli->error);
}

$mysqli->select_db($dbname);

// Create Core Tables from db_setup.sql
echo "<p><strong>Step 2: Creating Core Tables...</strong></p>";
$sql = file_get_contents(__DIR__ . '/db_setup.sql');

if ($sql === false) {
    die('❌ Unable to read schema file.');
}

if (!$mysqli->multi_query($sql)) {
    die('❌ Schema import failed: ' . $mysqli->error);
}

while ($mysqli->next_result()) {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
}

echo "✅ Core tables created successfully<br>";

// Add new columns to customers table for automated readings
echo "<p><strong>Step 3: Adding Automated Reading Columns...</strong></p>";

$columns_to_add = [
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS api_key VARCHAR(100) UNIQUE",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS meter_status VARCHAR(20) DEFAULT 'active'",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS last_reading_date DATETIME",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS auto_billing_enabled BOOLEAN DEFAULT TRUE"
];

foreach ($columns_to_add as $alter_sql) {
    if ($mysqli->query($alter_sql) === TRUE) {
        echo "✅ Column added successfully<br>";
    } else {
        if (strpos($mysqli->error, 'Duplicate') === false) {
            echo "⚠️ " . $mysqli->error . "<br>";
        }
    }
}

// Add new columns to bills table
echo "<p><strong>Step 4: Adding Bill Enhancement Columns...</strong></p>";

$bill_columns = [
    "ALTER TABLE bills ADD COLUMN IF NOT EXISTS auto_generated BOOLEAN DEFAULT FALSE",
    "ALTER TABLE bills ADD COLUMN IF NOT EXISTS reading_id INT"
];

foreach ($bill_columns as $alter_sql) {
    if ($mysqli->query($alter_sql) === TRUE) {
        echo "✅ Bill column added<br>";
    } else {
        if (strpos($mysqli->error, 'Duplicate') === false) {
            echo "⚠️ " . $mysqli->error . "<br>";
        }
    }
}

// Create Automated Readings Tables
echo "<p><strong>Step 5: Creating Automated Reading Tables...</strong></p>";

$automated_tables = [
    "CREATE TABLE IF NOT EXISTS automated_readings_log (
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
    )",

    "CREATE TABLE IF NOT EXISTS meter_config (
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
    )",

    "CREATE TABLE IF NOT EXISTS consumption_alerts (
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
    )"
];

foreach ($automated_tables as $table_sql) {
    if ($mysqli->query($table_sql) === TRUE) {
        echo "✅ Automated table created<br>";
    } else {
        if (strpos($mysqli->error, 'already exists') === false) {
            echo "⚠️ " . $mysqli->error . "<br>";
        } else {
            echo "✅ Table already exists<br>";
        }
    }
}

// Create Default Admin User
echo "<p><strong>Step 6: Setting Up Admin User...</strong></p>";

$defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
$checkStmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
$username = 'admin';
$checkStmt->bind_param('s', $username);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $insertStmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $insertStmt->bind_param('ss', $username, $defaultPassword);
    if ($insertStmt->execute()) {
        echo "✅ Default admin user created<br>";
        echo "   Username: <strong>admin</strong><br>";
        echo "   Password: <strong>admin123</strong><br>";
        echo "   <span style='color: red;'>⚠️ Change this password after first login!</span><br>";
    }
} else {
    echo "ℹ️ Admin user already exists<br>";
}

echo "<hr>";
echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ Database Setup Complete!</p>";
echo "<p><a href='index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>→ Proceed to Login</a></p>";

$mysqli->close();

if (!in_array('payment_date', $existingColumns, true)) {
    $mysqli->query("ALTER TABLE bills ADD COLUMN payment_date DATE NULL");
}

if (!in_array('payment_method', $existingColumns, true)) {
    $mysqli->query("ALTER TABLE bills ADD COLUMN payment_method VARCHAR(50) NULL");
}

$mysqli->close();

header('Location: login.php');
exit();
