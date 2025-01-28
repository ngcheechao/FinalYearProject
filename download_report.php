<?php
require_once('tcpdf/tcpdf.php');session_start();

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

// Fetch food wastage data
$sql = "SELECT DATE(`timestamp`) AS waste_date, item_name, quantity, unit, price 
        FROM food_wastage 
        WHERE user_id = ? 
        ORDER BY waste_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total summary
$summary_sql = "SELECT SUM(quantity * (CASE WHEN unit = 'g' THEN 0.001 ELSE 1 END)) AS total_food_wasted,
                       SUM(price) AS total_cost
                FROM food_wastage
                WHERE user_id = ?";
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
$pdf->SetHeaderData('', 0, 'Food Wastage Report', 'Generated on ' . date('Y-m-d'));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// Add title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, "Food Wastage Report", 0, 1, 'C');
$pdf->Ln(5);

// Summary
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, "Total Food Wasted: " . number_format($total_food_wasted, 2) . " kg", 0, 1);
$pdf->Cell(0, 8, "Total Cost: $" . number_format($total_cost, 2), 0, 1);
$pdf->Ln(5);

// Table headers
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, "Date", 1);
$pdf->Cell(50, 10, "Item", 1);
$pdf->Cell(30, 10, "Quantity", 1);
$pdf->Cell(20, 10, "Unit", 1);
$pdf->Cell(30, 10, "Price ($)", 1);
$pdf->Ln();

// Table rows
$pdf->SetFont('helvetica', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(40, 10, $row['waste_date'], 1);
    $pdf->Cell(50, 10, $row['item_name'], 1);
    $pdf->Cell(30, 10, $row['quantity'], 1);
    $pdf->Cell(20, 10, $row['unit'], 1);
    $pdf->Cell(30, 10, number_format($row['price'], 2), 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output("food_wastage_report.pdf", "D");

$stmt->close();
$summary_stmt->close();
$conn->close();
?>
