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
$date_filter = "";
$filter_label = "All Time";
$selected_filter = isset($_GET['filter']) ? $_GET['filter'] : '';

if ($selected_filter) {
    switch ($selected_filter) {
        case '1day':
            $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            $filter_label = "Past 1 Day";
            break;
        case '1week':
            $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            $filter_label = "Past 1 Week";
            break;
        case '1month':
            $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            $filter_label = "Past 1 Month";
            break;
        case '5month':
                $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)";
                $filter_label = "Past 5 Month";
                break;
        case '1year':
            $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            $filter_label = "Past 1 Year";
            break;
        case '2year':
                $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)";
                $filter_label = "Past 2 Year";
                break;
    }
}

// Fetch food wastage data
$sql = "SELECT DATE(`timestamp`) AS waste_date, item_name, quantity, unit, price 
        FROM food_wastage 
        WHERE user_id = ? $date_filter
        ORDER BY waste_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$food_wastage_data = [];
while ($row = $result->fetch_assoc()) {
    $food_wastage_data[$row['waste_date']][] = $row;
}

// Fetch data for graph
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Wastage Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .navbar {
            background: linear-gradient(135deg, #14961F, rgb(23, 240, 38));
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            margin-top: 100px;
        }

        .report-container {
            display: flex;
            flex-wrap: wrap;
        }

        .data-section {
            width: 60%;
            padding-right: 20px;
        }

        .chart-section {
            width: 40%;
            text-align: center;
        }

        .day-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .day-title {
            font-weight: bold;
            font-size: 1.2rem;
            color: #14961F;
            margin-bottom: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .data-table th {
            background: #56A575;
            color: white;
        }

    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="user_dashboard.html">
                <img src="logo.png" alt="Logo" width="35"> ⬅ Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-center">Food Wastage Report</h1>

        <form method="GET" action="generate_report.php" class="mb-3">
            <label for="filter">Filter by:</label>
            <select id="filter" name="filter" class="form-select w-25" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="1day" <?= $selected_filter == '1day' ? 'selected' : '' ?>>Past 1 Day</option>
                <option value="1week" <?= $selected_filter == '1week' ? 'selected' : '' ?>>Past 1 Week</option>
                <option value="1month" <?= $selected_filter == '1month' ? 'selected' : '' ?>>Past 1 Month</option>
                <option value="5month" <?= $selected_filter == '5month' ? 'selected' : '' ?>>Past 5 Month</option>
                <option value="1year" <?= $selected_filter == '1year' ? 'selected' : '' ?>>Past 1 Year</option>
                <option value="2year" <?= $selected_filter == '2year' ? 'selected' : '' ?>>Past 2 Year</option>
            </select>
        </form>

        <a href="download_report.php?filter=<?= $selected_filter ?>" class="btn btn-success mb-3">Download Report</a>

        <div class="report-container">
            <div class="data-section">
                <?php foreach ($food_wastage_data as $date => $items): ?>
                    <div class="day-section">
                        <div class="day-title"><?= $date ?></div>
                        <table class="data-table">
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Price ($)</th>
                            </tr>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $item['item_name'] ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= $item['unit'] ?></td>
                                    <td><?= number_format($item['price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="chart-section">
                <h3>Wastage Trend</h3>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: { 
                labels: <?= json_encode(array_column($graph_data, 'date')) ?>, 
                datasets: [{ 
                    label: 'Total Price Wasted ($)', 
                    data: <?= json_encode(array_column($graph_data, 'total_price')) ?>,
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
