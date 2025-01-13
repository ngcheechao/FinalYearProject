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
                window.location.href = 'user_dashboard.html';
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
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        /* Green back to dashboard button style */
        .back-to-dashboard {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-to-dashboard:hover {
            background-color: #45a049;
        }

        .search-container {
            position: absolute;
            top: 20px;
            right: 20px;
            text-align: right;
        }

        .search-container input[type='text'] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-container button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #45a049;
        }

        table {
            width: 90%;
            max-width: 800px;
            margin: 60px auto;
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
    <!-- Green Back to Dashboard Button -->
    <a href='user_dashboard.html' class='back-to-dashboard'>Back to Dashboard</a>
    
    <h1>All Recipes</h1>
    
    <!-- Search Bar -->
    <div class='search-container'>
        <form method='get' action='search_results.php'>
            <input type='text' name='search' placeholder='Search by recipe name...'>
            <button type='submit'>Search</button>
        </form>
    </div>";

    if ($result->num_rows > 0) {
        echo "<table>
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['recipe_name']) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['ingredients'])) . "</td>
                    <td>" . nl2br(htmlspecialchars($row['instructions'])) . "</td>
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
