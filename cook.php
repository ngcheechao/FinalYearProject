<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user_id']; // Retrieve user_id from session

// Define a mapping for unit values
$unit_mapping = [
    1 => 'kg',
    2 => 'g',
    3 => 'pieces',
    4 => 'ml',
    5 => 'l'
];

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
$sql = "SELECT * FROM groceries WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); 
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
        background: url('food_6.jpg') no-repeat center center fixed; /* Change 'background.jpg' to your actual image file */
        background-size: cover;
        color: white;
        font-family: Arial, sans-serif;
        padding-top: 80px; /* Prevents navbar from overlapping content */
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

        table {
            width: 100%;
            max-width: 1000px;
            border-collapse: collapse;
            margin: 20px auto;
            background: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #28a745;
            color: black;
            font-weight: bold;
        }

        td {
            color: #495057;
        }

        tr:hover {
            background: #f8f9fa;
        }
        h2 {
            color: white;
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
                    <li class="nav-item"><a class="nav-link active" href="view_shopping_list.php">Shopping List</a></li>
                    <li class="nav-item"><a class="nav-link" href="recipe_manage.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="cook.php">Cook</a></li>
                    <li class="nav-item"><a class="nav-link" href="calculate_wastage.html">Waste Impact</a></li>
                    <li class="nav-item"><a class="nav-link" href="generate_report.php">Reports</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="text-center">Plan Your Cooking!</h2>

        <?php if ($result->num_rows > 0): ?>
            <form action='generate_recipe.php' method='POST'>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Select</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): 
                            $unit_label = isset($unit_mapping[$row['unit']]) ? $unit_mapping[$row['unit']] : 'Unknown';
                            $today = new DateTime();
                            $expiry_date = new DateTime($row['expiry_date']);
                            $interval = $today->diff($expiry_date)->days;

                            if ($expiry_date < $today) {
                                $bg_color = 'background-color: #ffcccc;';
                                $checkbox_disabled = 'disabled';
                            } elseif ($interval <= 3) {
                                $bg_color = 'background-color: #fff3cd;';
                                $checkbox_disabled = '';
                            } else {
                                $bg_color = 'background-color: #d4edda;';
                                $checkbox_disabled = '';
                            }
                        ?>
                        <tr style="<?= $bg_color ?>">
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td><?= htmlspecialchars($unit_label) ?></td>
                            <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                            <td>
                                <input type='checkbox' name='selected_items[]' value='<?= $row['id'] ?>' <?= $checkbox_disabled ?>>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="text-center">
                    <button type='submit' class='btn btn-success'>Submit Selected Items</button>
                    <a href='add_items.html' class='btn btn-primary'>Add More Items</a>
                    
                </div>
            </form>
        <?php else: ?>
            <p class='text-center text-muted'>No items found in your shopping list.</p>
        <?php endif; ?>

        <?php 
        $stmt->close();
        $conn->close();
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
