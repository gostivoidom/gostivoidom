<?php
$host = getenv('DB_HOST') ?: "sql7.freesqldatabase.com";
$db_user = getenv('DB_USER') ?: "sql7821811";
$db_pass = getenv('DB_PASS') ?: "w6Ez8DvgsT";
$db_name = getenv('DB_NAME') ?: "sql7821811";

$conn = new mysqli($host, $db_user, $db_pass, $db_name, 3306);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$conn->set_charset("utf8");
$conn->query("CREATE TABLE IF NOT EXISTS feedback_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL,
    image_url VARCHAR(255)
)");

$conn->query("CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>