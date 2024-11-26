<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

// Retrieve user_id from session
$user_id = $_SESSION['user_id'];

// Database connection settings
$host = 'localhost'; // Change if necessary
$username = 'root'; // Change if necessary
$password = ''; // Change if necessary
$database = 'fyp'; // Replace with your actual database name

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if (isset($_POST['submit'])) {
    // Retrieve form data
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit']; // Numeric unit value from the form
    $price = $_POST['price'];

    // Validate form inputs
    if (empty($item_name) || empty($category) || empty($quantity) || empty($unit) || empty($price)) {
        die("<p>Error: All fields are required! <a href='add_items.html'>Go back</a></p>");
    }

    if (!is_numeric($quantity) || !is_numeric($price)) {
        die("<p>Error: Quantity and Price must be numbers! <a href='add_items.html'>Go back</a></p>");
    }

    // Insert the data into the database using a prepared statement
    $stmt = $conn->prepare("INSERT INTO groceries (item_name, quantity, price, unit, user_id)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsii", $item_name, $quantity, $price, $unit, $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "
        <div style='
            max-width: 400px; 
            margin: 20px auto; 
            padding: 20px; 
            border: 1px solid #b3ffb3; 
            border-radius: 10px; 
            background-color: #e6ffe6; 
            font-family: Arial, sans-serif; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);'>
            <h3 style='color: green; margin-top: 0;'>Item Added Successfully!</h3>
            <p style='margin: 10px 0;'>Your item has been added to the purchased list.</p>
            <a href='view_shopping_list.php' style='
                display: inline-block; 
                padding: 8px 15px; 
                color: white; 
                background-color: #006600; 
                text-decoration: none; 
                border-radius: 5px; 
                font-size: 14px;'>View Items</a>
        </div>";
    } else {
        echo "<p style='color: red; font-size: 16px; font-family: Arial, sans-serif;'>
                Error: " . $stmt->error . "
              </p>";
    }
    
    

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
