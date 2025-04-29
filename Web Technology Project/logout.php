<?php
session_start();
include('db.php');

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_data = isset($_SESSION['cart']) ? json_encode($_SESSION['cart']) : json_encode([]);
    
    // Save cart to database before logging out
    try {
        $stmt = $conn->prepare("UPDATE user_carts SET cart_data = ? WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $cart_data, $user_id);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                $insert = $conn->prepare("INSERT INTO user_carts (user_id, cart_data) VALUES (?, ?)");
                if ($insert) {
                    $insert->bind_param("is", $user_id, $cart_data);
                    $insert->execute();
                    $insert->close();
                }
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error saving cart on logout: " . $e->getMessage());
    }
}

session_destroy();
echo "Logged out successfully";
?>