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

        .no-data {
            text-align: center;
            font-size: 18px;
            color: #6c757d;
        }

        .back-btn {
            margin: 20px;
            text-decoration: none;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: #218838;
        }

       /* Legend Box Positioned at Bottom-Right */
        .legend {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 250px;
        }


        .legend div {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .legend div span {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .legend .expired {
            background: #ffcccc;
        }

        .legend .near-expiry {
            background: #fff3cd;
        }

        .legend .fresh {
            background: #d4edda;
        }

        /* Daily Quote Section */
        .quote-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 8px;
            max-width: 90%;
            text-align: center;
            margin: 20px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php
    session_start();
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // Fetch Daily Quote
    function fetchDailyQuote() {
        $api_url = "https://api.quotable.io/random?" . time(); // Add timestamp to prevent caching
        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            curl_close($curl);
            return ['content' => 'Start your day with a positive thought!', 'author' => 'Anonymous'];
        }

        curl_close($curl);
        $data = json_decode($response, true);
        return [
            'content' => $data['content'] ?? 'Stay positive and reduce waste!',
            'author' => $data['author'] ?? 'Anonymous'
        ];
    }

    // Call the function
    $daily_quote = fetchDailyQuote();

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

            $today = new DateTime();
            $expiry_date = new DateTime($row['expiry_date']);
            $interval = $today->diff($expiry_date)->days;

            if ($expiry_date < $today) {
                $bg_color = 'background-color: #ffcccc;';
            } elseif ($interval <= 3) {
                $bg_color = 'background-color: #fff3cd;';
            } else {
                $bg_color = 'background-color: #d4edda;';
            }

            echo "<tr style='$bg_color'>
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

    <!-- Legend Section -->
    <div class="legend">
        <div><span class="expired"></span> Expired Items</div>
        <div><span class="near-expiry"></span> Expiring Soon (within 3 days)</div>
        <div><span class="fresh"></span> Fresh Items</div>
    </div>

    <!-- Daily Quote Section at the Bottom -->
    <div class="quote-section">
        <p style="font-style: italic; font-size: 18px; color: #333;">
            "<?php echo htmlspecialchars($daily_quote['content']); ?>"
        </p>
        <p style="font-weight: bold; font-size: 16px; color: #555;">
            - <?php echo htmlspecialchars($daily_quote['author']); ?>
        </p>
    </div>
</body>
</html>
