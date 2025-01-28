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

    // Fetch all recipes from the database
    $sql = "SELECT id, recipe_name, ingredients, instructions, created_at FROM recipes ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe List</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            text-align: center;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        /* Table Styling */
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007BFF;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }

        /* Dashboard Button */
        .logout-container {
            margin-top: 20px;
        }
        .logout-btn {
            display: inline-block;
            background: #007BFF;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <h2>Recipe List</h2>

    <table>
        <tr>
            <th>Recipe Name</th>
            <th>Ingredients</th>
            <th>Instructions</th>
        </tr>
        
        <?php if (!empty($recipes)): ?>
            <?php foreach ($recipes as $recipe): ?>
                <tr>
                    <td><?php echo htmlspecialchars($recipe['recipe_name']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($recipe['ingredients'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align:center;">No recipes found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="logout-container">
        <a href="admin_dashboard.html" class="logout-btn">To Dashboard</a>
    </div>

</body>
</html>
