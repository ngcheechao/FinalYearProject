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
    $foodTypes = $_POST['foodType'];
    $foodNames = $_POST['foodName'];
    $quantities = $_POST['quantity'];
    $units = $_POST['unit'];
    $costs = $_POST['cost'];

    $sql = "INSERT INTO food_wastage (user_id, food_type, food_name, quantity, unit, cost) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    for ($i = 0; $i < count($foodTypes); $i++) {
        $foodType = $conn->real_escape_string($foodTypes[$i]);
        $foodName = $conn->real_escape_string($foodNames[$i]);
        $quantity = floatval($quantities[$i]);
        $unit = $conn->real_escape_string($units[$i]);
        $cost = floatval($costs[$i]);

        $stmt->bind_param("issdss", $user_id, $foodType, $foodName, $quantity, $unit, $cost);

        if (!$stmt->execute()) {
            echo "Error adding wastage data: " . $stmt->error;
        }
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();

header("Location: calculate_wastage.html");
exit();
?>
