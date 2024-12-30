<?php
include 'dbConfig.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        // Check token validity
        $query = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
        $query->bind_param("s", $token);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Update the password and clear the token
            $query = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $query->bind_param("ss", $hashedPassword, $token);
            $query->execute();

            echo "Password successfully updated.";
        } else {
            echo "Invalid or expired token.";
        }
    } else {
        echo "Passwords do not match.";
    }
}
?>
