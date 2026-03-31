-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Сен 18 2025 г., 12:32
-- Версия сервера: 5.7.24
-- Версия PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `guesthouse_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(1, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99');

-- --------------------------------------------------------

--
-- Структура таблицы `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `name`, `email`, `phone`, `check_in`, `check_out`, `created_at`, `status`) VALUES
(1, 2, 'Иван Петров', 'ivan.petrov@example.com', '+79161234567', '2025-09-20', '2025-09-25', '2025-09-18 09:30:00', 'confirmed'),
(2, 1, 'Мария Сидорова', 'maria.sidorova@example.com', '+79031234568', '2025-09-22', '2025-09-24', '2025-09-17 14:20:00', 'confirmed'),
(3, 3, 'Алексей Козлов', 'alexey.kozlov@example.com', '+79261234569', '2025-10-01', '2025-10-07', '2025-09-16 11:45:00', 'pending'),
(4, 2, 'Елена Волкова', 'elena.volkova@example.com', '+79171234570', '2025-09-30', '2025-10-05', '2025-09-15 16:10:00', 'cancelled');

-- --------------------------------------------------------

--
-- Структура таблицы `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Дмитрий Смирнов', 'dmitry.smirnov@example.com', 'Здравствуйте! Хотел бы узнать о возможности проведения корпоративного мероприятия в вашем гостевом доме на 50 человек. Какие условия вы можете предложить?', '2025-09-15 08:30:00'),
(2, 'Ольга Новикова', 'olga.novikova@example.com', 'Добрый день! Интересует информация о наличии парковки для гостей и её стоимости. Спасибо.', '2025-09-16 12:15:00'),
(3, 'Павел Иванов', 'pavel.ivanov@example.com', 'Здравствуйте! Планируем семейный отдых с детьми. Есть ли в номерах детские кроватки и высокие стулья для кормления?', '2025-09-17 15:40:00'),
(4, 'Анна Ковалева', 'anna.kovaleva@example.com', 'Добрый вечер! Подскажите, есть ли в вашем гостевом доме доступ для людей с ограниченными возможностями?', '2025-09-18 10:20:00');

-- --------------------------------------------------------

--
-- Структура таблицы `feedback_requests`
--

CREATE TABLE `feedback_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `feedback_requests`
--

