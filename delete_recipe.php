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

// Handle multiple recipe deletions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_ids'])) {
    $delete_ids = $_POST['delete_ids'];
    $placeholders = implode(',', array_fill(0, count($delete_ids), '?')); // Create placeholders for prepared statement
    $sql = "DELETE FROM recipes WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param(str_repeat('i', count($delete_ids)), ...$delete_ids);

    if ($stmt->execute()) {
        // Redirect with success message
        echo "<script>
                alert('Selected recipes deleted successfully!');
                window.location.href = 'delete_recipe.php';
              </script>";
    } else {
        echo "<p>Error deleting recipes: " . $stmt->error . "</p>";
    }

    // Close the statement
    $stmt->close();
}

// Fetch all recipes
$sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Recipes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #2e7d32;
            padding: 10px;
        }
        .back-button {
            color: #ffffff;
            text-decoration: none;
            font-size: 18px;
            margin-left: 10px;
        }
        .back-button:hover {
            text-decoration: underline;
        }
        h1 {
            text-align: center;
            margin: 20px 0;
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
        .delete-section {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        .delete-button {
            padding: 10px 20px;
            color: #ffffff;
            background-color: #43a047;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .delete-button:hover {
            background-color: #388e3c;
        }
        .delete-button:disabled {
            background-color: #a5d6a7;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header>
        <a href="admin_dashboard.html" class="back-button">← Back to Dashboard</a>
    </header>
    <h1>All Recipes</h1>

    <form method="POST" action="delete_recipe.php">
        <?php if ($result->num_rows > 0): ?>
            <table class="recipe-table">
                <tr>
                    <th>Select</th>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="delete_ids[]" value="<?php echo $row['id']; ?>">
                        </td>
                        <td><?php echo htmlspecialchars($row['recipe_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['ingredients'])); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <div class="delete-section">
                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete the selected recipes?')">Delete Selected</button>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #2e7d32;">No recipes found.</p>
        <?php endif; ?>
    </form>

    <?php $conn->close(); ?>
</body>
</html>
