<?php
// Database credentials
$host = "sql100.infinityfree.com";
$user = "if0_41746477";
$pass = "24csAyisha2005";
$dbname = "if0_41746477_expense_tracker";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check if connection works
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>