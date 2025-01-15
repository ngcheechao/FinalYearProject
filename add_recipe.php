<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Initialize feedback message
$feedback = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if form fields are empty
    if (empty($_POST['recipe_name']) || empty($_POST['ingredients']) || empty($_POST['instructions'])) {
        $feedback = "Please fill in all fields.";
    } else {
        // Sanitize and retrieve form inputs
        $recipe_name = $conn->real_escape_string(trim($_POST['recipe_name']));
        $ingredients = $conn->real_escape_string(trim($_POST['ingredients']));
        
        // Process instructions
        $instructions = trim($_POST['instructions']);
        $instructions = str_replace(array("\r", "\n"), '<br>', $instructions);
        $instructions = preg_replace('/\s+/', ' ', $instructions);
        $instructions = $conn->real_escape_string($instructions);

        // Prepare the SQL statement
        $sql = "INSERT INTO recipes (recipe_name, ingredients, instructions) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }

        // Bind and execute
        $stmt->bind_param("sss", $recipe_name, $ingredients, $instructions);
        if ($stmt->execute()) {
            $feedback = "Recipe added successfully!";
        } else {
            $feedback = "Error adding recipe: " . $stmt->error;
        }

        $stmt->close();
    }

    // Redirect with feedback
    echo "<script>
        alert('$feedback');
        window.location.href = 'admin_dashboard.html';
    </script>";
    exit();
}
