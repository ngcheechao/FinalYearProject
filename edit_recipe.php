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

// Display all recipes
$sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Recipes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #343a40;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #007bff;
            font-size: 26px;
            margin-bottom: 20px;
        }
        .recipe-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            color: #495057;
        }
        .recipe-table th {
            background-color: #007bff;
            color: #ffffff;
            padding: 12px;
            text-align: left;
            font-size: 18px;
        }
        .recipe-table td {
            background-color: #f9fafb;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .recipe-table tr:hover {
            background-color: #e9ecef;
        }
        .edit-button {
            display: inline-block;
            color: #ffffff;
            background-color: #007bff;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .edit-button:hover {
            background-color: #0056b3;
        }
        /* Responsive styling */
        @media (max-width: 768px) {
            .recipe-table th, .recipe-table td {
                padding: 10px;
            }
            h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>All Recipes</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="recipe-table">
                <tr>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['recipe_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['ingredients'])); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></td>
                        <td><a href="edit_recipe.php?recipe_id=<?php echo $row['id']; ?>" class="edit-button">Edit</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No recipes found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
