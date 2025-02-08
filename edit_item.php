<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM groceries WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Item not found.";
        exit;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $expiry_date = $_POST['expiry_date'];
    $unit = $_POST['unit']; 

    $today = date('Y-m-d');

    // Validate expiry date (must be today or later)
    if ($expiry_date < $today) {
        echo "
        <div style='
            max-width: 400px; 
            margin: 20px auto; 
            padding: 20px; 
            border: 1px solid #ffb3b3; 
            border-radius: 10px; 
            background-color: #ffe6e6; 
            font-family: Arial, sans-serif; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);'>
            <h3 style='color: red; margin-top: 0;'>❌ Invalid Expiry Date!</h3>
            <p style='margin: 10px 0;'>The expiry date cannot be in the past. Please select today or a future date.</p>
            <a href='edit_item.php?id=$id' style='
                display: inline-block; 
                padding: 8px 15px; 
                color: white; 
                background-color: #cc0000; 
                text-decoration: none; 
                border-radius: 5px; 
                font-size: 14px;'>Go Back</a>
        </div>";
        exit();
    }

    // Use prepared statement to update the database securely
    $stmt = $conn->prepare("UPDATE groceries 
                            SET item_name = ?, quantity = ?, price = ?, expiry_date = ?, unit = ?
                            WHERE id = ?");
    $stmt->bind_param("sidsii", $item_name, $quantity, $price, $expiry_date, $unit, $id);

    if ($stmt->execute()) {
        header("Location: view_shopping_list.php");
        exit;
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        a {
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
            text-align: center;
            margin-top: 15px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Item</h2>
        <form method="post" action="edit_item.php">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <label for="item_name">Item Name:</label>
            <input type="text" name="item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>" required>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" value="<?php echo htmlspecialchars($row['quantity']); ?>" required min="1">

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" required step="0.01" min="0">

            <label for="expiry_date">Expiry Date:</label>
            <input type="date" name="expiry_date" value="<?php echo htmlspecialchars($row['expiry_date']); ?>" required>

            <label for="unit">Unit:</label>
            <select name="unit" required>
                <option value="1" <?php if ($row['unit'] == 1) echo 'selected'; ?>>Kilogram</option>
                <option value="2" <?php if ($row['unit'] == 2) echo 'selected'; ?>>Gram</option>
                <option value="3" <?php if ($row['unit'] == 3) echo 'selected'; ?>>Pieces</option>
                <option value="4" <?php if ($row['unit'] == 4) echo 'selected'; ?>>Millilitre</option>
                <option value="5" <?php if ($row['unit'] == 5) echo 'selected'; ?>>Liter</option>
            </select>

            <button type="submit">Update Item</button>
        </form>
        <a href="view_shopping_list.php">Back to Shopping List</a>
    </div>
</body>
</html>
