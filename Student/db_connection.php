<?php
// Database connection parameters
$host = '127.0.0.1:3307';
$dbname = 'student_mentoring_system';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Connection successful
?>
