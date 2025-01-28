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
$items_stmt->bind_param("i", $user_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Fetch data for graphs
$graph_sql = "SELECT DATE(`timestamp`) AS waste_date, SUM(price) AS total_price
              FROM food_wastage
              WHERE user_id = ? $date_filter
              GROUP BY waste_date
              ORDER BY waste_date ASC";
$graph_stmt = $conn->prepare($graph_sql);
$graph_stmt->bind_param("i", $user_id);
$graph_stmt->execute();
$graph_result = $graph_stmt->get_result();

$graph_data = [];
while ($row = $graph_result->fetch_assoc()) {
    $graph_data[] = [
        'date' => $row['waste_date'],
        'total_price' => $row['total_price']
    ];
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="user_dashboard.html" class="btn btn-primary">Back to Dashboard</a>
        <a href="download_report.php" class="btn btn-success">Download as PDF</a>
        <h1 class="text-center">Food Wastage Report</h1>

        <form method="GET" action="generate_report.php" class="mb-3">
            <label for="filter">Filter by:</label>
            <select id="filter" name="filter" class="form-select w-25" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="1day" <?= isset($_GET['filter']) && $_GET['filter'] == '1day' ? 'selected' : '' ?>>Past 1 Day</option>
                <option value="1week" <?= isset($_GET['filter']) && $_GET['filter'] == '1week' ? 'selected' : '' ?>>Past 1 Week</option>
                <option value="1month" <?= isset($_GET['filter']) && $_GET['filter'] == '1month' ? 'selected' : '' ?>>Past 1 Month</option>
                <option value="1year" <?= isset($_GET['filter']) && $_GET['filter'] == '1year' ? 'selected' : '' ?>>Past 1 Year</option>
            </select>
        </form>

        <h3>Summary</h3>
        <p><strong>Total Food Wasted:</strong> <?= number_format($total_food_wasted, 2) ?> kg</p>
        <p><strong>Total Cost:</strong> $<?= number_format($total_cost, 2) ?></p>

        <h3>Detailed Report</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Item</th>
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

        <h3>Wastage Trend</h3>
        <canvas id="barChart"></canvas>
    </div>

    <script>
        const barCtx = document.getElementById('barChart').getContext('2d');
        const chartData = <?= json_encode($graph_data) ?>;
        const labels = chartData.map(data => data.date);
        const totalPrices = chartData.map(data => data.total_price);

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Price Wasted ($)',
                    data: totalPrices,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>

<?php
$items_stmt->close();
$wastage_stmt->close();
$conn->close();
?>
