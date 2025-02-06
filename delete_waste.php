<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json'); // Ensure correct JSON response format

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if deleting all items
    if (isset($_POST["delete_all"])) {
        $stmt = $conn->prepare("DELETE FROM food_wastage WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $success = $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => $success]);
        exit();
    }

    // Check if deleting a single item
    if (isset($_POST["id"])) {
        $waste_id = intval($_POST["id"]);
        if (!$waste_id) {
            echo json_encode(["success" => false, "error" => "Invalid ID"]);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM food_wastage WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $waste_id, $user_id);
        $success = $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => $success]);
        exit();
    }
}

// If no valid request
echo json_encode(["success" => false, "error" => "Invalid request"]);
$conn->close();
?>
