<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = (int)$_POST['room_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $check_in = $conn->real_escape_string($_POST['check_in']);
    $check_out = $conn->real_escape_string($_POST['check_out']);

    $sql = "INSERT INTO bookings (room_id, name, email, phone, check_in, check_out, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $room_id, $name, $email, $phone, $check_in, $check_out);

    if ($stmt->execute()) {
        header("Location: booking.php?room_id=$room_id&success=1");
    } else {
        die("Ошибка при бронировании: " . $conn->error);
    }
    $stmt->close();
}
$conn->close();
?>