<?php
include("auth.php");
include("db_connect.php");

header('Content-Type: application/json');

// Get latest reading (by id) for each customer
$sql = "SELECT r.id, r.customer_id, r.current_reading, r.previous_reading, r.consumption, r.reading_date
    FROM meter_readings r
    JOIN (SELECT customer_id, MAX(id) AS mid FROM meter_readings GROUP BY customer_id) m
      ON r.customer_id = m.customer_id AND r.id = m.mid";

$result = mysqli_query($conn, $sql);
$rows = array();
while ($row = mysqli_fetch_assoc($result)) {
  // find the previous entry (if any) for this customer to use as fallback
  $latestId = (int)$row['id'];
  $cust = (int)$row['customer_id'];
  $prev_sql = "SELECT id, current_reading, previous_reading, consumption, reading_date FROM meter_readings WHERE customer_id = '$cust' AND id < $latestId ORDER BY id DESC LIMIT 1";
  $prev_res = mysqli_query($conn, $prev_sql);
  $prev_row = mysqli_fetch_assoc($prev_res);
  if ($prev_row) {
    $row['previous_entry_current'] = $prev_row['current_reading'];
    $row['previous_entry_previous'] = $prev_row['previous_reading'];
    $row['previous_entry_date'] = $prev_row['reading_date'];
  } else {
    $row['previous_entry_current'] = null;
    $row['previous_entry_previous'] = null;
    $row['previous_entry_date'] = null;
  }

  $rows[$row['customer_id']] = $row;
}

echo json_encode($rows);

?>
