<?php
require_once('tcpdf/tcpdf.php');
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

// Handle date filtering (custom date range takes precedence over dropdown)
$date_filter = "";
$filter_label = "All Time";
if (isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $date_filter = "AND DATE(`timestamp`) BETWEEN '$start_date' AND '$end_date'";
    $filter_label = "From $start_date to $end_date";
} elseif (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    if ($filter == '1day') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        $filter_label = "Past 1 Day";
    } elseif ($filter == '1week') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
        $filter_label = "Past 1 Week";
    } elseif ($filter == '1month') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $filter_label = "Past 1 Month";
    } elseif ($filter == '1year') {
        $date_filter = "AND `timestamp` >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $filter_label = "Past 1 Year";
    }
}

// Fetch food wastage data based on the filter
$sql = "SELECT DATE(`timestamp`) AS waste_date, item_name, quantity, unit, price 
        FROM food_wastage 
        WHERE user_id = ? $date_filter
        ORDER BY waste_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total summary
$summary_sql = "SELECT SUM(quantity * (CASE WHEN unit = 'g' THEN 0.001 ELSE 1 END)) AS total_food_wasted,
                       SUM(price) AS total_cost
                FROM food_wastage
                WHERE user_id = ? $date_filter";
$summary_stmt = $conn->prepare($summary_sql);
$summary_stmt->bind_param("i", $user_id);
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$summary_data = $summary_result->fetch_assoc();

$total_food_wasted = $summary_data['total_food_wasted'] ?? 0;
$total_cost = $summary_data['total_cost'] ?? 0;

// Initialize PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Food Wastage Report');
$pdf->SetTitle('Food Wastage Report');
$pdf->SetHeaderData('', 0, 'Food Wastage Report', "Filtered by: $filter_label\nGenerated on " . date('Y-m-d'));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// Add Title
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, "Food Wastage Report", 0, 1, 'C');
$pdf->Ln(3);

// Display Filter Timeframe
$pdf->SetFont('helvetica', 'I', 12);
$pdf->Cell(0, 8, "Timeframe: $filter_label", 0, 1, 'C');
$pdf->Ln(3);

// Display Summary
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, "Total Food Wasted: " . number_format($total_food_wasted, 2) . " kg", 0, 1, 'C');
$pdf->Cell(0, 8, "Total Cost: $" . number_format($total_cost, 2), 0, 1, 'C');
$pdf->Ln(5);

// Table Header Styling
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$cellWidth1 = 40;
$cellWidth2 = 50;
$cellWidth3 = 30;
$cellWidth4 = 20;
$cellWidth5 = 30;
$cellHeight = 10;

$pdf->Cell($cellWidth1, $cellHeight, "Date", 1, 0, 'C', 1);
$pdf->Cell($cellWidth2, $cellHeight, "Item", 1, 0, 'C', 1);
$pdf->Cell($cellWidth3, $cellHeight, "Quantity", 1, 0, 'C', 1);
$pdf->Cell($cellWidth4, $cellHeight, "Unit", 1, 0, 'C', 1);
$pdf->Cell($cellWidth5, $cellHeight, "Price ($)", 1, 1, 'C', 1);

// Table Rows
$pdf->SetFont('helvetica', '', 12);
$pdf->SetFillColor(255, 255, 255);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell($cellWidth1, $cellHeight, $row['waste_date'], 1, 0, 'C', 1);
    $pdf->Cell($cellWidth2, $cellHeight, $row['item_name'], 1, 0, 'L', 1);
    $pdf->Cell($cellWidth3, $cellHeight, $row['quantity'], 1, 0, 'C', 1);
    $pdf->Cell($cellWidth4, $cellHeight, $row['unit'], 1, 0, 'C', 1);
    $pdf->Cell($cellWidth5, $cellHeight, number_format($row['price'], 2), 1, 1, 'R', 1);
}

// Output PDF for Download
$pdf->Output("food_wastage_report.pdf", "D");

$stmt->close();
$summary_stmt->close();
$conn->close();
?>
