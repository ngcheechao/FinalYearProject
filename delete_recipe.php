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

// Handle form submission for deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $recipe_id = $_POST['recipe_id'];

    // Delete the recipe from the database
    $sql = "DELETE FROM recipes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recipe_id);

    if ($stmt->execute()) {
        echo "<p>Recipe deleted successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>