<?php
$host = 'localhost'; //server address
$user = 'root';
$pass = ''; 
$db   = 'im102_lab2_salomon'; //db name

$conn = new mysqli($host, $user, $pass, $db);

//check for errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//set characters encoding
$conn->set_charset("utf8mb4");
?>