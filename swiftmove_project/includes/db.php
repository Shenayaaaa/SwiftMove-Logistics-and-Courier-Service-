<?php

// Start session if it hasn't already been started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration settings
$db_host = 'localhost';     // Database host
$db_user = 'root';          // Database username (adjust as needed)
$db_pass = '';              // Database password (adjust as needed)
$db_name = 'swiftmove_db';  // Database name

// Create a new MySQLi connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to support international characters (optional)
$conn->set_charset("utf8mb4");

// Now your connection is ready to use, and sessions are started.
