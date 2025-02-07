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

// Initialize $recipe as null
$recipe = null;

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
    $recipe_name = trim($_POST["recipe_name"]);
    $ingredients = trim($_POST["ingredients"]);
    $instructions = trim($_POST["instructions"]);

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
            background: linear-gradient(135deg, #e0e0e0, #a5d6a7);
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #2e7d32;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 20px;
        }
        header a {
            text-decoration: none;
            color: white;
        }
        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
            text-align: center;
        }
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .recipe-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .recipe-card:hover {
            transform: scale(1.05);
        }
        .recipe-name {
            font-size: 1.5rem;
            color: #388e3c;
            margin-bottom: 10px;
            border-bottom: 2px solid #388e3c;
            padding-bottom: 5px;
        }
        .recipe-details {
            font-size: 1rem;
            color: #555;
            text-align: left;
            margin-top: 10px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .recipe-actions {
            margin-top: 15px;
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
        .edit-btn {
            background-color: #388e3c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .edit-btn:hover {
            background-color: #2e7d32;
        }
    </style>
</head>
<body>
    <header>
        <a href="admin_dashboard.html">← Back to Dashboard</a>
    </header>
    <div class="container">
        <?php if ($recipe !== null): ?>
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
             <header>All Recipes</header>
    <div class="container">
        <div class="recipes-grid">
            <?php
            // Database connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "fyp";
            
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='recipe-card'>";
                    echo "<div class='recipe-name'>" . htmlspecialchars($row['recipe_name']) . "</div>";
                    echo "<div class='recipe-details'><strong>Ingredients:</strong> <br>" . nl2br(htmlspecialchars($row['ingredients'])) . "</div>";
                    echo "<div class='recipe-details'><strong>Instructions:</strong> <br>" . nl2br(htmlspecialchars($row['instructions'])) . "</div>";
                    echo "<div class='recipe-actions'><a class='edit-btn' href='edit_recipe.php?recipe_id=" . $row['id'] . "'>Edit</a></div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No recipes found.</p>";
            }
            
            $conn->close();
            ?>
        </div>
    </div>
        <?php endif; ?>
    </div>
</body>
</html>
