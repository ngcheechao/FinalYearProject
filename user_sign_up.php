<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS -->
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
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "fyp";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("<p class='error-message'>Connection failed: " . $conn->connect_error . "</p>");
        }

        // Get form data
        $user = $_POST['username'];
        $email = $_POST['email'];
        $pass = $_POST['password'];

        // Assign the role as "Normal User"
        $is_admin = 0; // Normal users are not admins

        // Insert data into the `users` table
        $sql = "INSERT INTO users (username, email, password, is_admin) VALUES ('$user', '$email', '$pass', $is_admin)";

        if ($conn->query($sql) === TRUE) {
            echo "<h2 class='success-message'>🎉 Account created successfully!</h2>";
            echo "<p>Welcome, <strong>$user</strong>! You can now log in.</p>";
            echo "<a href='login.html' class='btn btn-success'>Go to Login Page</a>";
        } else {
            echo "<h2 class='error-message'>Error Creating Account</h2>";
            echo "<p>There was an issue: " . $conn->error . "</p>";
            echo "<a href='login.html' class='btn btn-danger'>Try Again</a>";
        }

        $conn->close();
        ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
