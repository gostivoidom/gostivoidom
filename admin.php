<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$title = 'Админ-панель - Гостевой дом "Уют"';
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

include 'db/connect.php';
if (!$conn || $conn->connect_error) {
    die("Ошибка подключения к базе данных: " . ($conn->connect_error ?? "Переменная \$conn не определена"));
}
echo "<!-- Debug: Подключение к базе успешно -->";

// Получение данных из базы
$rooms = $conn->prepare("SELECT * FROM rooms");
$rooms->execute();
$rooms = $rooms->get_result();
if (!$rooms) {
    die("Ошибка запроса номеров: " . $conn->error);
}
echo "<!-- Debug: Запрос номеров выполнен, строк: " . $rooms->num_rows . " -->";

$news = $conn->prepare("SELECT * FROM news ORDER BY created_at DESC");
$news->execute();
$news = $news->get_result();
if (!$news) {
    die("Ошибка запроса новостей: " . $conn->error);
}
echo "<!-- Debug: Запрос новостей выполнен, строк: " . $news->num_rows . " -->";

$booked_dates = [];
$bookings = $conn->prepare("SELECT check_in, check_out FROM bookings WHERE status = 'confirmed'");
$bookings->execute();
$bookings = $bookings->get_result();
if (!$bookings) {
    echo "<!-- Debug: Ошибка запроса бронирований: " . $conn->error . " -->";
} else {
    echo "<!-- Debug: Запрос бронирований выполнен, строк: " . $bookings->num_rows . " -->";
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
$booked_dates_json = json_encode($booked_dates);

$bookings_list = $conn->prepare("SELECT b.id, r.name AS room_name, b.name AS customer_name, b.email, b.phone, b.check_in, b.check_out, b.status FROM bookings b LEFT JOIN rooms r ON b.room_id = r.id ORDER BY b.check_in DESC");
$bookings_list->execute();
$bookings_list = $bookings_list->get_result();
if (!$bookings_list) {
    echo "<!-- Debug: Ошибка запроса списка бронирований: " . $conn->error . " -->";
} else {
    echo "<!-- Debug: Запрос списка бронирований выполнен, строк: " . $bookings_list->num_rows . " -->";
}

$feedbacks = $conn->prepare("SELECT * FROM feedback_requests ORDER BY created_at DESC");
$feedbacks->execute();
$feedbacks = $feedbacks->get_result();
if (!$feedbacks) {
    echo "<!-- Debug: Ошибка запроса заявок: " . $conn->error . " -->";
} else {
    echo "<!-- Debug: Запрос заявок выполнен, строк: " . $feedbacks->num_rows . " -->";
}

// Обработка подтверждения бронирования
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $booking_id = (int)$_POST['booking_id'];
    $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        header("Location: admin.php?message=" . urlencode("Бронирование успешно подтверждено"));
    } else {
        header("Location: admin.php?message=" . urlencode("Ошибка подтверждения: " . $conn->error));
    }
    $stmt->close();
    exit;
}

// Обработка удаления бронирования
if (isset($_GET['delete_booking']) && is_numeric($_GET['delete_booking'])) {
    $booking_id = (int)$_GET['delete_booking'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        header("Location: admin.php?message=" . urlencode("Бронирование успешно удалено"));
    } else {
        header("Location: admin.php?message=" . urlencode("Ошибка при удалении: " . $conn->error));
    }
    $stmt->close();
    exit;
}

