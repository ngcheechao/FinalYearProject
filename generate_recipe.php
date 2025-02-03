<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user_id']; // Retrieve user_id from session

// Connect to the database
$host = 'localhost'; 
$username = 'root'; 
$password = ''; 
$database = 'fyp'; 

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch items for the logged-in user
$sql_items = "SELECT * FROM groceries WHERE user_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $user_id); 
$stmt_items->execute();
$result_items = $stmt_items->get_result();

// Fetch recipes from the database
$sql_recipes = "SELECT * FROM recipes";
$stmt_recipes = $conn->prepare($sql_recipes);
$stmt_recipes->execute();
$result_recipes = $stmt_recipes->get_result();

// Fetch selected items from the form submission
$selected_items = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];

// Function to clean up item names (optional, to avoid special characters)
function clean_string($str) {
    $str = strtolower(trim($str)); 
    $str = preg_replace('/[^a-z0-9\s]/', '', $str); // Remove special characters
    $str = preg_replace('/\s+/', ' ', $str); // Remove extra spaces
    return $str;
}

// Get cleaned item names for selected items
$item_names = [];
while ($row = $result_items->fetch_assoc()) {
    if (in_array($row['id'], $selected_items)) {
        $cleaned_item = clean_string($row['item_name']);
        $item_names[] = $cleaned_item; // Store selected item names
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Recipe</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e9f7ef;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 80px;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #14961F, rgb(23, 240, 38));
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            padding: 10px 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-left: 20px;
            text-decoration: none;
        }

        /* Table Styles */
        h2 {
            text-align: center;
            margin: 20px;
            color: #333;
        }

        table {
            width: 95%;
            max-width: 1200px;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            word-wrap: break-word; /* Ensure long words break and wrap */
            white-space: pre-wrap; /* Preserve whitespace and line breaks */
        }

        th {
            background: #28a745;
            color: white;
            font-weight: bold;
        }

        td {
            color: #495057;
        }

        tr:hover {
            background: #f8f9fa;
        }

        /* Button Styles */
        .btn-container {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-container a {
            text-decoration: none;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .btn-container a:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a class="navbar-brand" href="user_dashboard.html">
            <img src="logo.png" alt="Logo" width="35"> ⬅️ Dashboard
        </a>
    </nav>

    <h2>Selected Items: <?php echo implode(", ", $item_names); ?></h2>

    <?php
    $recipes_found = false;

    if (count($selected_items) > 0 && $result_recipes->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                </tr>";

        while ($recipe = $result_recipes->fetch_assoc()) {
            // Directly use ingredients as stored in the database
            $recipe_ingredients = nl2br(htmlspecialchars($recipe['ingredients'])); // Convert newlines to <br> tags

            // Check if any of the selected items are contained as a substring in the recipe ingredients
            $matched_ingredients = [];
            foreach ($item_names as $item) {
                if (strpos(strtolower($recipe_ingredients), strtolower($item)) !== false) {
                    $matched_ingredients[] = $item;  // If match found, add to matched ingredients
                }
            }

            // Only display recipes where at least one item is matched
            if (count($matched_ingredients) > 0) {
                $recipes_found = true;
                echo "<tr>
                        <td>" . htmlspecialchars($recipe['recipe_name']) . "</td>
                        <td>" . $recipe_ingredients . "</td>  <!-- Show full ingredients as stored in DB with line breaks -->
                        <td>" . htmlspecialchars($recipe['instructions']) . "</td>
                      </tr>";
            }
        }
        echo "</table>";
    }

    if (!$recipes_found) {
        echo "<p>No search results found based on selected items.</p>";
    }
    ?>

    <div class="btn-container">
        <a href="user_dashboard.html">Return to Dashboard</a>
        <a href="recipe_manage.php">Back to Recipe Management</a>
    </div>

</body>
</html>
