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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Collect input data
        $recipe_name = trim($_POST["recipe_name"]);
        $ingredients = trim($_POST["ingredients"]);
        $instructions = trim($_POST["instructions"]);
        $user_id = 1; // Change this if needed, maybe get from session login?

        // Validate input (Check if empty)
        if (empty($recipe_name) || empty($ingredients) || empty($instructions)) {
            die("All fields are required.");
        }

        // Prepare SQL query using prepared statements to prevent SQL injection
        $sql = "INSERT INTO recipes (recipe_name, ingredients, instructions, user_id, created_at) 
                VALUES (:recipe_name, :ingredients, :instructions, :user_id, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":recipe_name", $recipe_name);
        $stmt->bindParam(":ingredients", $ingredients);
        $stmt->bindParam(":instructions", $instructions);
        $stmt->bindParam(":user_id", $user_id);

        // Execute the query
        if ($stmt->execute()) {
            echo "Recipe added successfully!";
        } else {
            echo "Error adding recipe.";
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