// Обработка удаления номера
if (isset($_GET['delete_room']) && is_numeric($_GET['delete_room'])) {
    $room_id = (int)$_GET['delete_room'];
    $stmt = $conn->prepare("SELECT image_url FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    $old_image_url = $room['image_url'] ?? '';
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    if ($stmt->execute()) {
        if ($old_image_url && file_exists($old_image_url)) {
            unlink($old_image_url);
            echo "<!-- Изображение $old_image_url удалено -->";
        }
        header("Location: admin.php?message=" . urlencode("Номер успешно удален"));
    } else {
        header("Location: admin.php?message=" . urlencode("Ошибка при удалении номера: " . $conn->error));
    }
    $stmt->close();
    exit;
}

// Обработка удаления новости
if (isset($_GET['delete_news']) && is_numeric($_GET['delete_news'])) {
    $news_id = (int)$_GET['delete_news'];
    $stmt = $conn->prepare("SELECT image_url FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
    $old_image_url = $news['image_url'] ?? '';
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    if ($stmt->execute()) {
        if ($old_image_url && file_exists($old_image_url)) {
            unlink($old_image_url);
            echo "<!-- Изображение $old_image_url удалено -->";
        }
        header("Location: admin.php?message=" . urlencode("Новость успешно удалена"));
    } else {
        header("Location: admin.php?message=" . urlencode("Ошибка при удалении новости: " . $conn->error));
    }
    $stmt->close();
    exit;
}

// Обработка удаления заявки на обратную связь
if (isset($_GET['delete_feedback']) && is_numeric($_GET['delete_feedback'])) {
    $feedback_id = (int)$_GET['delete_feedback'];
    $stmt = $conn->prepare("DELETE FROM feedback_requests WHERE id = ?");
    $stmt->bind_param("i", $feedback_id);
    if ($stmt->execute()) {
        header("Location: admin.php?message=" . urlencode("Заявка успешно удалена"));
    } else {
        header("Location: admin.php?message=" . urlencode("Ошибка при удалении заявки: " . $conn->error));
    }
    $stmt->close();
    exit;
}

// Обработка добавления/обновления номера с загрузкой изображения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['add_room']) || isset($_POST['edit_room']))) {
    $room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $capacity = $_POST['capacity'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_url = '';

    $upload_dir = "images/";
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        $error = "Не удалось создать папку $upload_dir. Проверьте права доступа.";
        echo "<!-- Ошибка: $error -->";
    } elseif (!is_writable($upload_dir)) {
        $error = "Папка $upload_dir не доступна для записи. Проверьте права доступа.";
        echo "<!-- Ошибка: $error -->";
    }

    $image_file = $_FILES['image_file'] ?? null;
    $old_image_url = '';
    if ($room_id) {
        $stmt = $conn->prepare("SELECT image_url FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $old_room = $stmt->get_result()->fetch_assoc();
        $old_image_url = $old_room['image_url'] ?? '';
        $stmt->close();
    }

    if ($image_file && $image_file['error'] == UPLOAD_ERR_OK) {
        $file_name = basename($image_file['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;
            if (move_uploaded_file($image_file['tmp_name'], $target_file)) {
                $image_url = $target_file;
                if ($old_image_url && file_exists($old_image_url)) {
                    unlink($old_image_url);
                    echo "<!-- Старое изображение $old_image_url удалено -->";
                }
                echo "<!-- Новое изображение сохранено как $target_file -->";
            } else {
                $error = "Ошибка при загрузке изображения в $target_file. Проверьте права или размер файла.";
                echo "<!-- Ошибка: $error, error_code: " . $image_file['error'] . " -->";
            }
        } else {
            $error = "Недопустимый формат файла. Используйте .jpg, .jpeg, .png, .gif.";
            echo "<!-- Ошибка: $error -->";
        }
    } elseif ($old_image_url) {
        $image_url = $old_image_url;
        echo "<!-- Сохранено старое изображение: $image_url -->";
    } else {
        echo "<!-- Нет нового изображения, image_url остается пустым -->";
    }

    if ($name && $price && $capacity) {
        if ($room_id) {
            $stmt = $conn->prepare("UPDATE rooms SET name = ?, price = ?, capacity = ?, description = ?, image_url = ? WHERE id = ?");
            $stmt->bind_param("sdissi", $name, $price, $capacity, $description, $image_url, $room_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO rooms (name, price, capacity, description, image_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdiss", $name, $price, $capacity, $description, $image_url);
        }
        if ($stmt->execute()) {
            $last_id = $room_id ?: $conn->insert_id;
            echo "<!-- Данные сохранены в базу для room_id = $last_id, image_url = $image_url -->";
            $message = $room_id ? "Номер успешно обновлен" : "Номер успешно добавлен";
            header("Location: admin.php?message=" . urlencode($message));
        } else {
            $error = "Ошибка сохранения в базу: " . $conn->error;
            echo "<!-- Ошибка: $error -->";
        }
        $stmt->close();
    } else {
        $error = "Заполните все обязательные поля.";
        echo "<!-- Ошибка: $error -->";
    }
}

// Обработка добавления/обновления новости с загрузкой изображения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    $upload_dir = "images/";
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        $error = "Не удалось создать папку $upload_dir. Проверьте права доступа.";
        echo "<!-- Ошибка: $error -->";
    } elseif (!is_writable($upload_dir)) {
        $error = "Папка $upload_dir не доступна для записи. Проверьте права доступа.";
        echo "<!-- Ошибка: $error -->";
    }

    $image_file = $_FILES['image_file'] ?? null;
    $old_image_url = '';
    if ($news_id) {
        $stmt = $conn->prepare("SELECT image_url FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $old_news = $stmt->get_result()->fetch_assoc();
        $old_image_url = $old_news['image_url'] ?? '';
        $stmt->close();
    }

    $image_url = $old_image_url;
    if ($image_file && $image_file['error'] == UPLOAD_ERR_OK) {
        $file_name = basename($image_file['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $new_file_name;
            if (move_uploaded_file($image_file['tmp_name'], $target_file)) {
                $image_url = $target_file;
                if ($old_image_url && file_exists($old_image_url)) {
                    unlink($old_image_url);
                    echo "<!-- Старое изображение $old_image_url удалено -->";
                }
                echo "<!-- Новое изображение сохранено как $target_file -->";
            } else {
                $error = "Ошибка при загрузке изображения в $target_file. Проверьте права или размер файла.";
                echo "<!-- Ошибка: $error, error_code: " . $image_file['error'] . " -->";
            }
        } else {
            $error = "Недопустимый формат файла. Используйте .jpg, .jpeg, .png, .gif.";
            echo "<!-- Ошибка: $error -->";
        }
    }

    if ($title && $content) {
        if ($news_id) {
            $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, image_url = ? WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $image_url, $news_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO news (title, content, image_url, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $title, $content, $image_url);
        }
        if ($stmt->execute()) {
            $last_id = $news_id ?: $conn->insert_id;
            echo "<!-- Данные сохранены в базу для news_id = $last_id, image_url = $image_url -->";
            $message = $news_id ? "Новость успешно обновлена" : "Новость успешно добавлена";
            header("Location: admin.php?message=" . urlencode($message));
        } else {
            $error = "Ошибка сохранения в базу: " . $conn->error;
            echo "<!-- Ошибка: $error -->";
        }
        $stmt->close();
    } else {
        $error = "Заполните все обязательные поля (заголовок и текст новости).";
        echo "<!-- Ошибка: $error -->";
    }
}

// Обработка редактирования новости
$edit_news = null;
if (isset($_GET['edit_news']) && is_numeric($_GET['edit_news'])) {
    $news_id = (int)$_GET['edit_news'];
    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $edit_news = $stmt->get_result()->fetch_assoc();
    if (!$edit_news) {
        header("Location: admin.php?message=" . urlencode("Новость не найдена"));
        exit;
    }
    $stmt->close();
}

// Обработка редактирования номера
$edit_room = null;
if (isset($_GET['edit_room']) && is_numeric($_GET['edit_room'])) {
    $room_id = (int)$_GET['edit_room'];
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $edit_room = $stmt->get_result()->fetch_assoc();
    if (!$edit_room) {
        header("Location: admin.php?message=" . urlencode("Номер не найден"));
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .booked-day {
            background-color: #ffcccc !important;
            cursor: not-allowed;
        }
        html {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }
        body {
            -webkit-text-align: -webkit-match-parent;
            text-align: match-parent;
        }
        .card-img-top {
            max-width: 150px; /* Уменьшен размер изображений */
            height: auto;
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
                    <h2 class="text-center mb-4">Админ-панель</h2>
                    <?php if (isset($_GET['message'])) echo "<div class='alert alert-info'>" . htmlspecialchars(urldecode($_GET['message'])) . "</div>"; ?>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>"; ?>

                    <h3 class="mb-3">Календарь бронирований</h3>
                    <div class="mb-4">
                        <div id="calendar-container"></div>
                    </div>

                    <h3 class="mb-3">Управление номерами</h3>
                    <form method="POST" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="<?php echo isset($_GET['edit_room']) ? 'edit_room' : 'add_room'; ?>" value="1">
                        <?php if (isset($_GET['edit_room']) && is_numeric($_GET['edit_room'])): ?>
                            <input type="hidden" name="room_id" value="<?php echo (int)$_GET['edit_room']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="name" class="form-label">Название номера</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_room['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Цена (руб./ночь)</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($edit_room['price'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Вместимость (чел.)</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="<?php echo htmlspecialchars($edit_room['capacity'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo htmlspecialchars($edit_room['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image_file" class="form-label">Изображение</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept=".jpg,.jpeg,.png,.gif">
                            <?php if (isset($edit_room['image_url']) && $edit_room['image_url']): ?>
                                <p class="mt-2">Текущее изображение: <a href="<?php echo htmlspecialchars($edit_room['image_url']); ?>" target="_blank"><?php echo basename($edit_room['image_url']); ?></a></p>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo isset($_GET['edit_room']) ? 'Обновить номер' : 'Добавить номер'; ?></button>
                    </form>
                    <div class="mt-3">
                        <?php
                        $rooms->data_seek(0);
                        while ($room = $rooms->fetch_assoc()): ?>
                            <div class="card mb-2">
                                <?php if ($room['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($room['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($room['name']); ?>" style="max-width: 150px; height: auto;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($room['name']); ?></h5>
                                    <p class="card-text">Цена: <?php echo htmlspecialchars($room['price']); ?> руб./ночь</p>
                                    <p class="card-text">Вместимость: <?php echo htmlspecialchars($room['capacity']); ?> чел.</p>
                                    <p class="card-text">Описание: <?php echo htmlspecialchars($room['description'] ?? 'Не указано'); ?></p>
                                    <a href="admin.php?edit_room=<?php echo $room['id']; ?>" class="btn btn-warning btn-sm">Редактировать</a>
                                    <a href="admin.php?delete_room=<?php echo $room['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить этот номер?');">Удалить</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <h3 class="mb-3">Управление новостями</h3>
                    <form method="POST" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="news_id" value="<?php echo $edit_news['id'] ?? ''; ?>">
                        <input type="hidden" name="add_news" value="1">
                        <div class="mb-3">
                            <label for="title" class="form-label">Заголовок</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($edit_news['title'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Текст новости</label>
                            <textarea class="form-control" id="content" name="content" rows="3" required><?php echo htmlspecialchars($edit_news['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image_file" class="form-label">Изображение</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept=".jpg,.jpeg,.png,.gif">
                            <?php if (isset($edit_news['image_url']) && $edit_news['image_url']): ?>
                                <p class="mt-2">Текущее изображение: <a href="<?php echo htmlspecialchars($edit_news['image_url']); ?>" target="_blank"><?php echo basename($edit_news['image_url']); ?></a></p>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $edit_news ? 'Обновить новость' : 'Добавить новость'; ?></button>
                    </form>
                    <div class="mt-3">
                        <?php
                        $news->data_seek(0);
                        while ($news_item = $news->fetch_assoc()): ?>
                            <div class="card mb-2">
                                <?php if ($news_item['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($news_item['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news_item['title']); ?>" style="max-width: 150px; height: auto;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($news_item['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($news_item['content']); ?></p>
                                    <p class="card-text"><small class="text-muted">Дата: <?php echo htmlspecialchars($news_item['created_at']); ?></small></p>
                                    <a href="admin.php?edit_news=<?php echo $news_item['id']; ?>" class="btn btn-warning btn-sm">Редактировать</a>
                                    <a href="admin.php?delete_news=<?php echo $news_item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить эту новость?');">Удалить</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <h3 class="mb-3">Список бронирований</h3>
                    <div class="mt-3">
                        <?php
                        $bookings_list->data_seek(0);
                        while ($booking = $bookings_list->fetch_assoc()): ?>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5 class="card-title">Бронирование #<?php echo htmlspecialchars($booking['id']); ?></h5>
                                    <p class="card-text">Номер: <?php echo htmlspecialchars($booking['room_name'] ?? 'Не указан'); ?></p>
                                    <p class="card-text">Клиент: <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                    <p class="card-text">Email: <?php echo htmlspecialchars($booking['email']); ?></p>
                                    <p class="card-text">Телефон: <?php echo htmlspecialchars($booking['phone']); ?></p>
                                    <p class="card-text">Заезд: <?php echo htmlspecialchars($booking['check_in']); ?></p>
                                    <p class="card-text">Выезд: <?php echo htmlspecialchars($booking['check_out']); ?></p>
                                    <p class="card-text">Статус: <?php echo htmlspecialchars($booking['status']); ?></p>
                                    <?php if ($booking['status'] !== 'confirmed'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="confirm_booking" value="1">
                                            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['id']); ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Подтвердить</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="admin.php?delete_booking=<?php echo $booking['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить это бронирование?');">Удалить</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <h3 class="mb-3">Заявки на обратную связь</h3>
                    <div class="mt-3">
                        <?php
                        $feedbacks->data_seek(0);
                        while ($feedback = $feedbacks->fetch_assoc()): ?>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h5 class="card-title">Заявка от <?php echo htmlspecialchars($feedback['name'] ?? 'Не указано'); ?></h5>
                                    <p class="card-text">Email: <?php echo htmlspecialchars($feedback['email'] ?? 'Не указан'); ?></p>
                                    <p class="card-text">Сообщение: <?php echo htmlspecialchars($feedback['message'] ?? 'Не указано'); ?></p>
                                    <p class="card-text"><small class="text-muted">Дата: <?php echo htmlspecialchars($feedback['created_at'] ?? 'Не указано'); ?></small></p>
                                    <a href="mailto:<?php echo htmlspecialchars($feedback['email'] ?? ''); ?>" class="btn btn-primary btn-sm">Ответить</a>
                                    <a href="admin.php?delete_feedback=<?php echo $feedback['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить эту заявку?');">Удалить</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-light text-center py-3 mt-auto">
            <p>&copy; 2025 Гостевой дом 'Уют'. Все права защищены.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#calendar-container", {
            inline: true,
            minDate: "today",
            disable: <?php echo $booked_dates_json; ?>.map(date => ({
                from: new Date(date),
                to: new Date(date)
            })),
            onChange: function(selectedDates, dateStr, instance) {
                console.log("Выбрана дата:", dateStr);
            }
        });
    </script>
</body>
</html>