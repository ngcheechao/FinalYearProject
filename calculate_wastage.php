<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categories = $_POST['category'];
    $items = $_POST['item'];
    $quantities = $_POST['quantity'];
    $units = $_POST['unit'];
    $prices = $_POST['price'];

    $sql = "INSERT INTO food_wastage (user_id, category, item_name, quantity, unit, price) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    for ($i = 0; $i < count($items); $i++) {
        $category = $conn->real_escape_string($categories[$i]);
        $item = $conn->real_escape_string($items[$i]);
        $quantity = floatval($quantities[$i]);
        $unit = $conn->real_escape_string($units[$i]);
        $price = floatval($prices[$i]);

        $stmt->bind_param("issdss", $user_id, $category, $item, $quantity, $unit, $price);

        if (!$stmt->execute()) {
            echo "Error adding wastage data: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();

    header("Location: calculate_wastage.html");
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    exit();
}
?>