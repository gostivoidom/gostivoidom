<?php
$title = "Номера - Гостевой дом «Уют»";
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

$capacity_filter = $_GET['capacity'] ?? '';
$price_filter = $_GET['price'] ?? '';

$where = [];
if ($capacity_filter === '1') {
    $where[] = 'capacity = 1';
} elseif ($capacity_filter === '2') {
    $where[] = 'capacity = 2';
} elseif ($capacity_filter === '3-4') {
    $where[] = 'capacity >= 3 AND capacity <= 4';
} elseif ($capacity_filter === '5+') {
    $where[] = 'capacity >= 5';
}

if ($price_filter === 'low') {
    $where[] = 'price <= 3000';
} elseif ($price_filter === 'mid') {
    $where[] = 'price > 3000 AND price <= 6000';
} elseif ($price_filter === 'high') {
    $where[] = 'price > 6000';
}

$sql = "SELECT * FROM rooms";
if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$rooms = $conn->query($sql);
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
        .description-list {
            padding-left: 0;
            margin-bottom: 10px;
        }
        .description-list ul {
            list-style-type: disc;
            padding-left: 20px;
            margin: 0;
        }
        .description-list li {
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .filter-bar {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }
        .filter-bar .btn-filter {
            margin: 3px;
        }
        .btn-filter.active {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">Гостевой дом «Уют»</a>
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

                    <div class="filter-bar">
                        <div class="row align-items-center g-2">
                            <div class="col-auto">
                                <strong>Вместимость:</strong>
                            </div>
                            <div class="col-auto">
                                <a href="rooms.php?price_filter=<?php echo urlencode($price_filter); ?>"
                                   class="btn btn-outline-primary btn-sm btn-filter <?php echo $capacity_filter === '' ? 'active' : ''; ?>">
                                    Все
                                </a>
                                <a href="rooms.php?capacity=1&price_filter=<?php echo urlencode($price_filter); ?>"
                                   class="btn btn-outline-primary btn-sm btn-filter <?php echo $capacity_filter === '1' ? 'active' : ''; ?>">
                                    Одноместный
                                </a>
                                <a href="rooms.php?capacity=2&price_filter=<?php echo urlencode($price_filter); ?>"
                                   class="btn btn-outline-primary btn-sm btn-filter <?php echo $capacity_filter === '2' ? 'active' : ''; ?>">
                                    Двухместный
                                </a>
                                <a href="rooms.php?capacity=3-4&price_filter=<?php echo urlencode($price_filter); ?>"
                                   class="btn btn-outline-primary btn-sm btn-filter <?php echo $capacity_filter === '3-4' ? 'active' : ''; ?>">
                                    Семейный (3–4 чел.)
                                </a>
                                <a href="rooms.php?capacity=5%2B&price_filter=<?php echo urlencode($price_filter); ?>"
                                   class="btn btn-outline-primary btn-sm btn-filter <?php echo $capacity_filter === '5+' ? 'active' : ''; ?>">
                                    Большой (5+ чел.)
                                </a>
                            </div>
                            <div class="col-auto ms-md-3">
                                <strong>Цена:</strong>
                            </div>
                            <div class="col-auto">
                                <a href="rooms.php?capacity=<?php echo urlencode($capacity_filter); ?>"
                                   class="btn btn-outline-secondary btn-sm btn-filter <?php echo $price_filter === '' ? 'active' : ''; ?>">
                                    Любая
                                </a>
                                <a href="rooms.php?capacity=<?php echo urlencode($capacity_filter); ?>&price_filter=low"
                                   class="btn btn-outline-secondary btn-sm btn-filter <?php echo $price_filter === 'low' ? 'active' : ''; ?>">
                                    до 3 000 руб.
                                </a>
                                <a href="rooms.php?capacity=<?php echo urlencode($capacity_filter); ?>&price_filter=mid"
                                   class="btn btn-outline-secondary btn-sm btn-filter <?php echo $price_filter === 'mid' ? 'active' : ''; ?>">
                                    3 000–6 000 руб.
                                </a>
                                <a href="rooms.php?capacity=<?php echo urlencode($capacity_filter); ?>&price_filter=high"
                                   class="btn btn-outline-secondary btn-sm btn-filter <?php echo $price_filter === 'high' ? 'active' : ''; ?>">
                                    от 6 000 руб.
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if ($rooms->num_rows === 0): ?>
                        <p class="text-center text-muted">Номера по выбранным фильтрам не найдены.</p>
                    <?php else: ?>
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
                                            $description_items = array_filter(explode("\n", $description));
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
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <footer class="bg-dark text-white text-center py-3">
            <div class="container">
                <p>© <?php echo date("Y"); ?> Гостевой дом «Уют». Все права защищены.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/chat.js"></script>
</body>
</html>
<?php $conn->close(); ?>
