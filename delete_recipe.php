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

// Handle recipe deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prepare and execute the delete statement
    $sql = "DELETE FROM recipes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        // Redirect with a success message using JavaScript
        echo "<script>
                alert('Recipe deleted successfully!');
                window.location.href = 'admin_dashboard.html';
              </script>";
    } else {
        echo "<p>Error deleting recipe: " . $stmt->error . "</p>";
    }

    // Close the statement
    $stmt->close();
}

// Fetch and display all recipes in a table
$sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Recipe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            color: #2e7d32;
        }
        .recipe-table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #ffffff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .recipe-table th, .recipe-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .recipe-table th {
            background-color: #66bb6a;
            color: #ffffff;
            font-weight: bold;
        }
        .recipe-table tr:nth-child(even) {
            background-color: #f1f8e9;
        }
        .delete-button {
            display: inline-block;
            padding: 8px 12px;
            color: #ffffff;
            background-color: #43a047;
            border-radius: 5px;
            text-decoration: none;
        }
        .delete-button:hover {
            background-color: #388e3c;
        }
    </style>
</head>
<body>
    <h1>All Recipes</h1>

    <?php if ($result->num_rows > 0): ?>
        <table class="recipe-table">
            <tr>
                <th>Recipe Name</th>
                <th>Ingredients</th>
                <th>Instructions</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['recipe_name']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['ingredients'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></td>
                    <td>
                        <a href="delete_recipe.php?delete_id=<?php echo $row['id']; ?>" 
                           class="delete-button" 
                           onclick="return confirm('Are you sure you want to delete this recipe?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #2e7d32;">No recipes found.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
