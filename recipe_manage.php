<?php
// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get search query and filter
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'recipe_name';

    // Build query for fetching recipes
    $sql = "SELECT id, recipe_name, ingredients, instructions FROM recipes";
    $params = [];
    if (!empty($search)) {
        $keywords = array_map('trim', explode(' ', strtolower($search)));
        $sql .= " WHERE 1=1";
        foreach ($keywords as $index => $keyword) {
            $sql .= " AND LOWER($filter) LIKE :keyword$index";
            $params[":keyword$index"] = "%" . $keyword . "%";
        }
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
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
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
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
    .search-container {
        position: fixed;
        top: 80px;
        right: 20px;
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
    .container {
        margin-top: 140px;
    }
    /* Card layout for recipes */
    .recipe-card {
        margin-bottom: 20px;
    }
    .section-header {
        font-size: 1.25rem;
        font-weight: bold;
        color: #155724;
        border-bottom: 2px solid #155724;
        padding-bottom: 5px;
        margin-top: 15px;
        margin-bottom: 10px;
    }
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
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="user_dashboard.html">
        <img src="logo.png" alt="Logo" width="35"> ⬅️ Dashboard
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

  <div class="container">
    <h2 class="text-center">Search Results</h2>
    <div class="row">
      <?php if (!empty($recipes)): ?>
        <?php foreach ($recipes as $recipe): ?>
          <div class="col-md-4 recipe-card">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($recipe['recipe_name']); ?></h5>
                <div class="section-header">Ingredients</div>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($recipe['ingredients'])); ?></p>
                <div class="section-header">Instructions</div>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($recipe['instructions'])); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-warning text-center">No recipes found.</div>
        </div>
      <?php endif; ?>
    </div>
    <div class="back-button">
      <a href="recipe_manage.php">Back to Recipes</a>
    </div>
  </div>
</body>
</html>