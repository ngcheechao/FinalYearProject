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

// Check if selected items are posted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items'])) {
    $selected_items = $_POST['selected_items'];

    // Fetch ingredient names for the selected items
    $ingredient_list = '';
    if (!empty($selected_items)) {
        $placeholders = implode(',', array_fill(0, count($selected_items), '?'));
        $sql = "SELECT item_name FROM groceries WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($selected_items)), ...$selected_items);
        $stmt->execute();
        $result = $stmt->get_result();

        $ingredient_names = [];
        while ($row = $result->fetch_assoc()) {
            $ingredient_names[] = $row['item_name'];
        }
        $ingredient_list = implode(', ', $ingredient_names);
        $stmt->close();
    }

    // Fetch recipes matching the selected ingredients
    $matched_recipes = [];
    if (!empty($ingredient_names)) {
        $sql = "SELECT recipe_name, ingredients, instructions FROM recipes";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $recipe_ingredients = explode(', ', $row['ingredients']);
            $matches = array_intersect($recipe_ingredients, $ingredient_names);

            // Only include recipes that match all selected ingredients
            if (count($matches) == count($ingredient_names)) {
                $matched_recipes[] = $row;
            }
        }
    }
} else {
    echo "<p>Error: No ingredients selected. <a href='cook.php'>Go back to select ingredients</a></p>";
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9f7ec;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2d6a4f;
        }

        table {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #d3e4d8;
        }

        th {
            background-color: #52b788;
            color: #ffffff;
        }

        tr:nth-child(even) {
            background-color: #edf7f0;
        }

        tr:hover {
            background-color: #d3e4d8;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .button-container a {
            text-decoration: none;
            background: #40916c;
            color: white;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .button-container a:hover {
            background: #1b4332;
        }

        .no-recipes {
            text-align: center;
            color: #d00000;
        }
    </style>
</head>
<body>
    <h2>Recipe results for the ingredients you selected: <?php echo htmlspecialchars($ingredient_list); ?></h2>

    <?php if (!empty($matched_recipes)) { ?>
        <table>
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matched_recipes as $recipe) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($recipe['recipe_name']); ?></td>
                        <td><?php echo htmlspecialchars($recipe['ingredients']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p class="no-recipes">No recipes match all the selected ingredients.</p>
    <?php } ?>

    <div class="button-container">
        <a href="user_dashboard.html">Return to Dashboard</a>
        <a href="recipe_manage.php">Manage Recipes</a>
    </div>
</body>
</html>
