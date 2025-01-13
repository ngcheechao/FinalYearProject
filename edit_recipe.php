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
        // Use JavaScript to show an alert and redirect back to edit_recipe.php
        echo "<script>
                alert('Recipe updated successfully!');
                window.location.href = 'edit_recipe.php';
              </script>";
    } else {
        echo "<p>Error updating recipe: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    // Display all recipes in a table
    $sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #81c784, #388e3c);
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #2e7d32;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
        }
        header a {
            text-decoration: none;
            color: white;
        }
        .container {
            width: 90%;
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            font-size: 2.5rem;
            color: #388e3c;
        }
        form {
            margin-top: 30px;
        }
        p {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #388e3c;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #388e3c;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 1rem;
            color: #333;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, textarea:focus {
            border-color: #2e7d32;
            outline: none;
        }
        textarea {
            height: 150px;
            resize: vertical;
        }
        .btn-update, .btn-back {
            display: inline-block;
            padding: 12px 25px;
            background-color: #388e3c;
            color: white;
            border: none;
            border-radius: 30px;
            text-decoration: none;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s;
        }
        .btn-update:hover, .btn-back:hover {
            background-color: #2e7d32;
            transform: scale(1.05);
        }
        .btn-back {
            background-color: #4caf50;
        }
        .btn-back:hover {
            background-color: #388e3c;
        }
        .btn-update:focus, .btn-back:focus {
            outline: none;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #388e3c;
            color: white;
            font-size: 1.1rem;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        td a {
            color: #388e3c;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        td a:hover {
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <header>
        <a href="admin_dashboard.html">← Back to Dashboard</a>
    </header>
    <div class="container">
        <?php if (isset($recipe)): ?>
            <h1>Edit Recipe</h1>
            <form method="post" action="edit_recipe.php">
                <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">
                <p>
                    <label for="recipe_name">Recipe Name:</label>
                    <input type="text" id="recipe_name" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required>
                </p>
                <p>
                    <label for="ingredients">Ingredients:</label>
                    <textarea id="ingredients" name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                </p>
                <p>
                    <label for="instructions">Instructions:</label>
                    <textarea id="instructions" name="instructions" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                </p>
                <button type="submit" class="btn-update">Update Recipe</button>
                <a href="edit_recipe.php" class="btn-back">Cancel</a>
            </form>
        <?php else: ?>
            <h1>All Recipes</h1>
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Recipe Name</th>
                            <th>Ingredients</th>
                            <th>Instructions</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['recipe_name']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($row['ingredients'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></td>
                                <td>
                                    <a href="edit_recipe.php?recipe_id=<?php echo $row['id']; ?>">Edit</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recipes found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
