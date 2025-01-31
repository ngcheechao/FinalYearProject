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

    // Fetch recipes based on the search query
    $sql = "SELECT id, recipe_name, ingredients, instructions, created_at FROM recipes";
    if (!empty($search)) {
        $sql .= " WHERE recipe_name LIKE :search";
    }
    $sql .= " ORDER BY created_at DESC";

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
    <title>Recipe List</title>
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

        .navbar-nav {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .navbar-nav .nav-item {
            margin: 0 8px;
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
            top: 80px; /* Position below the navbar */
            right: 20px; /* Position on the right side */
            z-index: 999;
        }

        .search-form {
            display: flex;
            align-items: center;
        }

        .search-form input {
            border-radius: 20px;
            padding: 8px 20px;
            border: 1px solid #ddd;
            outline: none;
            width: 250px;
        }

        .search-form button {
            background: #007BFF;
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            margin-left: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-form button:hover {
            background: #0056b3;
        }

        /* Container Styling */
        .container {
            margin-top: 140px; /* Adjusted to accommodate the search bar */
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
            color: green;
        }

        tr:hover {
            background: #f1f1f1;
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="add_items.html">Add Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_shopping_list.php">Shopping List</a></li>
                    <li class="nav-item"><a class="nav-link active" href="recipe_list.php">Recipes</a></li>
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
            <input type="text" name="search" id="searchInput" placeholder="Search recipes..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Recipe List -->
    <div class="container">
        <h2 class="text-center">Recipe List</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Recipe Name</th>
                    <th>Ingredients</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody id="recipeTableBody">
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

</body>
</html>
