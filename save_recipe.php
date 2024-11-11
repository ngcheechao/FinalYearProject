<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form inputs
    $recipe_name = $conn->real_escape_string($_POST['recipe_name']);
    $ingredients = $conn->real_escape_string($_POST['ingredients']);
    $instructions = $conn->real_escape_string($_POST['instructions']);
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    // Check if the user_id exists in the users table (optional but recommended)
    $user_check_query = "SELECT id FROM users WHERE id = ?";
    $user_check_stmt = $conn->prepare($user_check_query);
    $user_check_stmt->bind_param("i", $user_id);
    $user_check_stmt->execute();
    $user_check_result = $user_check_stmt->get_result();

    if ($user_check_result->num_rows == 0) {
        echo "<p>Error: Invalid user ID. Please log in again.</p>";
    } else {
        // Prepare the SQL statement to insert the recipe data
        $sql = "INSERT INTO recipes (recipe_name, ingredients, instructions, user_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $recipe_name, $ingredients, $instructions, $user_id);

        // Execute the statement and check if successful
        if ($stmt->execute()) {
            echo "<p>Recipe added successfully!</p>";
        } else {
            echo "<p>Error adding recipe: " . $stmt->error . "</p>";
        }

        // Close the statement
        $stmt->close();
    }

    // Close the user check statement
    $user_check_stmt->close();
}

// Close the connection
$conn->close();
?>
