<?php
$title = "Бронирование - Гостевой дом 'Уют'";
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$room = null;
if ($room_id) {
    $room = $conn->query("SELECT * FROM rooms WHERE id = $room_id")->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';

    if ($name && $email && $phone && $check_in && $check_out && $room_id) {
        // Проверка занятости дат
        $booked_dates = [];
        $bookings = $conn->query("SELECT check_in, check_out FROM bookings WHERE room_id = $room_id");
        if ($bookings) {
            while ($booking = $bookings->fetch_assoc()) {
                $start = new DateTime($booking['check_in']);
                $end = new DateTime($booking['check_out']);
                $interval = new DateInterval('P1D');
                $dateRange = new DatePeriod($start, $interval, $end);
                foreach ($dateRange as $date) {
                    $booked_dates[] = $date->format('Y-m-d');
                }
            }
        }

        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        $booking_dates = new DatePeriod($check_in_date, new DateInterval('P1D'), $check_out_date);
        $conflict = false;
        foreach ($booking_dates as $date) {
            if (in_array($date->format('Y-m-d'), $booked_dates)) {
                $conflict = true;
                break;
            }
        }

        if (!$conflict) {
            $stmt = $conn->prepare("INSERT INTO bookings (room_id, name, email, phone, check_in, check_out, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isssss", $room_id, $name, $email, $phone, $check_in, $check_out);
            if ($stmt->execute()) {
                header("Location: booking.php?room_id=$room_id&message=Бронирование успешно оформлено");
            } else {
                $error = "Ошибка при бронировании: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Выбранные даты заняты. Пожалуйста, выберите другие даты.";
        }
    } else {
        $error = "Заполните все обязательные поля.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/booking.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="css/chat.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">Гостевой дом 'Уют'</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="rooms.php">Номера</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contacts.php">Контакты</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="news.php">Новости</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1">
            <section class="py-5">
                <div class="container">
                    <h2 class="text-center mb-4">Бронирование</h2>
                    <?php if (isset($_GET['message'])) echo "<div class='alert alert-success'>{$_GET['message']}</div>"; ?>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>{$error}</div>"; ?>

                    <?php if ($room): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($room['name']); ?></h5>
                                <p class="card-text">Цена: <?php echo htmlspecialchars($room['price']); ?> руб./ночь</p>
                                <p class="card-text">Вместимость: <?php echo htmlspecialchars($room['capacity']); ?> чел.</p>
                            </div>
                        </div>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Пожалуйста, введите имя.</div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Пожалуйста, введите корректный email.</div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                                <div class="invalid-feedback">Пожалуйста, введите телефон.</div>
                            </div>
                            <div class="mb-3">
                                <label for="check_in" class="form-label">Дата заезда</label>
                                <input type="text" class="form-control" id="check_in" name="check_in" required>
                                <div class="invalid-feedback">Пожалуйста, выберите дату заезда.</div>
                            </div>
                            <div class="mb-3">
                                <label for="check_out" class="form-label">Дата выезда</label>
                                <input type="text" class="form-control" id="check_out" name="check_out" required>
                                <div class="invalid-feedback">Пожалуйста, выберите дату выезда.</div>
                            </div>
                            <button type="submit" name="book" class="btn btn-primary">Оформить бронирование</button>
                        </form>
                    <?php else: ?>
                        <p class="text-center">Пожалуйста, выберите номер на странице "Номера".</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <footer class="bg-dark text-white text-center py-3">
            <div class="container">
                <p>© <?php echo date("Y"); ?> Гостевой дом 'Уют'. Все права защищены.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#check_in", {
            minDate: "today",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                flatpickr("#check_out").set("minDate", dateStr);
            }
        });
        flatpickr("#check_out", {
            minDate: "today",
            dateFormat: "Y-m-d"
        });

        // Валидация формы
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    <script src="js/chat.js"></script>
</body>
</html>
<?php $conn->close(); ?>