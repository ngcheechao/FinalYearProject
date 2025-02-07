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
            text-align: center;
        }
        .message-box {
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .success-box {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Secure Database Connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "fyp";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<div class='message-box error-box'>❌ Connection failed: " . $conn->connect_error . "</div>");
        }

        // Check if form data is set
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = trim($_POST['username']);
            $email = trim($_POST['email']);
            $pass = $_POST['password'];

            // Input Validation
            if (empty($user) || empty($email) || empty($pass)) {
                echo "<div class='message-box error-box'>❌ Error: All fields are required!</div>";
                echo "<a href='login.html' class='btn btn-danger'>Try Again</a>";
                exit();
            }

            // Validate Email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='message-box error-box'>⚠️ Invalid Email Format!</div>";
                echo "<a href='login.html' class='btn btn-warning'>Try Again</a>";
                exit();
            }

            // Validate Password Length: must be exactly 8 characters
            if (strlen($pass) !== 8) {
                echo "<div class='message-box error-box'>❌ Password must be exactly 8 characters long!</div>";
                echo "<a href='login.html' class='btn btn-danger'>Try Again</a>";
                exit();
            }

            // Check if Email Already Exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                echo "<div class='message-box error-box'>⚠️ Email already registered! Please log in.</div>";
                echo "<a href='login.html' class='btn btn-primary'>Go to Login</a>";
                $check_stmt->close();
                $conn->close();
                exit();
            }
            $check_stmt->close();

            // Assign Role
            $is_admin = 0;

            // Use Prepared Statements to Prevent SQL Injection
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $user, $email, $pass, $is_admin);

            if ($stmt->execute()) {
                echo "<div class='message-box success-box'>🎉 Account created successfully!</div>";
                echo "<p>Welcome, <strong>" . htmlspecialchars($user) . "</strong>! You can now log in.</p>";
                echo "<a href='login.html' class='btn btn-success'>Go to Login Page</a>";
            } else {
                echo "<div class='message-box error-box'>❌ Error Creating Account!</div>";
                echo "<p>There was an issue: " . $stmt->error . "</p>";
                echo "<a href='login.html' class='btn btn-danger'>Try Again</a>";
            }

            // Close Statement & Connection
            $stmt->close();
            $conn->close();
        } else {
            echo "<div class='message-box error-box'>❌ Invalid Request!</div>";
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