INSERT INTO `feedback_requests` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Сергей Морозов', 'sergey.morozov@example.com', 'Отличный сервис и уютные номера! Особенно понравился завтрак. Обязательно вернемся снова.', '2025-09-10 14:30:00'),
(2, 'Наталья Павлова', 'natalya.pavlova@example.com', 'Прекрасное место для отдыха! Чистота, комфорт и доброжелательный персонал. Рекомендую всем!', '2025-09-12 11:45:00'),
(3, 'Михаил Лебедев', 'mikhail.lebedev@example.com', 'Хорошее соотношение цена/качество. Единственное пожелание - добавить больше розеток в номере.', '2025-09-14 16:20:00'),
(4, 'Татьяна Федорова', 'tatiana.fedorova@example.com', 'Отдыхали семьей, все было прекрасно! Дети в восторге от игровой площадки. Спасибо за теплый прием!', '2025-09-16 09:15:00');

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `created_at`, `image_url`) VALUES
(1, 'Открытие нового сезона', 'Мы рады сообщить, что новый сезон бронирований открыт! Ждем вас с 1 августа 2025 года.', '2025-07-30 15:44:25', NULL),
(2, 'Скидки на лето', 'Получите 20% скидку на бронирование до конца июля. Спешите!', '2025-07-30 15:44:25', NULL),
(3, 'Новая услуга: трансфер от аэропорта', 'Теперь мы предлагаем услугу трансфера от аэропорта до нашего гостевого дома. Удобно, быстро и комфортно!', '2025-08-15 10:30:00', 'images/transfer.jpg'),
(4, 'Ремонт завершен', 'Завершился капитальный ремонт бассейна и спа-зоны. Теперь наши гости могут наслаждаться обновленными facilities.', '2025-09-01 14:20:00', 'images/pool_renovation.jpg'),
(5, 'Мастер-классы по кулинарии', 'Каждую субботу наши шеф-повара проводят мастер-классы по приготовлению местных блюд. Присоединяйтесь!', '2025-09-10 11:00:00', 'images/cooking_class.jpg'),
(6, 'Экологическая инициатива', 'Мы перешли на использование биоразлагаемых материалов и установили солнечные панели для более экологичного туризма.', '2025-09-18 09:45:00', 'images/eco_initiative.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `price`, `capacity`, `description`, `image_url`) VALUES
(1, 'Стандартный номер', '2500.00', 2, 'Уютный номер на 2 человека', 'images/688cbd7909531.jpg'),
(2, 'Семейный номер', '4500.00', 4, 'Просторный номер на 4 человека', 'images/688cd087aa12c.jpg'),
(3, 'Люкс', '7000.00', 2, 'Роскошный номер с видом на природу. Двуспальная кровать, кондиционер, мини-бар, ванна и душевая кабина, сейф.', 'images/688cd090976f3.jpg'),
(4, 'Делюкс с балконом', '3800.00', 2, 'Просторный номер с собственным балконом и видом на сад. Идеально для романтического отдыха.', 'images/deluxe_balcony.jpg'),
(5, 'Эконом номер', '1800.00', 1, 'Компактный и уютный номер для одного гостя. Все необходимое для комфортного проживания.', 'images/economy_room.jpg'),
(6, 'Семейный люкс', '8500.00', 6, 'Просторный двухкомнатный номер для большой семьи. Гостиная и две спальни.', 'images/family_suite.jpg'),
(7, 'Номер с камином', '5500.00', 2, 'Уютный номер с камином. Особенно комфортно в холодное время года.', 'images/fireplace_room.jpg'),
(8, 'Бизнес номер', '4200.00', 2, 'Номер для деловых путешественников с рабочим столом, оргтехникой и хорошим Wi-Fi.', 'images/business_room.jpg'),
(9, 'Номер с джакузи', '6500.00', 2, 'Романтический номер с гидромассажной ванной. Идеально для особых occasions.', 'images/jacuzzi_room.jpg'),
(10, 'Молодежный номер', '3200.00', 4, 'Яркий современный номер для молодежи. Несколько отдельных спальных мест.', 'images/youth_room.jpg'),
(11, 'Номер для новобрачных', '7500.00', 2, 'Специально оформленный номер для молодоженов с романтическими деталями.', 'images/honeymoon_room.jpg'),
(12, 'Номер с видом на море', '4800.00', 2, 'Номер с панорамным видом на море. Незабываемые пейзажи каждое утро.', 'images/sea_view_room.jpg'),
(13, 'Апартаменты', '9200.00', 4, 'Просторные апартаменты с кухней и гостиной зоной. Для длительного проживания.', 'images/apartment.jpg'),
(14, 'Номер для людей с ограниченными возможностями', '3000.00', 2, 'Специально оборудованный номер для комфортного проживания гостей с ограниченными возможностями.', 'images/accessible_room.jpg'),
(15, 'Детский номер', '4000.00', 3, 'Яркий номер с детской тематикой и игрушками. Безопасность и комфорт для детей.', 'images/kids_room.jpg'),
(16, 'Номер в японском стиле', '5200.00', 2, 'Номер, оформленный в традиционном японском стиле с татами и низкой мебелью.', 'images/japanese_room.jpg'),
(17, 'Номер с сауной', '8000.00', 2, 'Уникальный номер с собственной мини-сауной. Для ценителей спа-процедур.', 'images/sauna_room.jpg'),
(18, 'Коттедж', '12000.00', 8, 'Отдельный коттедж с тремя спальнями, гостиной и кухней. Для большой компании или семьи.', 'images/cottage.jpg'),
(19, 'Лофт', '6800.00', 3, 'Стильный номер в индустриальном стиле с высокими потолками и большими окнами.', 'images/loft_room.jpg'),
(20, 'Номер в классическом стиле', '4700.00', 2, 'Элегантный номер, оформленный в классическом европейском стиле с антикварной мебелью.', 'images/classic_room.jpg'),
(21, 'Номер с библиотекой', '5300.00', 2, 'Для любителей чтения. Номер с собственной небольшой библиотекой и уютным креслом для чтения.', 'images/library_room.jpg'),
(22, 'Голливудский номер', '7100.00', 2, 'Номер, оформленный в стиле голливудских гримерок с зеркалами и звездной атмосферой.', 'images/hollywood_room.jpg'),
(23, 'Номер в морском стиле', '4400.00', 3, 'Морская тематика, элементы декора из натурального дерева и канатов. Для любителей моря.', 'images/nautical_room.jpg');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `feedback_requests`
--
ALTER TABLE `feedback_requests`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `feedback_requests`
--
ALTER TABLE `feedback_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;