<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user_id']; // Retrieve user_id from session

// Connect to the database
$host = 'localhost'; // Change if necessary
$username = 'root'; // Change if necessary
$password = ''; // Change if necessary
$database = 'fyp'; // Replace with your database name

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve selected ingredients from cook.php
$selected_ingredients = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

// If no ingredients are selected
if (empty($selected_ingredients)) {
    die("<p>No ingredients selected. <a href='cook.php'>Back</a></p>");
}

// Create a placeholder for the prepared statement
$placeholders = implode(',', array_fill(0, count($selected_ingredients), '?'));

// Prepare SQL query
$sql = "SELECT r.recipe_name, i.ingredient_name, r.instructions 
        FROM recipes r
        JOIN ingredients i ON r.id = i.recipe_id
        WHERE i.ingredient_name IN ($placeholders)
        GROUP BY r.recipe_name, r.instructions";

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($selected_ingredients)), ...$selected_ingredients); // Bind selected ingredients

$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Recipe Results for Selected Ingredients</h2>";

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Recipe Name</th>
                <th>Ingredients</th>
                <th>Instructions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['recipe_name']) . "</td>
                <td>" . htmlspecialchars($row['ingredient_name']) . "</td>
                <td>" . htmlspecialchars($row['instructions']) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No matching recipes found.</p>";
}

$stmt->close();
$conn->close();
?>

<!-- Buttons -->
<a href="user_dashboard.html" class="back-btn">Return to Dashboard</a>
<a href="recipe_manage.php" class="back-btn">Manage Recipes</a>

<style>
    .back-btn {
        margin: 10px;
        text-decoration: none;
        background: #28a745;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        transition: background 0.3s ease;
        display: inline-block;
    }

    .back-btn:hover {
        background: #218838;
    }
</style>
