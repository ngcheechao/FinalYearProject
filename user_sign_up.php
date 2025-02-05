<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .success-message {
            color: #28a745;
            font-weight: bold;
        }
        .error-message {
            color: #dc3545;
            font-weight: bold;
        }
        .btn {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container text-center">
        <?php
        // Secure Database Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "fyp";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='error-message'>Connection failed: " . $conn->connect_error . "</p>");
        }

        // Check if form data is set
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = trim($_POST['username']);
            $email = trim($_POST['email']);
            $pass = $_POST['password'];

            // Input Validation
            if (empty($user) || empty($email) || empty($pass)) {
                echo "<h2 class='error-message'>Error: All fields are required!</h2>";
                echo "<a href='sign_up.html' class='btn btn-danger'>Try Again</a>";
                exit();
            }

            // Validate Email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<h2 class='error-message'>Invalid Email Format</h2>";
                echo "<a href='sign_up.html' class='btn btn-danger'>Try Again</a>";
                exit();
            }

            // Assign Role
            $is_admin = 0;

            // Use Prepared Statements to Prevent SQL Injection
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $user, $email, $pass, $is_admin);

            if ($stmt->execute()) {
                echo "<h2 class='success-message'>🎉 Account created successfully!</h2>";
                echo "<p>Welcome, <strong>" . htmlspecialchars($user) . "</strong>! You can now log in.</p>";
                echo "<a href='login.html' class='btn btn-success'>Go to Login Page</a>";
            } else {
                echo "<h2 class='error-message'>Error Creating Account</h2>";
                echo "<p>There was an issue: " . $stmt->error . "</p>";
                echo "<a href='sign_up.html' class='btn btn-danger'>Try Again</a>";
            }

            // Close Statement & Connection
            $stmt->close();
            $conn->close();
        } else {
            echo "<h2 class='error-message'>Invalid Request</h2>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
