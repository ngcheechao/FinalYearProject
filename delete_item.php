<?php
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

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    // Get the ID from the URL and sanitize it
    $id = intval($_GET['id']); // Convert to integer to prevent SQL injection

    // Prepare the SQL delete statement
    $sql = "DELETE FROM groceries WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        echo "Item deleted successfully.";
    } else {
        echo "Error deleting item: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid item ID.";
}

// Close the connection
$conn->close();

// Redirect back to the shopping list page
header("Location: view_shopping_list.php");
exit();
?>
