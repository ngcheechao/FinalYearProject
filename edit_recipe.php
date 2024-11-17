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
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #343a40;
        }
        .recipe-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .recipe-table th,
        .recipe-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .recipe-table th {
            background-color: #007bff;
            color: #fff;
        }
        .recipe-table td {
            background-color: #f8f9fa;
            color: #343a40;
        }
        .btn {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .edit-button {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .edit-button:hover {
            color: #0056b3;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php if (isset($recipe)): ?>
        <div class="container">
            <h1>Edit Recipe</h1>
            <form method="post" action="edit_recipe.php">
                <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">

                <table class="recipe-table">
                    <tr>
                        <th>Recipe Name</th>
                        <td><input type="text" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Ingredients</th>
                        <td><textarea name="ingredients" rows="5" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea></td>
                    </tr>
                    <tr>
                        <th>Instructions</th>
                        <td><textarea name="instructions" rows="5" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea></td>
                    </tr>
                </table>

                <input type="submit" value="Update Recipe" class="btn">
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
