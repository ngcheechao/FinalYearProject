<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user_id'];

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'fyp');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background: url('food_6.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 80px; /* Prevents navbar from overlapping content */
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
            padding-left: 20px;
            text-decoration: none;
        }

        /* Table Styling */
        h2 {
            text-align: center;
            margin: 20px;
            color: white;
        }

        table {
            width: 90%;
            max-width: 1000px;
            border-collapse: collapse;
            margin: 20px 0;
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
            color: white;
            font-weight: bold;
        }

        td {
            color: #495057;
        }

        tr:hover {
            background: #f8f9fa;
        }

        /* Buttons */
        .edit-btn, .delete-btn, .waste-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn {
            background-color: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .waste-btn {
            background-color: #28a745;
            color: white;
        }

        .waste-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h2>My Groceries Tracker</h2>

    <?php
    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Unit</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $unit_mapping = [1 => 'kg', 2 => 'g', 3 => 'pieces', 4 => 'ml', 5 => 'l'];
            $unit_label = $unit_mapping[$row['unit']] ?? 'Unknown';

            echo "<tr>
                    <td>" . htmlspecialchars($row['item_name']) . "</td>
                    <td>" . $row['quantity'] . "</td>
                    <td>$" . number_format($row['price'], 2) . "</td>
                    <td>" . htmlspecialchars($unit_label) . "</td>
                    <td>" . htmlspecialchars($row['expiry_date']) . "</td>
                    <td>
                        <a href='edit_item.php?id=" . $row['id'] . "' class='edit-btn'>Edit</a>
                        <a href='delete_item.php?id=" . $row['id'] . "' class='delete-btn'>Delete</a>
                        <a href='calculate_wastage.html?item=" . urlencode($row['item_name']) . 
                        "&type=" . urlencode($row['category']) . 
                        "&quantity=" . urlencode($row['quantity']) . 
                        "&unit=" . urlencode($row['unit']) . "' 
                        class='waste-btn'>Waste</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-data'>No items found in your shopping list.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>

    <a href="add_items.html" class="back-btn">Add More Items</a>
    <a href="user_dashboard.html" class="back-btn">Go back to dashboard</a>

</body>
</html>
