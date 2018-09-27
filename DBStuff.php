<?php
date_default_timezone_set("Australia/Brisbane");

$servername = "127.0.0.1";
$username = "interact_roll_user";
$password = "interact_roll_user";
$dbname = "interact_roll";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>