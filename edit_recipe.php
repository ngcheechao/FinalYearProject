<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a recipe ID is provided in the URL to edit
if (isset($_GET['recipe_id'])) {
    $recipe_id = $_GET['recipe_id'];

    // Retrieve the current recipe details
    $sql = "SELECT recipe_name, ingredients, instructions FROM recipes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $recipe = $result->fetch_assoc();
    } else {
        echo "<p>Recipe not found.</p>";
        exit();
    }
    $stmt->close();
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_id'])) {
    $recipe_id = $_POST['recipe_id'];
    $recipe_name = $conn->real_escape_string($_POST['recipe_name']);
    $ingredients = $conn->real_escape_string($_POST['ingredients']);
    $instructions = $conn->real_escape_string($_POST['instructions']);

    // Update the recipe in the database
    $sql = "UPDATE recipes SET recipe_name = ?, ingredients = ?, instructions = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $recipe_name, $ingredients, $instructions, $recipe_id);

    if ($stmt->execute()) {
        echo "<p>Recipe updated successfully!</p>";
    } else {
        echo "<p>Error updating recipe: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    $sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<h1>All Recipes</h1>";
        echo "<table class='recipe-table'>";
        echo "<tr><th>Recipe Name</th><th>Ingredients</th><th>Instructions</th><th>Action</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['recipe_name']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['ingredients'])) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['instructions'])) . "</td>";
            echo "<td><a href='edit_recipe.php?recipe_id=" . $row['id'] . "' class='edit-button'>Edit</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No recipes found.</p>";
    }
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }
        .recipe-table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .recipe-table th, .recipe-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .recipe-table th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }
        .recipe-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .delete-button {
            display: inline-block;
            padding: 8px 12px;
            color: #fff;
            background-color: #dc3545;
            border-radius: 5px;
            text-decoration: none;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php if (isset($recipe)): ?>
    <div class="form-container">
        <h1>Edit Recipe</h1>
        <form method="post" action="edit_recipe.php">
            <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">
            
            <label for="recipe_name">Recipe Name:</label><br>
            <input type="text" id="recipe_name" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required><br><br>
            
            <label for="ingredients">Ingredients:</label><br>
            <textarea id="ingredients" name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea><br><br>
            
            <label for="instructions">Instructions:</label><br>
            <textarea id="instructions" name="instructions" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea><br><br>
            
            <input type="submit" value="Update Recipe" class="edit-button">
        </form>
    </div>
    <?php endif; ?>
</body>
</html>
