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

    // Check if a search query is provided
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'recipe_name'; // Default filter

    // Fetch recipes based on the search query
    $sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
    if (!empty($search)) {
        $sql .= " WHERE $filter LIKE :search"; // Use the selected filter
    }
    $sql .= " ORDER BY recipe_name ASC";

    $stmt = $pdo->prepare($sql);
    if (!empty($search)) {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $stmt->execute();
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
    <title>Recipe Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #14961F, rgb(23, 240, 38));
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-nav .nav-link {
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 10px 15px;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Search Bar Styling */
        .search-container {
            position: fixed;
            top: 80px; /* Below navbar */
            right: 20px; /* Right side */
            z-index: 999;
        }

        .search-form input {
            border-radius: 20px;
            padding: 8px 20px;
            border: 1px solid #ddd;
            outline: none;
            width: 250px;
        }

        .search-form select {
            border-radius: 20px;
            padding: 8px 10px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .search-form button {
            background: #007BFF;
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-form button:hover {
            background: #0056b3;
        }

        /* Container Styling */
        .container {
            margin-top: 140px;
        }

        /* Green Themed Table Styling */
        .recipe-table table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: #d4edda;
            border: 2px solid #155724;
            border-radius: 10px;
            overflow: hidden;
        }

        .recipe-table th, .recipe-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #155724;
            color: #000; /* Ensure text is black */
        }

        .recipe-table th {
            background: #155724;
            color: white;
            text-align: center;
        }

        .recipe-table tr:hover {
            background: #c3e6cb;
        }

        /* Back Button Styling */
        .back-button {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .back-button a {
            background: #14961F;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-button a:hover {
            background: #0e7016;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="user_dashboard.html">
                <img src="logo.png" alt="Logo" width="35"> ⬅ Dashboard
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="add_items.html">Add Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_shopping_list.php">Shopping List</a></li>
                    <li class="nav-item"><a class="nav-link active" href="recipe_manage.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="cook.php">Cook</a></li>
                    <li class="nav-item"><a class="nav-link" href="calculate_wastage.html">Waste Impact</a></li>
                    <li class="nav-item"><a class="nav-link" href="generate_report.php">Reports</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Search Bar -->
    <div class="search-container">
        <form class="search-form" method="GET" action="recipe_list.php">
            <select name="filter" id="filterSelect">
                <option value="recipe_name" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'recipe_name') ? 'selected' : ''; ?>>Recipe Name</option>
                <option value="ingredients" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'ingredients') ? 'selected' : ''; ?>>Ingredients</option>
                <option value="instructions" <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'instructions') ? 'selected' : ''; ?>>Instructions</option>
            </select>
            <input type="text" name="search" id="searchInput" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Recipe List -->
    <div class="container">
        <h2 class="text-center">Search Results</h2>

        <div class="recipe-table">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Ingredients</th>
                        <th>Instructions</th>
                    </tr>
                </thead>
                <tbody>
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
                            <td colspan="3" class="text-center">No recipes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Back to Recipes Button -->
        <div class="back-button">
            <a href="recipe_manage.php"></a>