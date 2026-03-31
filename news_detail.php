<?php
header('Content-Type: text/html; charset=utf-8');
$title = "Новость - Гостевой дом 'Уют'";
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных");
}

$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$news_id) {
    header("Location: news.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $news_id);
$stmt->execute();
$news_item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$news_item) {
    header("Location: news.php");
    exit;
}

$title = htmlspecialchars($news_item['title']) . " - Гостевой дом 'Уют'";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/news.css" rel="stylesheet">
    <link href="css/chat.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">Гостевой дом 'Уют'</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Главная</a></li>
                        <li class="nav-item"><a class="nav-link" href="rooms.php">Номера</a></li>
                        <li class="nav-item"><a class="nav-link" href="contacts.php">Контакты</a></li>
                        <li class="nav-item"><a class="nav-link active" href="news.php">Новости</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1">
            <section class="py-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <a href="news.php" class="btn btn-outline-secondary mb-3">&larr; Назад к новостям</a>
                            <?php if ($news_item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($news_item['image_url']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($news_item['title']); ?>">
                            <?php endif; ?>
                            <h2><?php echo htmlspecialchars($news_item['title']); ?></h2>
                            <p class="text-muted"><?php echo $news_item['created_at'] ? date('d.m.Y', strtotime($news_item['created_at'])) : ''; ?></p>
                            <div class="news-content mt-3">
                                <?php echo nl2br(htmlspecialchars($news_item['content'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-dark text-white text-center py-3">
            <div class="container">
                <p>&copy; <?php echo date("Y"); ?> Гостевой дом 'Уют'. Все права защищены.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/chat.js"></script>
</body>
</html>
<?php $conn->close(); ?>
