<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'fyp';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the item ID from the POST data
    $id = $_POST['id'];

    // Validate the ID
    if (!is_numeric($id)) {
        die("Invalid ID.");
    }

    // Prepare the DELETE query
    $stmt = $conn->prepare("DELETE FROM groceries WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect on successful deletion
        header("Location: view_shopping_list.php?message=deleted");
        exit;
    } else {
        // Show error message
        echo "Error deleting item: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
