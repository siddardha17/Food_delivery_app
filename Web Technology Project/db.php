<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "food_delivery";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create user_carts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS user_carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cart_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    // Don't die if table creation fails, just log it
    error_log("Error creating user_carts table: " . $conn->error);
}
?>