<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Get login form data
$email = $_POST['email'];
$pass = $_POST['password']; // Plain text comparison

// Check if the user exists in the database
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Directly compare plain text passwords
    if ($pass === $row['password']) {  // Remove password_verify and use direct comparison
        // Store user ID in session
        $_SESSION['user_id'] = $row['id'];

        // Check if the user is an admin
        if ($row['is_admin'] == 1) {
            header("Location: admin_dashboard.html");
        } else {
            header("Location: user_dashboard.html");
        }
        exit();
    } else {
        // Invalid password
        $message = "Invalid password. Please try again.";
    }
} else {
    // No account found
    $message = "No account found with that email.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <script>
        // Display a modal with the error message and redirect on close
        document.addEventListener('DOMContentLoaded', () => {
            const message = "<?php echo isset($message) ? $message : ''; ?>";
            if (message) {
                const modalHTML = `
                    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-danger" id="errorModalLabel">Error</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ${message}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();

                // Redirect to login page when the modal is closed
                const modalElement = document.getElementById('errorModal');
                modalElement.addEventListener('hidden.bs.modal', () => {
                    window.location.href = 'login.html';
                });
            } else {
                // Redirect to login page if no message exists
                window.location.href = 'login.html';
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
