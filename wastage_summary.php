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

// Define unit conversion factors based on your provided unit mapping
$unit_conversion = [
    1 => 1,      // Kilogram (kg) stays the same
    2 => 0.001,  // Gram (g) -> kg
    3 => 0.2,    // Pieces -> kg (Now updated to 0.2kg per piece)
    4 => 0.001,  // Millilitre (ml) -> kg (Assumption: for water-based foods)
    5 => 1       // Litre (L) -> kg
];

// Fetch food waste data with unit conversion applied
$sql = "SELECT 
            SUM(quantity * 
                (CASE unit 
                    WHEN 1 THEN 1       -- Kilogram (kg) stays the same
                    WHEN 2 THEN 0.001   -- Gram (g) -> kg
                    WHEN 3 THEN 0.2     -- Pieces -> kg (Updated to 0.2kg per piece)
                    WHEN 4 THEN 0.001   -- Millilitre (ml) -> kg (Assumption)
                    WHEN 5 THEN 1       -- Litre (L) -> kg
                    ELSE 1              -- Default fallback (no conversion)
                END)
            ) AS total_food_wasted,
            SUM(price) AS total_cost
        FROM food_wastage 
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$total_food_wasted = $data['total_food_wasted'] ?? 0;
$total_cost = $data['total_cost'] ?? 0;

// Calculate meals wasted
$calories_per_kg = 2000;
$calories_per_meal = 600;
$total_calories = $total_food_wasted * $calories_per_kg;
// $children_fed = floor($total_calories / $calories_per_meal);

$stmt->close();
$conn->close();

// Send JSON response
header('Content-Type: application/json');
echo json_encode([
    'total_food_wasted' => round($total_food_wasted, 2),
    'total_cost' => round($total_cost, 2),
    // 'children_fed' => $children_fed,
]);
?>
