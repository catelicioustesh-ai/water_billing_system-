<?php
include("auth.php");
include("db_connect.php");

header('Content-Type: application/json');

$sql = "SELECT r.*, c.customer_name, c.meter_number FROM meter_readings r LEFT JOIN customers c ON c.id = r.customer_id ORDER BY r.id DESC LIMIT 20";
$result = mysqli_query($conn, $sql);
$rows = array();
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

echo json_encode($rows);

?>
