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
    $sql = "SELECT * FROM groceries WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Item not found.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $expiry_date = $_POST['expiry_date']; // Retrieve expiry date from form

    $sql = "UPDATE groceries 
            SET item_name='$item_name', quantity=$quantity, price=$price, expiry_date='$expiry_date' 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: view_shopping_list.php");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
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
        input {
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
            <input type="number" name="quantity" value="<?php echo htmlspecialchars($row['quantity']); ?>" required>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" required>

            <label for="expiry_date">Expiry Date:</label>
            <input type="date" name="expiry_date" value="<?php echo htmlspecialchars($row['expiry_date']); ?>" required>

            <button type="submit">Update Item</button>
        </form>
        <a href="view_shopping_list.php">Back to Shopping List</a>
    </div>
</body>
</html>
