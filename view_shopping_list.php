<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "fyp"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user-specific items from the groceries table
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, item_name, quantity, price, unit FROM groceries WHERE user_id = '$user_id'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="shopping-list-container">
        <h2>Your Shopping List</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        
                        <!-- <th>ID</th> -->
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <!-- <td><?php echo $row["id"]; ?></td> -->
                            <td><?php echo htmlspecialchars($row["item_name"]); ?></td>
                            <td>
                                <?php 
                                // Display quantity with unit if it's not "pieces"
                                if (strtolower($row["unit"]) != "pieces") {
                                    echo htmlspecialchars($row["quantity"]) . " " . htmlspecialchars($row["unit"]);
                                } else {
                                    echo htmlspecialchars($row["quantity"]);
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars(number_format($row["price"], 2)); ?></td>
                            <td>
                                <a href="edit_item.php?id=<?php echo $row['id']; ?>" class="edit-button">Edit</a>
                            </td>
                            <td>
                                <a href="delete_item.php?id=<?php echo $row['id']; ?>" class="delete-button">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items in your shopping list yet.</p>
        <?php endif; ?>

        <a href="user_dashboard.html" class="back-button">Back to Dashboard</a>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
