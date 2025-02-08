<?php
header('Content-Type: application/json'); // Set response type to JSON

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get form data
$user = $_POST['username'];
$email = $_POST['email'];
$pass = $_POST['password']; // Plain password (consider hashing later)
$role = $_POST['role']; 
$is_admin = ($role === "Admin") ? 1 : 0;

// Backend Password Validation: At least 8 characters
if (strlen($pass) < 8) {
    echo json_encode(["status" => "error", "message" => "⚠️ Password must be at least 8 characters long!"]);
    exit();
}

// Check if the email already exists
$check_email = "SELECT email FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "⚠️ Email already registered!"]);
    exit();
}

// Insert new user if email is unique
$stmt->close();
$sql = "INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $user, $email, $pass, $is_admin);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "🎉 User created successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error creating user: " . $conn->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
