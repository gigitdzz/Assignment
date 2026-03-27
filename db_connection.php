<?php
/*
========================================
File: db_connection.php
Version: 1.0
Changes from previous version:
- Initial database connection file created
- Added structured comments for group work reference
========================================
*/

// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$database = "nhs_booking_system";

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>