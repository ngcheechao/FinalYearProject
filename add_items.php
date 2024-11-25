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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    // Insert data into database, including user_id
    $sql = "INSERT INTO groceries (item_name, quantity, price, user_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidi", $item_name, $quantity, $price, $user_id); // "sidi" -> s: string, i: integer, d: double, i: integer

    if ($stmt->execute()) {
        echo "<div style='background: url(\"food_4.jpg\") no-repeat center center; background-size: cover; color: #155724; font-weight: bold; font-size: 18px; padding: 20px; border-radius: 10px; margin-top: 15px; text-align: center;'>
                Item added successfully!
                <br><br>
                <a href='add_items.html' style='display: inline-block; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 16px; margin-top: 10px;'>Add more items</a>
              </div>";
    } else {
        echo "<div style='background: url(\"food_4.jpg\") no-repeat center center; background-size: cover; color: #721c24; font-weight: bold; font-size: 18px; padding: 20px; border-radius: 10px; margin-top: 15px; text-align: center;'>
                Error: " . $stmt->error . "
                <br><br>
                <a href='add_items.html' style='display: inline-block; background-color: #dc3545; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 16px; margin-top: 10px;'>Add more items</a>
              </div>";
    }
    
    

    $stmt->close();
}

// Close the database connection
$conn->close();
?>
