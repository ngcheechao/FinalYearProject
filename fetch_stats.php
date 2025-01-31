<?php
// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get total recipes
    $recipeQuery = $pdo->query("SELECT COUNT(*) AS total_recipes FROM recipes");
    $recipeCount = $recipeQuery->fetch(PDO::FETCH_ASSOC)['total_recipes'];

    // Query to get total users
    $userQuery = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $userCount = $userQuery->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Return JSON response
    echo json_encode(["recipes" => $recipeCount, "users" => $userCount]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
