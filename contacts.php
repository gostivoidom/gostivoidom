<?php
$title = 'Контакты - Гостевой дом "Уют"';
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    if ($name && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO feedback_requests (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $name, $email, $message);
        if ($stmt->execute()) {
            $success = "Заявка успешно отправлена!";
        } else {
            $error = "Ошибка при отправке заявки: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Заполните все поля.";
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
    <link href="css/contact.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php"><?php echo $title; ?></a>
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
                            <a class="nav-link active" href="contacts.php">Контакты</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="news.php">Новости</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1">
            <section>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <h2 class="text-center mb-4">Свяжитесь с нами</h2>
                            <p class="text-center">Телефон: +7 (999) 123-45-67</p>
                            <p class="text-center">Email: info@gostivoldom.ru</p>
                            <p class="text-center">Адрес: г. Курск, ул. Примерная, д. 1</p>

                            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                            <h3 class="text-center mt-4">Оставьте заявку на обратную связь</h3>
                            <form method="POST" class="mt-3">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Имя</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Сообщение</label>
                                    <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                                </div>
                                <button type="submit" name="submit_feedback" class="btn btn-primary w-100">Отправить заявку</button>
                            </form>
                        </div>
                    </div>
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
</body>
</html>
<?php $conn->close(); ?>