<?php
include('db.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                
                // Load user's cart from database
                $cart_sql = "SELECT cart_data FROM user_carts WHERE user_id = ?";
                $cart_stmt = $conn->prepare($cart_sql);
                if ($cart_stmt) {
                    $cart_stmt->bind_param("i", $id);
                    $cart_stmt->execute();
                    $cart_result = $cart_stmt->get_result();
                    
                    if ($cart_result->num_rows > 0) {
                        $cart_data = $cart_result->fetch_assoc();
                        $_SESSION['cart'] = json_decode($cart_data['cart_data'], true);
                    } else {
                        $_SESSION['cart'] = [];
                    }
                    $cart_stmt->close();
                }
                
                echo "Login successful!";
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
    } else {
        echo "Error executing query.";
    }

    $stmt->close();
    $conn->close();
}
?>