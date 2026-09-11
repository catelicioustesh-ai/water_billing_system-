<?php
$host = "sql309.infinityfree.com";
$user = "if0_42448807";
$pass = "Terryshii3276";
$db = "if0_42448807_waterbilling";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>