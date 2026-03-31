<?php
$title = "Номера - Гостевой дом 'Уют'";
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

$rooms = $conn->query("SELECT * FROM rooms");
if (!$rooms) {
    die("Ошибка запроса номеров: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/rooms.css" rel="stylesheet">
    <link href="css/chat.css" rel="stylesheet">
    <style>
        /* Дополнительный стиль для управления отступами и внешним видом списка */
        .description-list {
            padding-left: 0; /* Прижатие к левому краю карточки */
            margin-bottom: 10px;
        }
        .description-list ul {
            list-style-type: disc; /* Убедимся, что используются точки */
            padding-left: 20px; /* Отступ для точек */
            margin: 0; /* Убираем стандартный отступ */
        }
        .description-list li {
            font-size: 0.9em; /* Уменьшенный шрифт */
            margin-bottom: 5px;
        }
    </style>
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
                            <a class="nav-link active" href="rooms.php">Номера</a>
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
                    <h2 class="text-center mb-4">Наши номера</h2>
                    <div class="row">
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <?php
                                    $image_url = $room['image_url'] ?? '';
                                    if ($image_url): ?>
                                        <img src="<?php echo htmlspecialchars($image_url); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($room['name']); ?>">
                                    <?php else: ?>
                                        <div class="card-img-top bg-secondary text-white text-center py-3">Нет изображения</div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($room['name']); ?></h5>
                                        <p class="card-text">Цена: <?php echo htmlspecialchars($room['price']); ?> руб./ночь</p>
                                        <p class="card-text">Вместимость: <?php echo htmlspecialchars($room['capacity']); ?> чел.</p>
                                        <div class="description-list">
                                            <?php
                                            $description = $room['description'] ?? '';
                                            $description_items = array_filter(explode("\n", $description)); // Разделяем по новой строке
                                            if (!empty($description_items)): ?>
                                                <ul>
                                                    <?php foreach ($description_items as $item): ?>
                                                        <li><?php echo htmlspecialchars(trim($item)); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="card-text">Описание: Не указано</p>
                                            <?php endif; ?>
                                        </div>
                                        <a href="booking.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">Забронировать</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
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
    <script src="js/chat.js"></script>
</body>
</html>
<?php $conn->close(); ?>