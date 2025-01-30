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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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

        .container {
            margin-top: 100px;
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
            <a class="navbar-brand" href="user_dashboard.php">
                <img src="logo.png" alt="Logo" width="35"> ⬅ Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="add_items.php">Add Items</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_shopping_list.php">Shopping List</a></li>
                    <li class="nav-item"><a class="nav-link active" href="recipe_list.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="cook.php">Cook</a></li>
                    <li class="nav-item"><a class="nav-link" href="calculate_wastage.php">Waste Impact</a></li>
                    <li class="nav-item"><a class="nav-link" href="generate_report.php">Reports</a></li>
                </ul>
            </div>
        </div>
    </nav>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
