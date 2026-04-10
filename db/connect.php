<?php
$host = getenv('DB_HOST') ?: "gostivoidom-gostivoidom-9ef7.h.aivencloud.com";
$db_user = getenv('DB_USER') ?: "avnadmin";
$db_pass = getenv('DB_PASS') ?: "AVNS_LUWCD8eByJyWVQwig1O";
$db_name = getenv('DB_NAME') ?: "defaultdb";
$port = getenv('DB_PORT') ?: 22346;

$conn = new mysqli();
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL); // enable SSL without CA cert
$conn->real_connect($host, $db_user, $db_pass, $db_name, $port, NULL, MYSQLI_CLIENT_SSL);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
$conn->query("SET NAMES utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS feedback_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT NULL
)");
$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT NULL
)");
$conn->query("CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255)
)");
$conn->query("CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT NULL
)");
?>
