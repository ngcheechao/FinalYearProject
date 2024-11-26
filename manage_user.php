<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "";

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // First, delete related records in the food_wastage table
    $conn->query("DELETE FROM food_wastage WHERE user_id = $id");

    // Then, delete the user
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        $message = "User with ID $id has been deleted successfully.";
    } else {
        $message = "Error deleting user: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .table-container {
            margin: auto;
            max-width: 800px;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center mb-4">Admin Panel - User List</h2>

        <!-- Display messages -->
        <?php if (!empty($message)) { ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php } ?>

        <!-- User List Table -->
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all users (excluding admins)
                    $sql = "SELECT * FROM users WHERE is_admin = 0";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>
                                    <a href='manage_user.php?delete=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-center mt-4">
                <a href="createUser.html" class="btn btn-outline-primary">Add User</a>
            </div>
            <div class="text-center mt-4">
                <a href="admin_dashboard.html" class="btn btn-outline-primary">Back to Admin Dashboard</a>
            </div>
            
        </div>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
$conn->close();
?>
