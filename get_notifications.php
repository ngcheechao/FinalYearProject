<?php
session_start(); // Ensure session is started

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id']; 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}


$sql = "SELECT item_name, quantity, unit, reason, timestamp 
        FROM food_wastage 
        WHERE user_id = ?
        ORDER BY timestamp DESC 
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    // Convert numerical unit to readable format
    $unitNames = ["Unknown", "Kilogram (kg)", "Gram (g)", "Pieces", "Millilitre (ml)", "Litre (L)"];
    $unitIndex = (int)$row['unit'];
    $row['unit'] = isset($unitNames[$unitIndex]) ? $unitNames[$unitIndex] : "Unknown Unit";
    
    $notifications[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($notifications);
?>
