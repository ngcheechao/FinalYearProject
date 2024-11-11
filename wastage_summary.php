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

// Query to get total food wasted and total cost
$sql = "SELECT 
            SUM(quantity * (CASE WHEN unit = 'g' THEN 0.001 ELSE 1 END)) AS total_food_wasted,
            SUM(cost) AS total_cost
        FROM food_wastage 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$total_food_wasted = $data['total_food_wasted'] ?? 0;
$total_cost = $data['total_cost'] ?? 0;

// Estimate how many children could have been fed
$children_fed = floor($total_food_wasted / 0.5);

// Estimate the environmental impact of the food wastage
$environmental_impact = $total_food_wasted * 2.5;

$stmt->close();
$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'total_food_wasted' => round($total_food_wasted, 2),
    'total_cost' => round($total_cost, 2),
    'children_fed' => $children_fed,
    'environmental_impact' => round($environmental_impact, 2),
]);
