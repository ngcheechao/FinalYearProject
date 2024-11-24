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
        // Use JavaScript to show an alert and redirect
        echo "<script>
                alert('Recipe updated successfully!');
                window.location.href = 'admin_dashboard.html';
              </script>";
    } else {
        echo "<p>Error updating recipe: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    // Display all recipes in a table (same as before)
    $sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
    $result = $conn->query($sql);

    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>All Recipes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 20px;
            padding: 0;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        table {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        thead {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a.edit-button {
            color: #007BFF;
            text-decoration: none;
        }

        a.edit-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>All Recipes</h1>";

    if ($result->num_rows > 0) {
        echo "<table>
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['recipe_name']) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['ingredients'])) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['instructions'])) . "</td>
                    <td><a href='edit_recipe.php?recipe_id=" . $row['id'] . "' class='edit-button'>Edit</a></td>
                  </tr>";
        }
        echo "</tbody>
        </table>";
    } else {
        echo "<p>No recipes found.</p>";
    }

    echo "</body>
</html>";

    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if (isset($recipe)): ?>
        <div class="container">
            <h1 class="title">Edit Recipe</h1>
            <form method="post" action="edit_recipe.php">
                <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">

                <table class="recipe-edit-table">
                    <tr>
                        <th>Recipe Name</th>
                        <td><input type="text" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Ingredients</th>
                        <td><textarea name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea></td>
                    </tr>
                    <tr>
                        <th>Instructions</th>
                        <td><textarea name="instructions" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea></td>
                    </tr>
                </table>

                <div class="button-container">
                    <input type="submit" value="Update Recipe" class="btn">
                </div>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>
