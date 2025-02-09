<?php
// Database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "fyp";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable and user array
$message = "";
$alert_class = ""; // Used to style success/error messages
$user = [];

// Fetch user details to pre-populate the form
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prepare a statement to fetch the user details
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message = "User not found.";
        $alert_class = "alert-danger";
    }
}

// Update user details after form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and trim form fields
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $is_admin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;

    // Server-side validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = "Please fill in all required fields.";
        $alert_class = "alert-danger";
    } else {
        // Check if the email already exists for another user
        $sql_check = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $email, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "Error: The email address is already in use by another user.";
            $alert_class = "alert-danger";
        } else {
            // Prepare the update query
            $sql = "UPDATE users SET username = ?, email = ?, password = ?, is_admin = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $username, $email, $password, $is_admin, $user_id);

            if ($stmt->execute()) {
                $message = "User updated successfully.";
                $alert_class = "alert-success";

                // Update user array for form repopulation
                $user['username'] = $username;
                $user['email']    = $email;
                $user['password'] = $password;
                $user['is_admin'] = $is_admin;
            } else {
                $message = "Error updating user: " . $conn->error;
                $alert_class = "alert-danger";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Basic styling */
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      background-color: #ffffff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #2e7d32;
    }
    .form-control {
      border-radius: 8px;
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
    .alert {
      margin-bottom: 20px;
    }
    /* Password eye icon styling */
    .input-group-text {
      cursor: pointer;
      background: #ffffff;
      border-left: none;
    }
    .input-group input {
      border-right: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit User</h2>

    <!-- Display error or success messages -->
    <?php if (!empty($message)) { ?>
      <div class="alert <?php echo $alert_class; ?> text-center"><?php echo $message; ?></div>
    <?php } ?>

    <!-- Edit User Form -->
    <form method="POST" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input
          type="text"
          class="form-control"
          id="username"
          name="username"
          value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
          required
        >
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
          required
        >
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            value="<?php echo htmlspecialchars($user['password'] ?? ''); ?>"
            required
          >
          <span class="input-group-text" id="togglePassword">👁️</span>
        </div>
      </div>
      <div class="mb-3">
        <label for="is_admin" class="form-label">User Role</label>
        <select class="form-control" id="is_admin" name="is_admin" required>
          <option value="0" <?php if (isset($user['is_admin']) && $user['is_admin'] == 0) echo 'selected'; ?>>User</option>
          <option value="1" <?php if (isset($user['is_admin']) && $user['is_admin'] == 1) echo 'selected'; ?>>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100">Save Changes</button>
      <a href="manage_user.php" class="btn btn-secondary w-100 mt-3">Back to User List</a>
    </form>
  </div>

  <script>
    document.getElementById("togglePassword").addEventListener("click", function() {
      let passwordField = document.getElementById("password");
      passwordField.type = passwordField.type === "password" ? "text" : "password";
      this.textContent = passwordField.type === "password" ? "👁️" : "🙈";
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
