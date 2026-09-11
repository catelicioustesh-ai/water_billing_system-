<?php
include("auth.php");
include("db_connect.php");

// Simple Server-Sent Events stream that notifies clients of new meter_readings
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
set_time_limit(0);
ignore_user_abort(false);

$lastId = 0;
if (!empty($_SERVER["HTTP_LAST_EVENT_ID"])) {
    $lastId = intval($_SERVER["HTTP_LAST_EVENT_ID"]);
} elseif (!empty($_GET['last_id'])) {
    $lastId = intval($_GET['last_id']);
}

while (true) {
    // get the max id currently
    $res = mysqli_query($conn, "SELECT MAX(id) AS mid FROM meter_readings");
    $r = mysqli_fetch_assoc($res);
    $mid = intval($r['mid']);
    if ($mid > $lastId) {
        // fetch the new rows greater than lastId
        $sql = "SELECT r.*, c.customer_name, c.meter_number FROM meter_readings r LEFT JOIN customers c ON c.id = r.customer_id WHERE r.id > $lastId ORDER BY r.id ASC";
        $newRes = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($newRes)) {
            $lastId = intval($row['id']);
            $payload = json_encode($row);
            echo "id: {$lastId}\n";
            echo "data: {$payload}\n\n";
            @ob_flush();
            @flush();
        }
    }

    // heartbeat to keep connection alive
    echo ": heartbeat\n\n";
    @ob_flush();
    @flush();

    // sleep a short while
    sleep(2);

    if (connection_aborted()) break;
}

?>
