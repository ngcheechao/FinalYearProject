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

// Handle date filtering
$date_filter = ""; // Default: No date filter
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    if ($filter == '1day') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    } elseif ($filter == '1week') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
    } elseif ($filter == '1month') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    } elseif ($filter == '1year') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }
}

// Fetch individual items wasted, grouped by day
$items_sql = "SELECT DATE(`timestamp`) AS waste_date, item_name, quantity, unit, price 
              FROM food_wastage
              WHERE user_id = ? $date_filter
              ORDER BY waste_date ASC";
$items_stmt = $conn->prepare($items_sql);
if (!$items_stmt) {
    die("Error preparing query: " . $conn->error);
}
$items_stmt->bind_param("i", $user_id);

if (!$items_stmt->execute()) {
    die("Execution error: " . $items_stmt->error);
}

$items_result = $items_stmt->get_result();
if ($items_result->num_rows === 0) {
    echo "No results found for the query.";
}

// Fetch total food wastage summary
$wastage_sql = "SELECT SUM(quantity * (CASE WHEN unit = 'g' THEN 0.001 ELSE 1 END)) AS total_food_wasted,
                       SUM(price) AS total_cost
                FROM food_wastage
                WHERE user_id = ? $date_filter";
$wastage_stmt = $conn->prepare($wastage_sql);
$wastage_stmt->bind_param("i", $user_id);
$wastage_stmt->execute();
$wastage_result = $wastage_stmt->get_result();
$wastage_data = $wastage_result->fetch_assoc();

$total_food_wasted = $wastage_data['total_food_wasted'] ?? 0;
$total_cost = $wastage_data['total_cost'] ?? 0;

// HTML Output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('food_5.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333333;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
            max-width: 900px;
        }
        h1, h2 {
            color: #054A24;
            text-align: center;
        }
        .btn-primary {
            background-color: #56A575;
            border: none;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
            color: #333333;
            text-transform: uppercase;
            font-weight: bold;
        }
        table td {
            color: #333333;
        }
        .summary-title {
            text-align: left;
            font-weight: bold;
        }
        .summary-section {
            margin-top: 30px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generate Food Wastage Report</h1>
        
        <form method="GET" action="generate_report.php">
            <label for="filter">Filter by:</label>
            <select id="filter" name="filter" class="form-select" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="1day" <?= isset($_GET['filter']) && $_GET['filter'] == '1day' ? 'selected' : '' ?>>Past 1 Day</option>
                <option value="1week" <?= isset($_GET['filter']) && $_GET['filter'] == '1week' ? 'selected' : '' ?>>Past 1 Week</option>
                <option value="1month" <?= isset($_GET['filter']) && $_GET['filter'] == '1month' ? 'selected' : '' ?>>Past 1 Month</option>
                <option value="1year" <?= isset($_GET['filter']) && $_GET['filter'] == '1year' ? 'selected' : '' ?>>Past 1 Year</option>
            </select>
        </form>

        <h2>Detailed Report</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Grocery</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Price ($)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['waste_date']) ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['unit']) ?></td>
                        <td>$<?= number_format($row['price'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="summary-section">
            <h3>Summary</h3>
            <p><strong>Total Food Wasted:</strong> <?= number_format($total_food_wasted, 2) ?> kg</p>
            <p><strong>Total Cost:</strong> $<?= number_format($total_cost, 2) ?></p>
        </div>

        <?php if ($total_food_wasted == 0): ?>
            <p class="text-warning text-center">No data available for the selected time frame.</p>
        <?php endif; ?>

        <a href="user_dashboard.html" class="btn btn-primary mt-3">Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close database connection
$items_stmt->close();
$wastage_stmt->close();
$conn->close();
?>
