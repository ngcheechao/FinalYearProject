<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Unit conversion array
$unitMap = [
    1 => "(kg)",
    2 => "(g)",
    3 => "Pieces",
    4 => "(ml)",
    5 => "(L)"
];

// Get last 5 notifications (both manual & expired food wastage)
$sql = "SELECT item_name, quantity, unit, reason, timestamp FROM food_wastage ORDER BY timestamp DESC LIMIT 5";
$result = $conn->query($sql);
$notifications = [];

while ($row = $result->fetch_assoc()) {
    // Replace unit number with actual unit name
    $unitText = isset($unitMap[$row['unit']]) ? $unitMap[$row['unit']] : "Unknown Unit";
    
    $notifications[] = [
        "item_name" => $row['item_name'],
        "quantity" => $row['quantity'],
        "unit" => $unitText,
        "reason" => $row['reason'],
        "timestamp" => $row['timestamp']
    ];
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($notifications);
?>
