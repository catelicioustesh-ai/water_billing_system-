<?php
include("auth.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Meter Reading Entry</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">
    <a href="dashboard.php" class="back-arrow">← Back to Dashboard</a>
    <h2>Meter Reading Entry</h2>

    <form action="save_reading.php" method="POST">

Customer ID:
<input type="number" name="customer_id">

<br><br>

Previous Reading:
<input type="number" name="previous_reading">

<br><br>

Current Reading:
<input type="number" name="current_reading">

<br><br>

<input type="submit" value="Save Reading">

</form>
</div>

</body>
</html>