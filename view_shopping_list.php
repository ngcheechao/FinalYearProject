<?php
session_start();

// Predefined Quotes Array
$quotes = [
    ["content" => "Waste not, want not.", "author" => "Benjamin Franklin"],
    ["content" => "The greatest threat to our planet is the belief that someone else will save it.", "author" => "Robert Swan"],
    ["content" => "Reduce, reuse, recycle.", "author" => "Anonymous"],
    ["content" => "Sustainability is not a trend; it's a responsibility.", "author" => "Anonymous"],
    ["content" => "Every small action counts. Start reducing waste today.", "author" => "Unknown"],
    ["content" => "Be the change you wish to see in the world.", "author" => "Mahatma Gandhi"]
];

shuffle($quotes);
$daily_quote = $quotes[0];

if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user_id'];

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
        }

        h2 {
            text-align: center;
            margin: 20px;
            color: #495057;
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

        td button {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        td button:hover {
            transform: scale(1.05);
        }

        .edit-btn {
            background: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .wastage-btn {
            background: #28a745;
            color: white;
        }

        .wastage-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <h2 style="color:white">My Groceries Tracker</h2>

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
                        <form style='display: inline;' action='edit_item.php' method='GET'>
                            <input type='hidden' name='id' value='" . $row['id'] . "'>
                            <button type='submit' class='edit-btn'>Edit</button>
                        </form>
                        <form style='display: inline;' action='delete_item.php' method='POST'>
                            <input type='hidden' name='id' value='" . $row['id'] . "'>
                            <button type='submit' class='delete-btn'>Delete</button>
                        </form>
                        <a href='calculate_wastage.html?item=" . urlencode($row['item_name']) . "&quantity=" . $row['quantity'] . "&unit=" . urlencode($unit_label) . "&category=" . urlencode($row['category']) . "'>
                            <button class='wastage-btn'>Waste</button>
                        </a>
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
