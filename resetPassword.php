<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('food_4.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
        }

        .card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 12px;
            padding: 20px;
        }

        .btn-custom {
            background-color: #333333;
            color: #fff;
            font-size: 1rem;
            font-weight: bold;
        }

        .btn-custom:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div class="card">
        <h3 class="text-center mb-4">Reset Password</h3>
        <form action="updatePassword.php" method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
            <div class="mb-3">
                <label for="password" class="form-label">New Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm-password" class="form-label">Confirm Password:</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Reset Password</button>
        </form>
    </div>
</body>

</html>
