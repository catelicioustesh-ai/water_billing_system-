<?php
include("auth.php");
include("db_connect.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Live Meter Readings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Live Meter Readings (Auto-updating)</h2>

    <table border="1">
    <tr>
    <th>ID</th>
    <th>Customer</th>
    <th>Meter</th>
    <th>Previous</th>
    <th>Current</th>
    <th>Consumption</th>
    <th>Date</th>
    </tr>
    <tbody id="readings-body">
        <tr><td colspan="7">Loading...</td></tr>
    </tbody>
    </table>

</div>

<script src="script.js"></script>
<script>
if (typeof initReadingsLive === 'function') {
    initReadingsLive();
}
</script>

</body>
</html>
