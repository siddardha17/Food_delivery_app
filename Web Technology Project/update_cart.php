<?php
include('db.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'];
    
    try {
        // First try to update existing cart
        $stmt = $conn->prepare("UPDATE user_carts SET cart_data = ? WHERE user_id = ?");
        if ($stmt) {
            $cart_data = json_encode($data['cart']);
            $stmt->bind_param("si", $cart_data, $user_id);
            $stmt->execute();
            
            // If no rows were updated, insert new cart
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
        error_log("Error updating cart: " . $e->getMessage());
    }
    
    // Always update session
    $_SESSION['cart'] = $data['cart'];
    $_SESSION['applied_coupon'] = $data['applied_coupon'];
    $_SESSION['discount_percentage'] = $data['discount_percentage'];
    
    echo "Cart updated";
}
?>