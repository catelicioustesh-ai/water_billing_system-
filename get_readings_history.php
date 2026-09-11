<?php
include("auth.php");
include("db_connect.php");

header('Content-Type: application/json');

if (!isset($_GET['customer_id'])) {
    echo json_encode([]);
    exit;
}

$cust = intval($_GET['customer_id']);
$sql = "SELECT id, previous_reading, current_reading, consumption, reading_date FROM meter_readings WHERE customer_id = '$cust' ORDER BY id ASC";
$res = mysqli_query($conn, $sql);
$rows = [];
while ($row = mysqli_fetch_assoc($res)) {
    $rows[] = $row;
}

echo json_encode($rows);

?>
