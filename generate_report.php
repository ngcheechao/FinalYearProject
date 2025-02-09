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

// Determine date filter (custom date range takes precedence)
$date_filter = "";
$filter_label = "All Time";
$selected_filter = '';
$start_date = "";
$end_date = "";
if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $date_filter = "AND DATE(`timestamp`) BETWEEN '$start_date' AND '$end_date'";
    $filter_label = "From $start_date to $end_date";
} elseif (isset($_GET['filter']) && !empty($_GET['filter'])) {
    $selected_filter = $_GET['filter'];
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
} else {
    $selected_filter = '';
}

// Build query string for the download link
$download_query = "";
if (!empty($start_date) && !empty($end_date)) {
    $download_query = "start_date=" . urlencode($start_date) . "&end_date=" . urlencode($end_date);
} elseif (!empty($selected_filter)) {
    $download_query = "filter=" . urlencode($selected_filter);
}

// Unit mapping
$unit_mapping = [
    1 => "Kilogram",
    2 => "Gram",
    3 => "Pieces",
    4 => "Millilitre",
    5 => "Litre"
];

// Fetch food wastage data for table (newest first)
$sql = "SELECT DATE(`timestamp`) AS waste_date, item_name, category, quantity, unit, price 
        FROM food_wastage 
        WHERE user_id = ? $date_filter
        ORDER BY waste_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$food_wastage_data = [];
while ($row = $result->fetch_assoc()) {
    $row['unit'] = $unit_mapping[$row['unit']] ?? "Unknown";
    $food_wastage_data[$row['waste_date']][] = $row;
}

// Fetch graph data for cost (ordered ascending for time series)
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
    $graph_data[] = ['date' => $row['waste_date'], 'total_price' => $row['total_price']];
}

// Additional Summary Metrics
$summary_qty_sql = "SELECT SUM(
    CASE 
        WHEN unit = 1 THEN quantity 
        WHEN unit = 2 THEN quantity/1000 
        ELSE 0 
    END
) as total_food_wasted_kg
FROM food_wastage
WHERE user_id = ? $date_filter";
$qty_stmt = $conn->prepare($summary_qty_sql);
$qty_stmt->bind_param("i", $user_id);
$qty_stmt->execute();
$qty_result = $qty_stmt->get_result();
$qty_data = $qty_result->fetch_assoc();
$total_food_wasted_kg = $qty_data['total_food_wasted_kg'] ?? 0;

$count_sql = "SELECT COUNT(*) as total_items FROM food_wastage WHERE user_id = ? $date_filter";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_data = $count_result->fetch_assoc();
$total_items = $count_data['total_items'] ?? 0;

$most_wasted_sql = "SELECT item_name, SUM(price) as total_item_cost
                     FROM food_wastage
                     WHERE user_id = ? $date_filter
                     GROUP BY item_name
                     ORDER BY total_item_cost DESC
                     LIMIT 1";
$most_stmt = $conn->prepare($most_wasted_sql);
$most_stmt->bind_param("i", $user_id);
$most_stmt->execute();
$most_result = $most_stmt->get_result();
$most_wasted_data = $most_result->fetch_assoc();
$most_wasted_item = $most_wasted_data['item_name'] ?? 'N/A';
$most_wasted_cost = $most_wasted_data['total_item_cost'] ?? 0;

$cost_sql = "SELECT SUM(price) as total_cost FROM food_wastage WHERE user_id = ? $date_filter";
$cost_stmt = $conn->prepare($cost_sql);
$cost_stmt->bind_param("i", $user_id);
$cost_stmt->execute();
$cost_result = $cost_stmt->get_result();
$cost_data = $cost_result->fetch_assoc();
$total_cost = $cost_data['total_cost'] ?? 0;

