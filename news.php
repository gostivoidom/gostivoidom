<?php
header('Content-Type: text/html; charset=utf-8');    
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$title = "Новости - Гостевой дом 'Уют'";
include 'db/connect.php';
if (!isset($conn) || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . (isset($conn) ? $conn->connect_error : "Переменная \$conn не определена"));
}

$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
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
    <link href="css/news.css" rel="stylesheet">
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
                            <a class="nav-link active" href="news.php">Новости</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1">
            <section class="py-5">
                <div class="container">
                    <h2 class="text-center mb-4">Новости</h2>
                    <?php if (isset($_GET['message'])) echo "<div class='alert alert-info'>{$_GET['message']}</div>"; ?>

                    <div class="row">
                        <?php $news->data_seek(0); while ($new = $news->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <?php if ($new['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($new['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($new['title']); ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($new['title']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($new['content'], 0, 150)) . (strlen($new['content']) > 150 ? '...' : ''); ?></p>
                                        <p class="card-text"><small class="text-muted">Опубликовано: <?php echo date('d.m.Y', strtotime($new['created_at'])); ?></small></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <?php if ($news->num_rows === 0): ?>
                        <p class="text-center">Новостей пока нет.</p>
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
    <script src="js/chat.js"></script>
</body>
</html>
<?php $conn->close(); ?>