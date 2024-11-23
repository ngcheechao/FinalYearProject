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
            background-color: #f4f6f9;
            color: #343a40;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #007bff;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .recipe-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 16px;
            color: #495057;
        }
        .recipe-table th {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px;
            font-size: 18px;
        }
        .recipe-table td {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .btn {
            display: block;
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
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
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .edit-button:hover {
            color: #ffffff;
            background-color: #007bff;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f8f9fa;
        }
        .section-title {
            font-weight: bold;
            font-size: 20px;
            color: #343a40;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php if (isset($recipe)): ?>
        <div class="container">
            <h1>Edit Recipe</h1>
            <form method="post" action="edit_recipe.php">
                <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">

                <div class="section-title">Recipe Name</div>
                <input type="text" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required>

                <div class="section-title">Ingredients</div>
                <textarea name="ingredients" rows="5" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>

                <div class="section-title">Instructions</div>
                <textarea name="instructions" rows="5" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>

                <input type="submit" value="Update Recipe" class="btn">
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
