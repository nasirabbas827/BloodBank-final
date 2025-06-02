<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "", "bloodbank_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>