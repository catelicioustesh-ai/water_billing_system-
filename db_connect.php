<?php
$dbname = "waterbilling";

function ensureBillsColumns($conn, $dbname) {
    $columnsQuery = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'bills'");
    $existingColumns = [];

    while ($row = mysqli_fetch_assoc($columnsQuery)) {
        $existingColumns[] = $row['COLUMN_NAME'];
    }

    if (!in_array('status', $existingColumns, true)) {
        mysqli_query($conn, "ALTER TABLE bills ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'unpaid'");
    }

    if (!in_array('payment_date', $existingColumns, true)) {
        mysqli_query($conn, "ALTER TABLE bills ADD COLUMN payment_date DATE NULL");
    }

    if (!in_array('payment_method', $existingColumns, true)) {
        mysqli_query($conn, "ALTER TABLE bills ADD COLUMN payment_method VARCHAR(50) NULL");
    }
}

$conn = mysqli_connect("localhost", "root", "", "mysql");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname`")) {
    die("Failed to create database: " . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $dbname)) {
    die("Failed to select database: " . mysqli_error($conn));
}

$sql = file_get_contents(__DIR__ . '/db_setup.sql');
if ($sql !== false) {
    if (!mysqli_multi_query($conn, $sql)) {
        die("Schema import failed: " . mysqli_error($conn));
    }

    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));
}

ensureBillsColumns($conn, $dbname);

$defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
$checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$checkStmt->bind_param('s', $username);
$username = 'admin';
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $insertStmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $insertStmt->bind_param('ss', $username, $defaultPassword);
    $insertStmt->execute();
}