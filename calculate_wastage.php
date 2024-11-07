<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Database connection details
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "fyp"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs and sanitize them
    $foodType = $conn->real_escape_string($_POST['foodType']);
    $foodName = $conn->real_escape_string($_POST['foodName']);
    $quantity = $conn->real_escape_string($_POST['quantity']);
    $cost = $conn->real_escape_string($_POST['cost']);

    // Prepare SQL insert statement
    $sql = "INSERT INTO food_wastage (user_id, food_type, food_name, quantity, cost) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssd", $user_id, $foodType, $foodName, $quantity, $cost);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        echo "Wastage data added successfully.";
    } else {
        echo "Error adding wastage data: " . $conn->error;
    }

    // Close the statement
    $stmt->close();
} else {
    echo "Invalid request.";
}

// Close the connection
$conn->close();

// Redirect back to the form page (or another page as desired)
header("Location: calculate_wastage.html");
exit();
?>
