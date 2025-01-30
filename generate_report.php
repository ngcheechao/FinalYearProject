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

        .navbar-nav {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .navbar-nav .nav-item {
            margin: 0 8px;
        }

        .navbar-nav .nav-link {
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 10px 15px;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .container {
            margin-top: 100px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="user_dashboard.php">
                <img src="logo.png" alt="Logo" width="35"> ⬅ Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="add_items.html">Add Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_shopping_list.php">Shopping List</a></li>
                    <li class="nav-item"><a class="nav-link" href="recipe_manage.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="cook.php">Cook</a></li>
                    <li class="nav-item"><a class="nav-link" href="calculate_wastage.html">Waste Impact</a></li>
                    <li class="nav-item"><a class="nav-link active" href="generate_report.php">Reports</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
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

        <a href="download_report.php" class="btn btn-success mb-3">Download as PDF</a>

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
$wastage_stmt->close();
$conn->close();
?>
