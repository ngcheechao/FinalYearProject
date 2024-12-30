<?php
$servername = "localhost"; // Usually 'localhost' for local servers like XAMPP
$username = "root";        // Default username for XAMPP is 'root'
$password = "";            // Default password for XAMPP is empty
$dbname = "fyp"; // Replace with the name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
