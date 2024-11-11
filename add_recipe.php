<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root"; // Default for XAMPP
$password = ""; // Default for XAMPP
$dbname = "fyp"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);   

}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']))   
 {
    $recipe_name = $_POST['recipe_name'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];

    // Insert data into database
    $sql = "INSERT INTO recipes (recipe_name, ingredients, instructions) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $recipe_name, $ingredients, $instructions);

    if ($stmt->execute()) {
        echo "<p>Item added successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>