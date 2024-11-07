<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Get login form data
$email = $_POST['email'];
$pass = $_POST['password']; // Plain text comparison

// Check if the user exists in the database
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Directly compare plain text passwords
    if ($pass === $row['password']) {  // Remove password_verify and use direct comparison
        // Store user ID in session
        $_SESSION['user_id'] = $row['id'];

        // Check if the user is an admin
        if ($row['is_admin'] == 1) {
            header("Location: admin_dashboard.html");
        } else {
            header("Location: user_dashboard.html");
        }
        exit();
    } else {
        echo "Invalid password. Please try again.";
    }
} else {
    echo "No account found with that email.";
}

$conn->close();
?>
