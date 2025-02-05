<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select all expired items
$sql = "SELECT id, user_id, item_name, quantity, unit FROM groceries WHERE expiry_date < CURDATE()";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $item_name = $row['item_name'];
    $quantity = $row['quantity'];
    $unit = $row['unit'];
    $reason = "Expired";

    // ✅ Validate if user_id exists in users table
    $check_user_sql = "SELECT id FROM users WHERE id = ?";
    $stmt_check_user = $conn->prepare($check_user_sql);
    $stmt_check_user->bind_param("i", $user_id);
    $stmt_check_user->execute();
    $stmt_check_user->store_result();

    if ($stmt_check_user->num_rows > 0) {
        // ✅ Insert into food_wastage only if user exists
        $insert_sql = "INSERT INTO food_wastage (user_id, item_name, quantity, unit, reason, timestamp) 
                       VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("issss", $user_id, $item_name, $quantity, $unit, $reason);

        if (!$stmt->execute()) {
            error_log("❌ Error inserting expired item for user_id $user_id: " . $stmt->error);
        }
        $stmt->close();

        // ✅ Delete expired item from groceries
        $delete_sql = "DELETE FROM groceries WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_sql);
        $stmt_delete->bind_param("i", $row['id']);
        $stmt_delete->execute();
        $stmt_delete->close();
    } else {
        error_log("❌ Error: User ID $user_id does not exist, skipping expired item.");
    }

    $stmt_check_user->close();
}

$conn->close();
?>