<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$calories_per_kg = 2000; // Average calories in 1 kg of food
$calories_per_meal = 600; // Caloric requirement per meal
$total_calories = $total_food_wasted * $calories_per_kg;
$children_fed = floor($total_calories / $calories_per_meal);

// Calculate the environmental impact based on food category averages
$sql = "SELECT 
            food_type, 
            SUM(quantity * (CASE WHEN unit = 'g' THEN 0.001 ELSE 1 END)) AS total_quantity
        FROM food_wastage 
        WHERE user_id = ?
        GROUP BY food_type";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$carbon_footprint = [
    'meat' => 33.17, // kg CO2e per kg
    'vegetable' => 0.84,
    'fruit' => 0.85,
    'dairy' => 13.52
];

$environmental_impact = 0;

while ($row = $result->fetch_assoc()) {
    $type = strtolower($row['food_type']); // Normalize to lowercase
    $quantity = $row['total_quantity'];
    $impact_per_kg = $carbon_footprint[$type] ?? 0.5; // Default to 0.5 kg CO2e if type is unknown
    error_log("Processing food type: $type, quantity: $quantity, impact_per_kg: $impact_per_kg");
    $environmental_impact += $quantity * $impact_per_kg;
}

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
?>
