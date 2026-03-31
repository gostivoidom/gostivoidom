<?php
header("Content-Type: text/html; charset=utf-8");
$title = "Гостевой дом 'Уют'";
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 3"); // Показываем только 3 последние новости
if (!$news) {
    die("Ошибка запроса новостей: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    <link href="css/chat.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo $title; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Главная</a>
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

    <main>
        <section class="full-image text-center">
            <img src="img/uyt.jpg" alt="Гостевой дом 'Уют'" class="img-fluid w-100">
            <div class="overlay-text">
                <h1 class="welcome-text">Добро пожаловать в гостевой дом 'Уют'</h1>
                <div class="about-text">
                    <h3>О нас</h3>
                    <p>Гостевой дом 'Уют' — это место, где природа встречается с комфортом. Уютные номера, домашняя атмосфера и первоклассный сервис ждут вас для идеального отдыха с семьей или друзьями.</p>
                </div>
            </div>
        </section>

        <section class="gallery-section py-5">
            <div class="container">
                <h2 class="text-center mb-4">Галерея</h2>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <div class="col">
                        <div class="gallery-item">
                            <img src="img/1.jpg" alt="Номер 1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="gallery-item">
                            <img src="img/2.jpg" alt="Номер 1">
                        </div>
                    </div>
                    <div class="col">
                        <div class="gallery-item">
                            <img src="img/3.jpg" alt="Номер 1">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="news-section py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-4">Последние новости</h2>
                <?php if ($news->num_rows > 0): ?>
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php while ($new = $news->fetch_assoc()): ?>
                            <div class="col">
                                <div class="news-item">
                                    <h5 class="news-title"><?php echo htmlspecialchars($new['title']); ?></h5>
                                    <p class="news-text"><?php echo htmlspecialchars(substr($new['content'], 0, 100)) . (strlen($new['content']) > 100 ? '...' : ''); ?></p>
                                    <p class="news-date"><small class="text-muted">Опубликовано: <?php echo date('d.m.Y', strtotime($new['created_at'])); ?></small></p>
                                    <?php if ($new['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($new['image_url']); ?>" alt="<?php echo htmlspecialchars($new['title']); ?>" class="news-img">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center">Новостей пока нет.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p>© <?php echo date("Y"); ?> Гостевой дом 'Уют'. Все права защищены.</p>
        </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/chat.js"></script>
</body>
</html>
<?php $conn->close(); ?>