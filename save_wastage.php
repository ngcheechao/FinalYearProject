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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $foodTypes = $_POST['foodType'];
    $groceryItems = $_POST['groceryItem'];
    $quantities = $_POST['quantity'];
    $reasons = $_POST['reason'];

    foreach ($groceryItems as $index => $itemId) {
        $quantity = $quantities[$index];
        $reason = $reasons[$index];

        // Insert into food_wastage table
        $sqlInsert = "INSERT INTO food_wastage (user_id, category, item_name, quantity, unit, price, reason) 
                      SELECT ?, category, item_name, ?, unit, price * (? / quantity), ? FROM groceries WHERE id = ?";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("isdsi", $user_id, $quantity, $quantity, $reason, $itemId);
        $stmtInsert->execute();
        $stmtInsert->close();

        // Update or remove item from groceries table
        $sqlSelect = "SELECT quantity FROM groceries WHERE id = ?";
        $stmtSelect = $conn->prepare($sqlSelect);
        $stmtSelect->bind_param("i", $itemId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();
        $row = $result->fetch_assoc();
        $currentQuantity = $row['quantity'];
        $stmtSelect->close();

        if ($currentQuantity > $quantity) {
            $newQuantity = $currentQuantity - $quantity;
            $sqlUpdate = "UPDATE groceries SET quantity = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("di", $newQuantity, $itemId);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        } else {
            $sqlDelete = "DELETE FROM groceries WHERE id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $itemId);
            $stmtDelete->execute();
            $stmtDelete->close();
        }
    }

    $conn->close();
    $_SESSION['feedback'] = "Food wastage data has been successfully recorded.";
    header("Location: calculate_wastage.html");
    exit();
}
?>