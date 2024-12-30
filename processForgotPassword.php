<?php
// Database connection
include 'dbConfig.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if the email exists in the database
        $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));

            // Save the token in the database
            $query = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour
            $query->bind_param("sss", $token, $expiry, $email);
            $query->execute();

            // Send the password reset email
            $resetLink = "http://yourdomain.com/resetPassword.php?token=$token";
            $subject = "Password Reset Request";
            $message = "Hi,\n\nClick the link below to reset your password:\n$resetLink\n\nThis link will expire in 1 hour.";
            $headers = "From: no-reply@yourdomain.com";

            if (mail($email, $subject, $message, $headers)) {
                echo "A password reset link has been sent to your email.";
            } else {
                echo "Failed to send the email. Please try again.";
            }
        } else {
            echo "Email not found.";
        }
    } else {
        echo "Invalid email address.";
    }
}
?>
