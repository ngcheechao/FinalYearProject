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

// Fetch user details to pre-populate the form
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user from database
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message = "User not found.";
    }
}

// Update user details after form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Update query
    $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $password, $user_id);

    if ($stmt->execute()) {
        $message = "User updated successfully.";
    } else {
        $message = "Error updating user: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for eye icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* General page layout */
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin-top: 50px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 30px;
        }

        .form-control {
            border-radius: 8px;
            box-shadow: none;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }

        .btn-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }

        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .password-field {
            position: relative;
        }

        .alert-info {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-color: #a5d6a7;
            margin-bottom: 30px;
        }

        .text-center a {
            margin-top: 20px;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit User</h2>

        <!-- Display message -->
        <?php if (!empty($message)) { ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php } ?>

        <!-- Edit Form -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3 password-field">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                <i class="fas fa-eye eye-icon" id="togglePassword"></i> <!-- Eye Icon for visibility toggle -->
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            <a href="manage_user.php" class="btn btn-secondary w-100 mt-3">Back to User List</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            // Toggle password visibility
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle the eye icon (filled or empty)
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>