$avg_price = ($total_items > 0) ? ($total_cost / $total_items) : 0;
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
      body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
      .navbar { background: linear-gradient(135deg, #14961F, rgb(23, 240, 38)); position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.3); padding: 10px 20px; }
      .navbar-brand { font-size: 1.5rem; font-weight: bold; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px; }
      .navbar-nav { display: flex; flex-direction: row; gap: 15px; list-style: none; margin: 0 auto; padding: 0; }
      .nav-link { color: #fff; font-size: 1.1rem; font-weight: bold; padding: 10px 15px; transition: all 0.3s ease-in-out; border-radius: 5px; text-decoration: none; }
      .nav-link:hover { background: rgba(255,255,255,0.3); transform: scale(1.05); }
      .container { margin-top: 100px; margin-bottom: 50px; }
      .report-container { display: flex; flex-wrap: wrap; gap: 20px; }
      #data-section { width: 100% !important; }
      #graph-section { width: 100%; }
      @media(min-width: 992px) {
          .data-section { width: 60%; }
          .chart-section { width: 40%; }
          #graph-section .chart-section { width: 100% !important; }
      }
      .day-section { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
      .day-title { font-weight: bold; font-size: 1.2rem; color: #14961F; margin-bottom: 15px; }
      .data-table { width: 100%; border-collapse: collapse; }
      .data-table th, .data-table td { padding: 12px; border: 1px solid #ddd; text-align: left; }
      .data-table th { background: #56A575; color: #fff; }
      .data-table tbody tr:hover { background-color: #f1f1f1; }
      .btn-toggle { margin-right: 10px; }
      .chart-section { height: 400px; }
      #barChart { width: 100% !important; height: 100% !important; }
      /* Style for error messages */
      .error-message {
          color: red;
          font-weight: bold;
          margin-top: 10px;
          display: none;
      }
  </style>
</head>
<body>
  <nav class="navbar">
      <div class="container-fluid d-flex justify-content-between align-items-center">
          <a class="navbar-brand" href="user_dashboard.html">
              <img src="logo.png" alt="Logo" width="35"> ⬅️Dashboard
          </a>
          <ul class="navbar-nav">
              <li class="nav-item"><a class="nav-link" href="add_items.html">Add Items</a></li>
              <li class="nav-item"><a class="nav-link" href="view_shopping_list.php">Shopping List</a></li>
              <li class="nav-item"><a class="nav-link" href="recipe_manage.php">Recipes</a></li>
              <li class="nav-item"><a class="nav-link" href="cook.php">Cook</a></li>
              <li class="nav-item"><a class="nav-link" href="calculate_wastage.html">Waste Impact</a></li>
              <li class="nav-item"><a class="nav-link" href="generate_report.php">Reports</a></li>
          </ul>
      </div>
  </nav>

  <div class="container">
      <!-- This container will be updated if the PDF generation returns an error -->
      <div id="error-container"></div>

      <div class="mb-4 d-flex justify-content-between align-items-center">
          <div>
              <button class="btn btn-primary btn-toggle" onclick="showSection('data-section')">View Data</button>
              <button class="btn btn-secondary btn-toggle" onclick="showSection('graph-section')">View Graph</button>
          </div>
          <div>
              <!-- Notice the target="_blank" so the download opens in a new window -->
              <a href="download_report.php?<?= $download_query ?>" class="btn btn-success" target="_blank">Download Report</a>
          </div>
      </div>

      <h1 class="text-center my-4">Food Wastage Report</h1>
      
      <div class="mb-4">
          <form method="GET" action="generate_report.php" class="d-flex align-items-center" onsubmit="return validateDates();">
              <label for="filter" class="me-2">Filter by:</label>
              <select id="filter" name="filter" class="form-select w-auto me-2" onchange="this.form.submit()">
                  <option value="">All Time</option>
                  <option value="1day" <?= (isset($selected_filter) && $selected_filter=='1day') ? 'selected' : '' ?>>Past 1 Day</option>
                  <option value="1week" <?= (isset($selected_filter) && $selected_filter=='1week') ? 'selected' : '' ?>>Past 1 Week</option>
                  <option value="1month" <?= (isset($selected_filter) && $selected_filter=='1month') ? 'selected' : '' ?>>Past 1 Month</option>
                  <option value="5month" <?= (isset($selected_filter) && $selected_filter=='5month') ? 'selected' : '' ?>>Past 5 Month</option>
                  <option value="1year" <?= (isset($selected_filter) && $selected_filter=='1year') ? 'selected' : '' ?>>Past 1 Year</option>
                  <option value="2year" <?= (isset($selected_filter) && $selected_filter=='2year') ? 'selected' : '' ?>>Past 2 Year</option>
              </select>
              <label for="start_date" class="me-2">Start Date:</label>
              <input type="date" id="start_date" name="start_date" class="form-control w-auto me-2">
              <label for="end_date" class="me-2">End Date:</label>
              <input type="date" id="end_date" name="end_date" class="form-control w-auto">
              <button type="submit" class="btn btn-outline-primary ms-2">Apply</button>
          </form>
          <!-- Date error message (if only one date is selected) -->
          <div id="dateError" class="error-message">Please select both start date and end date.</div>
      </div>
      
      <div class="mb-4">
          <div class="alert alert-info">
              <strong>Summary:</strong>
              Total Cost: $<?= number_format($total_cost, 2) ?> |
              Total Items: <?= $total_items ?> |
              Average Price per Item: $<?= number_format($avg_price, 2) ?> |
              Most Wasted Item: <?= htmlspecialchars($most_wasted_item) ?> ($<?= number_format($most_wasted_cost, 2) ?>)
          </div>
      </div>
      
      <div class="report-container">
          <div id="data-section" class="content-container">
              <?php if (!empty($food_wastage_data)): ?>
                  <?php foreach ($food_wastage_data as $date => $items): ?>
                      <?php 
                      $day_total = 0;
                      foreach ($items as $item) {
                          $day_total += $item['price'];
                      }
                      ?>
                      <div class="day-section">
                          <div class="day-title"><?= $date ?></div>
                          <table class="data-table">
                              <thead>
                                  <tr>
                                      <th>Item</th>
                                      <th>Category</th>
                                      <th>Quantity</th>
                                      <th>Unit</th>
                                      <th>Price ($)</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php foreach ($items as $item): ?>
                                      <tr>
                                          <td><?= htmlspecialchars($item['item_name']) ?></td>
                                          <td><?= htmlspecialchars($item['category'] ?? 'N/A') ?></td>
                                          <td><?= htmlspecialchars($item['quantity']) ?></td>
                                          <td><?= htmlspecialchars($item['unit']) ?></td>
                                          <td><?= number_format($item['price'], 2) ?></td>
                                      </tr>
                                  <?php endforeach; ?>
                              </tbody>
                              <tfoot>
                                  <tr>
                                      <td colspan="4" class="text-end"><strong>Total Price:</strong></td>
                                      <td><strong><?= number_format($day_total, 2) ?></strong></td>
                                  </tr>
                              </tfoot>
                          </table>
                      </div>
                  <?php endforeach; ?>
              <?php else: ?>
                  <p class="text-center">No data available for the selected filter.</p>
              <?php endif; ?>
          </div>
          <div id="graph-section" class="content-container" style="display: none;">
              <div class="chart-section">
                  <h3 class="text-center mb-3">Wastage Trend</h3>
                  <!-- Only cost option available -->
                  <div class="mb-3 d-flex justify-content-center">
                      <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="graphToggle" id="graphCost" value="cost" checked>
                          <label class="form-check-label" for="graphCost">Cost ($)</label>
                      </div>
                  </div>
                  <canvas id="barChart"></canvas>
              </div>
          </div>
      </div>
  </div>

  <!-- JavaScript at the bottom -->
  <script>
      // Validate that both start and end dates are selected
      function validateDates() {
          var start = document.getElementById("start_date").value;
          var end = document.getElementById("end_date").value;
          var errorDiv = document.getElementById("dateError");
          if ((start && !end) || (!start && end)) {
              errorDiv.style.display = "block";
              return false;
          }
          errorDiv.style.display = "none";
          return true;
      }

      // Define showSection in global scope so inline onclick attributes can access it.
      window.showSection = function(id) {
          document.querySelectorAll('.content-container').forEach(function(el) {
              el.style.display = 'none';
          });
          document.getElementById(id).style.display = 'block';
          if (id === 'graph-section' && typeof barChart !== 'undefined') {
              setTimeout(function() {
                  barChart.resize();
                  barChart.update();
              }, 100);
          }
      };

      var barChart;
      const costData = <?= json_encode(array_column($graph_data, 'total_price')) ?>;
      const labels = <?= json_encode(array_column($graph_data, 'date')) ?>;
      
      function createChart(dataset, labelText, yAxisLabel) {
          if (barChart) {
              barChart.data.datasets[0].data = dataset;
              barChart.data.datasets[0].label = labelText;
              barChart.options.scales.y.title.text = yAxisLabel;
              barChart.update();
          } else {
              const ctx = document.getElementById('barChart').getContext('2d');
              barChart = new Chart(ctx, {
                  type: 'line',
                  data: {
                      labels: labels,
                      datasets: [{
                          label: labelText,
                          data: dataset,
                          backgroundColor: 'rgba(75, 192, 192, 0.3)',
                          borderColor: 'rgba(75, 192, 192, 1)',
                          borderWidth: 2,
                          fill: true
                      }]
                  },
                  options: {
                      responsive: true,
                      maintainAspectRatio: false,
                      scales: {
                          y: {
                              beginAtZero: true,
                              title: { display: true, text: yAxisLabel }
                          }
                      }
                  }
              });
          }
      }
      
      // Create chart with cost data by default.
      createChart(costData, 'Total Price Wasted ($)', 'Cost ($)');

      // Since only cost option is available, the toggle event listener is optional.
      document.querySelectorAll('input[name="graphToggle"]').forEach(function(radio) {
          radio.addEventListener('change', function() {
              if (this.value === 'cost') {
                  createChart(costData, 'Total Price Wasted ($)', 'Cost ($)');
              }
          });
      });
  </script>
</body>
</html>
