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

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch item summary
$item_sql = "SELECT food_type, food_name, SUM(quantity) AS total_quantity, SUM(cost) AS total_cost
             FROM food_wastage
             WHERE user_id = ?
             GROUP BY food_type, food_name";
$item_stmt = $conn->prepare($item_sql);
$item_stmt->bind_param("i", $user_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

// Fetch food wastage summary
$wastage_sql = "SELECT SUM(quantity * (CASE WHEN unit = 'g' THEN 0.001 ELSE 1 END)) AS total_food_wasted,
                       SUM(cost) AS total_cost
                FROM food_wastage
                WHERE user_id = ?";
$wastage_stmt = $conn->prepare($wastage_sql);
$wastage_stmt->bind_param("i", $user_id);
$wastage_stmt->execute();
$wastage_result = $wastage_stmt->get_result();
$wastage_data = $wastage_result->fetch_assoc();

$total_food_wasted = $wastage_data['total_food_wasted'] ?? 0;
$total_cost = $wastage_data['total_cost'] ?? 0;

// Estimate how many children could have been fed
$children_fed = floor($total_food_wasted / 0.5);

// Estimate environmental impact
$environmental_impact = $total_food_wasted * 2.5;

// Start building the HTML content for the report
$html_report = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Food Wastage Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .report-container { margin: 20px; padding: 20px; border: 1px solid #ddd; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        .summary { margin-top: 20px; font-size: 18px; }
    </style>
</head>
<body>
    <div class='report-container'>
        <h1>Food Wastage Detailed Report</h1>
        
        <h2>Item Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Food Type</th>
                    <th>Food Name</th>
                    <th>Total Quantity (kg)</th>
                    <th>Total Cost ($)</th>
                </tr>
            </thead>
            <tbody>";

while ($item = $item_result->fetch_assoc()) {
    $html_report .= "<tr>
                        <td>{$item['food_type']}</td>
                        <td>{$item['food_name']}</td>
                        <td>" . number_format($item['total_quantity'], 2) . "</td>
                        <td>" . number_format($item['total_cost'], 2) . "</td>
                    </tr>";
}

$html_report .= "
            </tbody>
        </table>
        
        <h2>Food Wastage Summary</h2>
        <div class='summary'>
            <p><strong>Total Food Wasted:</strong> " . number_format($total_food_wasted, 2) . " kg</p>
            <p><strong>Total Cost of Wasted Food:</strong> $" . number_format($total_cost, 2) . "</p>
            <p><strong>Meals for Children:</strong> Could have fed {$children_fed} children</p>
            <p><strong>Environmental Impact:</strong> " . number_format($environmental_impact, 2) . " kg CO2 equivalent</p>
        </div>
    </div>
</body>
</html>";

$item_stmt->close();
$wastage_stmt->close();
$conn->close();

// Output the generated HTML report
echo $html_report;
?>
