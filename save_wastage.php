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

        // 🔹 Step 1: Check if the grocery item exists before inserting
        $sqlCheck = "SELECT * FROM groceries WHERE id = ? AND user_id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $itemId, $user_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $rowCheck = $resultCheck->fetch_assoc();
        $stmtCheck->close();

        if (!$rowCheck) {
            echo "<script>alert('Error: Item not found in groceries!'); window.history.back();</script>";
            exit();
        }

        // 🔹 Step 2: Insert into food_wastage table
        $sqlInsert = "INSERT INTO food_wastage (user_id, category, item_name, quantity, unit, price, reason) 
                      SELECT ?, category, item_name, ?, unit, price * (? / quantity), ? FROM groceries WHERE id = ?";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("isdsi", $user_id, $quantity, $quantity, $reason, $itemId);
        $stmtInsert->execute();
        $stmtInsert->close();

        // 🔹 Step 3: Update or remove item from groceries table
        $currentQuantity = $rowCheck['quantity'];

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

    // ✅ Show a success popup message and redirect to calculate_wastage.html
    echo "<script>
        alert('✅ Food wastage data has been successfully recorded!');
        window.location.href = 'calculate_wastage.html';
    </script>";
    exit();
}
?>
