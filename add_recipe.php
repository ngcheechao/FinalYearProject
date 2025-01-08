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
    // Check if form fields are empty
    if (empty($_POST['recipe_name']) || empty($_POST['ingredients']) || empty($_POST['instructions'])) {
        echo "<p>Please fill in all fields.</p>";
    } else {
        // Sanitize and retrieve form inputs
        $recipe_name = $conn->real_escape_string(trim($_POST['recipe_name']));
        
        // Remove unwanted carriage returns and newlines from ingredients and instructions
        $ingredients = $conn->real_escape_string(trim(str_replace(array("\r", "\n"), ' ', $_POST['ingredients'])));
        $instructions = $conn->real_escape_string(trim(str_replace(array("\r", "\n"), ' ', $_POST['instructions'])));

        // Prepare the SQL statement
        $sql = "INSERT INTO recipes (recipe_name, ingredients, instructions) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }

        // Bind the parameters
        $stmt->bind_param("sss", $recipe_name, $ingredients, $instructions);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            echo "<p>Recipe added successfully!</p>";
        } else {
            echo "<p>Error adding recipe: " . $stmt->error . "</p>";
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection
$conn->close();
?>