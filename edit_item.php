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

    $sql = "UPDATE groceries SET item_name='$item_name', quantity=$quantity, price=$price WHERE id=$id";

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
</head>
<body>
    <h2>Edit Item</h2>

    <form method="post" action="edit_item.php">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" value="<?php echo htmlspecialchars($row['item_name']); ?>" required><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" value="<?php echo htmlspecialchars($row['quantity']); ?>" required><br>

        <label for="price">Price:</label>
        <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" required><br>

        <button type="submit">Update Item</button>
    </form>

    <a href="view_shopping_list.php">Back to Shopping List</a>
</body>
</html>
