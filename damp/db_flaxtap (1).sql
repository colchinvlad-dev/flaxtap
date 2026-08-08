-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.4:3306
-- Время создания: Июн 16 2026 г., 20:42
-- Версия сервера: 8.4.7
-- Версия PHP: 8.5.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `db_flaxtap`
--

-- --------------------------------------------------------

--
-- Структура таблицы `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int DEFAULT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `description`, `image_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Уходовая косметика', 'skincare', NULL, 'Средства для ухода за кожей лица и тела', NULL, 1, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(2, 'Профессиональные наборы', 'professional-kits', NULL, 'Наборы для профессионалов косметологии', NULL, 2, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(3, 'Все для перманента', 'permanent', NULL, 'Оборудование и материалы для перманентного макияжа', NULL, 3, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(4, 'Ресницы и брови', 'lashes-brows', NULL, 'Материалы для наращивания ресниц и оформления бровей', NULL, 4, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(5, 'Расходные материалы', 'consumables', NULL, 'Одноразовые материалы для косметологических процедур', NULL, 5, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(6, 'Для лица', 'face-care', 1, 'Средства для ухода за кожей лица', NULL, 1, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(7, 'Для тела', 'body-care', 1, 'Средства для ухода за телом', NULL, 2, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(8, 'Кремы', 'creams', 1, 'Увлажняющие и питательные кремы', NULL, 3, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(9, 'Скрабы', 'scrubs', 1, 'Средства для отшелушивания кожи', NULL, 4, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(10, 'Сыворотки', 'serums', 1, 'Концентрированные средства для ухода', NULL, 5, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27'),
(11, 'Маски', 'masks', 1, 'Маски для лица и тела', NULL, 6, 1, '2026-01-06 20:28:27', '2026-01-06 20:28:27');

-- --------------------------------------------------------

--
-- Структура таблицы `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL COMMENT 'Тема обращения',
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `status` enum('new','read','answered') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `phone`, `email`, `subject`, `message`, `ip_address`, `user_agent`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Лукин Иван', '+72133242423', 'ivanov1121223@example.co1m', 'product', 'вцсвццувауцвас', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 YaBrowser/25.12.0.0 Safari/537.36', 'new', '2026-02-01 12:07:13', '2026-02-01 12:07:13'),
(2, 'Колчин Владислав Сергеевич', '+79087193593', 'colchin.vl4d@yandex.ru', 'product', 'цупфуцпцкп', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 YaBrowser/25.12.0.0 Safari/537.36', 'new', '2026-02-01 12:15:22', '2026-02-01 12:15:22'),
(3, 'Лукин Иван ииргрнргн', '+72133242423', 'ivanov1121223@example.co1m', 'other', 'свыфвваамкв', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 YaBrowser/25.12.0.0 Safari/537.36', 'new', '2026-02-02 08:32:48', '2026-02-02 08:32:48'),
(4, 'TEST TESTOVIC', '79123456712', 'test@gmail.com', 'cooperation', 'TEST SQL', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'new', '2026-02-08 12:14:31', '2026-02-08 12:14:31'),
(5, 'Тестовый запрос 12.03.2025', '+79087193593', 'colchin.vl4d@yandex.ru', 'product', 'Тестовый запрос 12.03.2025', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36', 'new', '2026-03-12 13:09:21', '2026-03-12 13:09:21'),
(6, 'Тестовый запрос 12.03.2025 6.5.0', '+79087193593', 'colchin.vl4d@yandex.ru', 'product', 'Тестовый запрос 12.03.2025 6.5.0', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36', 'new', '2026-03-12 13:39:53', '2026-03-12 13:39:53');

-- --------------------------------------------------------

--
-- Структура таблицы `coupons`
--

CREATE TABLE `coupons` (
  `id` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percent','fixed','free_shipping') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT '0.00' COMMENT 'Минимальная сумма заказа для применения',
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'Максимальная сумма скидки (для процентной скидки)',
  `usage_limit` int DEFAULT NULL COMMENT 'Максимальное количество использований',
  `used_count` int DEFAULT '0',
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `delivery_methods`
--

CREATE TABLE `delivery_methods` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `free_threshold` decimal(10,2) DEFAULT NULL COMMENT 'Сумма заказа для бесплатной доставки',
  `delivery_days` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `delivery_methods`
--

INSERT INTO `delivery_methods` (`id`, `name`, `description`, `price`, `free_threshold`, `delivery_days`, `is_active`, `sort_order`) VALUES
(1, 'Курьерская доставка', 'Доставка курьером по адресу', 300.00, 5000.00, '1-3 дня', 1, 1),
(2, 'Самовывоз', 'Самовывоз из магазина', 0.00, NULL, 'В день заказа', 1, 2),
(3, 'Почта России', 'Доставка почтой России', 250.00, 7000.00, '5-14 дней', 1, 3),
(4, 'СДЭК', 'Доставка транспортной компанией СДЭК', 350.00, 8000.00, '2-5 дней', 1, 4),
(5, 'Boxberry', 'Доставка в пункт выдачи Boxberry', 200.00, 6000.00, '3-7 дней', 1, 5);

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE `news` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL COMMENT 'Заголовок новости',
  `slug` varchar(200) NOT NULL COMMENT 'URL новости',
  `excerpt` text COMMENT 'Краткое описание',
  `content` longtext COMMENT 'Полный текст новости',
  `category_id` int DEFAULT NULL COMMENT 'Категория новости',
  `author_id` int DEFAULT NULL COMMENT 'Автор новости',
  `main_image_url` varchar(500) DEFAULT NULL COMMENT 'Главное изображение',
  `thumbnail_url` varchar(500) DEFAULT NULL COMMENT 'Миниатюра для списка',
  `status` enum('draft','published','archived') DEFAULT 'draft' COMMENT 'Статус новости',
  `published_at` datetime DEFAULT NULL COMMENT 'Дата публикации',
  `reading_time_minutes` int DEFAULT '5' COMMENT 'Время чтения в минутах',
  `views_count` int DEFAULT '0' COMMENT 'Количество просмотров',
  `shares_count` int DEFAULT '0' COMMENT 'Количество репостов',
  `comments_count` int DEFAULT '0' COMMENT 'Количество комментариев',
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `is_featured` tinyint(1) DEFAULT '0' COMMENT 'Показана на главной',
  `is_pinned` tinyint(1) DEFAULT '0' COMMENT 'Закрепленная новость',
  `allow_comments` tinyint(1) DEFAULT '1' COMMENT 'Разрешены комментарии',
  `created_by` int DEFAULT NULL COMMENT 'Кто создал новость',
  `updated_by` int DEFAULT NULL COMMENT 'Кто обновил новость',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `category_id`, `author_id`, `main_image_url`, `thumbnail_url`, `status`, `published_at`, `reading_time_minutes`, `views_count`, `shares_count`, `comments_count`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_pinned`, `allow_comments`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Запуск новой линии сывороток FlaxSerum Pro с инновационной нано-технологией', 'zapusk-novoy-linii-syvorotok-flaxserum-pro', 'Представляем революционную серию сывороток с улучшенной формулой и нано-технологиями доставки активных компонентов...', '<p><br></p>', 1, 1, 'https://flaxtap.ru/media/banners/global/bcfa01300c0249c7ad73dbb80b1f33dc.jpg', 'https://flaxtap.ru/media/banners/global/bcfa01300c0249c7ad73dbb80b1f33dc.jpg', 'published', '2025-03-15 10:00:00', 5, 2845, 0, 0, 'Запуск новой линии сывороток FlaxSerum Pro - Новости FlaxTap', 'FlaxTap запускает новую линию сывороток с инновационной нано-технологией доставки активных компонентов', 'Представляем революционную серию сывороток с улучшенной формулой и нано-технологиями доставки активных компонентов...', 1, 0, 1, NULL, 1, '2025-03-14 12:30:00', '2026-02-13 09:40:26'),
(2, 'Открытие нового учебного центра в Москве', 'otkrytie-novogo-uchebnogo-tsentra-v-moskve', 'FlaxTap открывает современный учебный центр для косметологов с новейшим оборудованием и авторскими программами обучения...', '<p>FlaxTap открывает современный учебный центр для косметологов с новейшим оборудованием и авторскими программами обучения...</p>', 2, 2, 'https://flaxtap.ru/media/banners/093f3d253dee4ed195a31bb025cfb3a2.webp', 'https://flaxtap.ru/media/banners/093f3d253dee4ed195a31bb025cfb3a2.webp', 'published', '2025-03-10 09:00:00', 5, 1567, 0, 0, 'FlaxTap открывает современный учебный центр для косметологов с новейшим оборудованием и авторскими программами обучения...', 'FlaxTap открывает современный учебный центр для косметологов с новейшим оборудованием и авторскими программами обучения...', 'FlaxTap открывает современный учебный центр для косметологов с новейшим оборудованием и авторскими программами обучения...', 0, 0, 1, NULL, 1, '2026-01-06 20:50:46', '2026-02-13 09:37:51'),
(3, 'Результаты клинических исследований сыворотки с ретинолом', 'rezultaty-klinicheskih-issledovaniy-syvorotki-s-retinolom', 'Публикуем результаты 6-месячных клинических исследований нашей сыворотки с инкапсулированным ретинолом...', '<p>Публикуем результаты 6-месячных клинических исследований нашей сыворотки с инкапсулированным ретинолом...</p>', 3, 3, 'https://flaxtap.ru/media/banners/42b4a584f84f4d0b91554686ee9cff82.jpg', 'https://flaxtap.ru/media/banners/42b4a584f84f4d0b91554686ee9cff82.jpg', 'published', '2025-03-05 11:30:00', 5, 2346, 0, 0, 'Публикуем результаты 6-месячных клинических исследований нашей сыворотки с инкапсулированным ретинолом...', 'Публикуем результаты 6-месячных клинических исследований нашей сыворотки с инкапсулированным ретинолом...', 'Публикуем результаты 6-месячных клинических исследований нашей сыворотки с инкапсулированным ретинолом...', 0, 0, 1, NULL, 1, '2026-01-06 20:50:46', '2026-02-13 09:38:23'),
(4, 'Весенний уход за кожей: советы главного косметолога', 'vesenniy-uhod-za-kozhey-sovety-glavnogo-kosmetologa', 'Как правильно подготовить кожу к смене сезона? Главный косметолог FlaxTap делится секретами эффективного весеннего ухода...', '<p>Как правильно подготовить кожу к смене сезона? Главный косметолог FlaxTap делится секретами эффективного весеннего ухода...</p>', 4, 4, 'https://flaxtap.ru/media/banners/global/bcfa01300c0249c7ad73dbb80b1f33dc.jpg', 'https://flaxtap.ru/media/banners/global/bcfa01300c0249c7ad73dbb80b1f33dc.jpg', 'published', '2025-02-28 14:15:00', 5, 3125, 0, 0, 'Как правильно подготовить кожу к смене сезона? Главный косметолог FlaxTap делится секретами эффективного весеннего ухода...', 'Как правильно подготовить кожу к смене сезона? Главный косметолог FlaxTap делится секретами эффективного весеннего ухода...', 'Как правильно подготовить кожу к смене сезона? Главный косметолог FlaxTap делится секретами эффективного весеннего ухода...', 0, 0, 1, NULL, 1, '2026-01-06 20:50:46', '2026-02-13 09:38:54'),
(5, 'Участие в международной выставке CosmoProf 2025', 'uchastie-v-mezhdunarodnoy-vystavke-cosmoprof-2025', 'FlaxTap представит новые разработки на крупнейшей международной выставке профессиональной косметики в Болонье...', '<p>FlaxTap представит новые разработки на крупнейшей международной выставке профессиональной косметики в Болонье...</p>', 6, 1, 'https://flaxtap.ru/media/banners/fc8d262ff03b4c97aeb7a384e6d7373e.avif', 'https://flaxtap.ru/media/banners/fc8d262ff03b4c97aeb7a384e6d7373e.avif', 'published', '2025-02-20 16:45:00', 5, 1892, 0, 0, 'FlaxTap представит новые разработки на крупнейшей международной выставке профессиональной косметики в Болонье...', 'FlaxTap представит новые разработки на крупнейшей международной выставке профессиональной косметики в Болонье...', 'FlaxTap представит новые разработки на крупнейшей международной выставке профессиональной косметики в Болонье...', 0, 0, 1, NULL, 1, '2026-01-06 20:50:46', '2026-03-12 13:40:18'),
(6, 'Переход на экологичную упаковку: наши обязательства', 'perehod-na-ekologichnuyu-upakovku-nashi-obyazatelstva', 'FlaxTap объявляет о полном переходе на перерабатываемую упаковку к концу 2025 года...', '<p>FlaxTap объявляет о полном переходе на перерабатываемую упаковку к концу 2025 года...</p>', 7, 2, 'https://flaxtap.ru/media/banners/093f3d253dee4ed195a31bb025cfb3a2.webp', 'https://flaxtap.ru/media/banners/093f3d253dee4ed195a31bb025cfb3a2.webp', 'published', '2025-02-15 13:20:00', 5, 2765, 0, 0, 'FlaxTap объявляет о полном переходе на перерабатываемую упаковку к концу 2025 года...', 'FlaxTap объявляет о полном переходе на перерабатываемую упаковку к концу 2025 года...', 'FlaxTap объявляет о полном переходе на перерабатываемую упаковку к концу 2025 года...', 0, 0, 1, NULL, 1, '2026-01-06 20:50:46', '2026-02-13 09:39:56'),
(12, 'Редизайн сайта Магазин косметики FlaxTap', 'редизайн-сайта-магазин-косметики-flaxtap', 'Редизайн сайта Магазин косметики FlaxTap', '<p><br></p>', 2, 1, 'https://flaxtap.ru/media/banners/42b4a584f84f4d0b91554686ee9cff82.jpg', 'https://flaxtap.ru/media/banners/42b4a584f84f4d0b91554686ee9cff82.jpg', 'published', '2026-02-13 12:42:09', 5, 1, 0, 0, 'Редизайн сайта Магазин косметики FlaxTap1', 'Редизайн сайта Магазин косметики FlaxTap', 'Редизайн сайта Магазин косметики FlaxTap', 1, 0, 1, 1, 1, '2026-02-13 09:42:09', '2026-06-04 16:34:53'),
(13, 'Запуск новой линии сывороток FlaxSerum', 'запуск-новой-линии-сывороток-flaxserum', 'Запуск новой линии сывороток FlaxSerum', '<p>Запуск новой линии сывороток FlaxSerum</p>', 4, 2, 'https://flaxtap.ru/media/banners/fc8d262ff03b4c97aeb7a384e6d7373e.avif', 'https://flaxtap.ru/media/banners/fc8d262ff03b4c97aeb7a384e6d7373e.avif', 'published', '2026-02-13 12:43:31', 5, 0, 0, 0, 'Запуск новой линии сывороток FlaxSerum', 'Запуск новой линии сывороток FlaxSerum', 'Запуск новой линии сывороток FlaxSerum', 0, 0, 1, 1, 1, '2026-02-13 09:43:22', '2026-02-13 09:43:31');

-- --------------------------------------------------------

--
-- Структура таблицы `news_attachments`
--

CREATE TABLE `news_attachments` (
  `id` int NOT NULL,
  `news_id` int NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL COMMENT 'Оригинальное имя файла',
  `file_name` varchar(255) DEFAULT NULL COMMENT 'Имя файла для загрузки',
  `file_size` int DEFAULT NULL COMMENT 'Размер файла в байтах',
  `file_type` varchar(50) DEFAULT NULL COMMENT 'Тип файла',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME тип',
  `icon_class` varchar(50) DEFAULT 'fas fa-file' COMMENT 'Класс иконки',
  `download_count` int DEFAULT '0' COMMENT 'Количество скачиваний',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_attachments`
--

INSERT INTO `news_attachments` (`id`, `news_id`, `file_url`, `original_name`, `file_name`, `file_size`, `file_type`, `mime_type`, `icon_class`, `download_count`, `sort_order`, `created_at`) VALUES
(1, 1, '/assets/downloads/clinical-research-report.pdf', 'Полный отчет клинических исследований.pdf', 'clinical-research-report.pdf', 2516582, 'pdf', NULL, 'fas fa-file-pdf', 0, 0, '2026-01-06 20:50:46'),
(2, 1, '/assets/downloads/serum-usage-guide.pdf', 'Инструкция по применению сывороток.pdf', 'serum-usage-guide.pdf', 1153434, 'pdf', NULL, 'fas fa-file-alt', 0, 0, '2026-01-06 20:50:46');

-- --------------------------------------------------------

--
-- Структура таблицы `news_authors`
--

CREATE TABLE `news_authors` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Имя автора',
  `slug` varchar(100) NOT NULL COMMENT 'URL автора',
  `position` varchar(100) DEFAULT NULL COMMENT 'Должность',
  `bio` text COMMENT 'Биография',
  `avatar_url` varchar(500) DEFAULT NULL COMMENT 'URL аватарки',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email автора',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Телефон',
  `social_links` json DEFAULT NULL COMMENT 'Ссылки на соцсети',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Активен ли автор',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_authors`
--

INSERT INTO `news_authors` (`id`, `name`, `slug`, `position`, `bio`, `avatar_url`, `email`, `phone`, `social_links`, `is_active`, `created_at`) VALUES
(1, 'Мария Беляева', 'maria-belyaeva', 'Генеральный директор', 'Основатель и генеральный директор FlaxTap с 2015 года. Эксперт в области косметологии с 15-летним опытом.', '/assets/media/personal/author1.jpg', 'maria@flaxtap.ru', NULL, NULL, 1, '2026-01-06 20:50:46'),
(2, 'Анна Смирнова', 'anna-smirnova', 'Главный редактор', 'Главный редактор новостного портала FlaxTap, автор более 200 статей о косметологии и уходе за кожей.', '/assets/media/personal/author2.jpg', 'anna@flaxtap.ru', NULL, NULL, 1, '2026-01-06 20:50:46'),
(3, 'Дмитрий Волков', 'dmitry-volkov', 'Научный руководитель', 'Доктор биологических наук, руководитель научного отдела FlaxTap. Специалист по разработке косметических формул.', '/assets/media/personal/author3.jpg', 'dmitry@flaxtap.ru', NULL, NULL, 1, '2026-01-06 20:50:46'),
(4, 'Елена Петрова', 'elena-petrova', 'Главный косметолог', 'Ведущий косметолог с 12-летним опытом работы. Специалист по anti-age терапии и уходу за проблемной кожей.', '/assets/media/personal/author4.jpg', 'elena@flaxtap.ru', NULL, NULL, 1, '2026-01-06 20:50:46');

-- --------------------------------------------------------

--
-- Структура таблицы `news_categories`
--

CREATE TABLE `news_categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Название категории',
  `slug` varchar(100) NOT NULL COMMENT 'URL категории',
  `description` text COMMENT 'Описание категории',
  `color` varchar(20) DEFAULT '#3498db' COMMENT 'Цвет категории для отображения',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Иконка категории',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Активна ли категория',
  `sort_order` int DEFAULT '0' COMMENT 'Порядок сортировки',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_categories`
--

INSERT INTO `news_categories` (`id`, `name`, `slug`, `description`, `color`, `icon`, `is_active`, `sort_order`, `created_at`) VALUES
(1, 'Новинки', 'novelties', 'Новые продукты и коллекции', '#3498db', 'fas fa-star', 1, 1, '2026-01-06 20:50:46'),
(2, 'Обучение', 'training', 'Обучающие материалы и курсы', '#2ecc71', 'fas fa-graduation-cap', 1, 2, '2026-01-06 20:50:46'),
(3, 'Исследования', 'research', 'Научные исследования и клинические испытания', '#9b59b6', 'fas fa-flask', 1, 3, '2026-01-06 20:50:46'),
(4, 'Советы экспертов', 'expert-advice', 'Рекомендации от профессионалов', '#e74c3c', 'fas fa-user-tie', 1, 4, '2026-01-06 20:50:46'),
(5, 'Акции', 'promotions', 'Скидки и специальные предложения', '#f39c12', 'fas fa-percentage', 1, 5, '2026-01-06 20:50:46'),
(6, 'События', 'events', 'Выставки, конференции, мероприятия', '#1abc9c', 'fas fa-calendar-alt', 1, 6, '2026-01-06 20:50:46'),
(7, 'Экология', 'ecology', 'Экологические инициативы и устойчивое развитие', '#27ae60', 'fas fa-leaf', 1, 7, '2026-01-06 20:50:46');

-- --------------------------------------------------------

--
-- Структура таблицы `news_comments`
--

CREATE TABLE `news_comments` (
  `id` int NOT NULL,
  `news_id` int NOT NULL,
  `user_id` int DEFAULT NULL COMMENT 'ID пользователя (если зарегистрирован)',
  `parent_id` int DEFAULT NULL COMMENT 'Родительский комментарий',
  `author_name` varchar(100) DEFAULT NULL COMMENT 'Имя автора (если не зарегистрирован)',
  `author_email` varchar(100) DEFAULT NULL COMMENT 'Email автора',
  `author_avatar_url` varchar(500) DEFAULT NULL COMMENT 'URL аватарки',
  `content` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT '0' COMMENT 'Одобрен ли комментарий',
  `likes_count` int DEFAULT '0',
  `dislikes_count` int DEFAULT '0',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `edited_at` datetime DEFAULT NULL COMMENT 'Дата последнего редактирования',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `news_gallery`
--

CREATE TABLE `news_gallery` (
  `id` int NOT NULL,
  `news_id` int NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `caption` text COMMENT 'Подпись к изображению',
  `sort_order` int DEFAULT '0' COMMENT 'Порядок сортировки',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_gallery`
--

INSERT INTO `news_gallery` (`id`, `news_id`, `image_url`, `alt_text`, `caption`, `sort_order`, `created_at`) VALUES
(1, 1, '/assets/media/news/gallery1.jpg', 'Процесс разработки', 'Наша лаборатория: процесс разработки новой формулы', 1, '2026-01-06 20:50:46'),
(2, 1, '/assets/media/news/gallery2.jpg', 'Лабораторные исследования', 'Клинические испытания в лабораторных условиях', 2, '2026-01-06 20:50:46'),
(3, 1, '/assets/media/news/gallery3.jpg', 'Тестирование продукции', 'Тестирование сывороток на добровольцах', 3, '2026-01-06 20:50:46');

-- --------------------------------------------------------

--
-- Структура таблицы `news_news_tags`
--

CREATE TABLE `news_news_tags` (
  `news_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_news_tags`
--

INSERT INTO `news_news_tags` (`news_id`, `tag_id`, `created_at`) VALUES
(1, 3, '2026-02-13 09:40:26'),
(1, 4, '2026-02-13 09:40:26'),
(1, 6, '2026-02-13 09:40:26'),
(1, 8, '2026-02-13 09:40:26');

-- --------------------------------------------------------

--
-- Структура таблицы `news_related`
--

CREATE TABLE `news_related` (
  `news_id` int NOT NULL,
  `related_news_id` int NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_related`
--

INSERT INTO `news_related` (`news_id`, `related_news_id`, `sort_order`, `created_at`) VALUES
(1, 2, 0, '2026-02-13 09:40:26'),
(1, 3, 0, '2026-02-13 09:40:26'),
(1, 4, 0, '2026-02-13 09:40:26'),
(2, 1, 0, '2026-02-13 09:37:51'),
(2, 3, 0, '2026-02-13 09:37:51'),
(3, 1, 0, '2026-02-13 09:38:24'),
(3, 5, 0, '2026-02-13 09:38:24'),
(6, 5, 0, '2026-02-13 09:39:57'),
(12, 1, 0, '2026-02-13 09:42:14'),
(12, 2, 0, '2026-02-13 09:42:14'),
(12, 5, 0, '2026-02-13 09:42:14'),
(13, 5, 0, '2026-02-13 09:43:31'),
(13, 6, 0, '2026-02-13 09:43:31');

-- --------------------------------------------------------

--
-- Структура таблицы `news_subscribers`
--

CREATE TABLE `news_subscribers` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL COMMENT 'Имя подписчика',
  `is_active` tinyint(1) DEFAULT '1',
  `subscription_date` date NOT NULL,
  `unsubscribe_date` date DEFAULT NULL,
  `unsubscribe_reason` text,
  `source` varchar(50) DEFAULT 'website' COMMENT 'Источник подписки',
  `ip_address` varchar(45) DEFAULT NULL,
  `confirmation_token` varchar(100) DEFAULT NULL COMMENT 'Токен подтверждения',
  `confirmed_at` datetime DEFAULT NULL COMMENT 'Дата подтверждения',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `news_tags`
--

CREATE TABLE `news_tags` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Название тега',
  `slug` varchar(50) NOT NULL COMMENT 'URL тега',
  `color` varchar(20) DEFAULT '#3498db' COMMENT 'Цвет тега',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `news_tags`
--

INSERT INTO `news_tags` (`id`, `name`, `slug`, `color`, `is_active`, `created_at`) VALUES
(1, 'Косметология', 'cosmetology', '#3498db', 1, '2026-01-06 20:50:46'),
(2, 'Уход за кожей', 'skincare', '#2ecc71', 1, '2026-01-06 20:50:46'),
(3, 'Новинки продукции', 'new-products', '#e74c3c', 1, '2026-01-06 20:50:46'),
(4, 'Клинические исследования', 'clinical-research', '#9b59b6', 1, '2026-01-06 20:50:46'),
(5, 'Обучение', 'education', '#f39c12', 1, '2026-01-06 20:50:46'),
(6, 'Технологии', 'technology', '#1abc9c', 1, '2026-01-06 20:50:46'),
(7, 'Экология', 'ecology', '#27ae60', 1, '2026-01-06 20:50:46'),
(8, 'Сыворотки', 'serums', '#8e44ad', 1, '2026-01-06 20:50:46'),
(9, 'Ретинол', 'retinol', '#d35400', 1, '2026-01-06 20:50:46'),
(10, 'Витамин C', 'vitamin-c', '#c0392b', 1, '2026-01-06 20:50:46');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `order_number` varchar(20) NOT NULL COMMENT 'Уникальный номер заказа, например ORD-2024-00123',
  `user_id` int DEFAULT NULL,
  `status_id` int DEFAULT '1' COMMENT 'Ссылка на order_statuses.id',
  `payment_method_id` int DEFAULT NULL,
  `delivery_method_id` int DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `delivery_address` text,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_postcode` varchar(20) DEFAULT NULL,
  `delivery_notes` text COMMENT 'Комментарии по доставке',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Стоимость товаров без учета доставки и скидок',
  `discount_amount` decimal(10,2) DEFAULT '0.00' COMMENT 'Сумма скидки',
  `delivery_price` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Итоговая сумма к оплате',
  `is_paid` tinyint(1) DEFAULT '0',
  `paid_amount` decimal(10,2) DEFAULT '0.00',
  `payment_date` datetime DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL COMMENT 'ID платежа в платежной системе',
  `tracking_number` varchar(100) DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` date DEFAULT NULL,
  `customer_notes` text COMMENT 'Комментарий от клиента',
  `manager_notes` text COMMENT 'Внутренние заметки менеджера',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `source` varchar(50) DEFAULT 'website' COMMENT 'Источник заказа: website, mobile_app, etc.',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `status_id`, `payment_method_id`, `delivery_method_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_address`, `delivery_city`, `delivery_postcode`, `delivery_notes`, `subtotal`, `discount_amount`, `delivery_price`, `total_amount`, `is_paid`, `paid_amount`, `payment_date`, `payment_id`, `tracking_number`, `estimated_delivery`, `actual_delivery`, `customer_notes`, `manager_notes`, `ip_address`, `user_agent`, `source`, `created_at`, `updated_at`) VALUES
(12, 'ORD-2026-00001', 18, 1, 3, 1, 'Миронов Егор Миронович', 'user3@ex.ru', '+79123456781', 'ул.Косметологов д.45', 'Санкт-Петербург', '213456', '-', 4060.00, 0.00, 300.00, 4360.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, 'при получении', NULL, NULL, NULL, 'website', '2026-02-13 09:26:57', '2026-02-13 09:26:57'),
(13, 'ORD-2026-00002', 14, 3, 2, 2, 'Зубкова Юлия Викторовна', 'user@ex.ru', '+79123456781', 'ул.Косметологов д.45', 'Санкт-Петербург', '12432', '-', 53890.00, 0.00, 0.00, 53890.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '-', NULL, NULL, NULL, 'website', '2026-02-13 09:31:28', '2026-02-13 09:31:28'),
(14, 'ORD-2026-00003', 20, 4, 1, 2, 'Лапин Тимофей Павлович', 'use1213r@ex.ru', '+79123456781', 'ул.Косметологов', 'Санкт-Петербург', '32112', '-', 56590.00, 0.00, 0.00, 56590.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '-', NULL, NULL, NULL, 'website', '2026-02-13 09:33:56', '2026-02-13 09:33:56'),
(15, 'ORD-2026-00004', 17, 6, 1, 2, 'Кузнецова Айлин Николаевна', 'user12@ex.ru', '+79123456781', 'ул.Косметологов', 'Санкт-Петербург', '213123', '-', 25290.00, 0.00, 0.00, 25290.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '-', NULL, NULL, NULL, 'website', '2026-02-13 09:35:24', '2026-02-13 09:35:24'),
(16, 'ORD-2026-00005', 19, 1, 2, 1, 'Лаврентьева Эмилия Владимировна', 'user123@ex.ru', '+79123456781', 'ул.Косметологов', 'Санкт-Петербург', '334232', '-', 23290.00, 0.00, 300.00, 23590.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '-', NULL, NULL, NULL, 'website', '2026-02-13 09:36:21', '2026-02-13 09:36:21'),
(17, 'ORD-2026-00006', 19, 1, 1, 1, 'Лаврентьева Эмилия Владимировна', 'user123@ex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 3070.00, 0.00, 300.00, 3370.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, 'нет', NULL, NULL, NULL, 'website', '2026-03-12 11:41:32', '2026-03-12 11:41:32'),
(18, 'ORD-2026-00007', NULL, 1, 1, 1, 'Лаврентьева Эмилия Владимировна', 'user123@ex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 790.00, 0.00, 300.00, 1090.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, 'нет', NULL, NULL, NULL, 'website', '2026-03-12 11:42:09', '2026-03-12 11:42:09'),
(19, 'ORD-2026-00008', 15, 5, 1, 1, 'Борисов Юрий Николаевич', 'use1r@ex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 16690.00, 0.00, 300.00, 16990.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'website', '2026-03-12 11:42:54', '2026-03-12 11:42:54'),
(20, 'ORD-2026-00009', 19, 1, 1, 1, 'Лаврентьева Эмилия Владимировна', 'user123@ex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 1000.00, 0.00, 300.00, 1300.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'website', '2026-03-12 11:45:34', '2026-03-12 11:45:34'),
(21, 'ORD-2026-00010', 19, 1, 1, 2, 'Лаврентьева Эмилия Владимировна', 'user123@ex.ru', '+79087193593', 'Санкт-Петербург, В.О., 6-я линия, 25\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 1090.00, 0.00, 0.00, 1090.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'website', '2026-03-12 11:48:10', '2026-03-12 11:48:10'),
(22, 'ORD-2026-00011', 17, 6, 2, 3, 'Кузнецова Айлин Николаевна', 'user12@ex.ru', '+79087193593', 'Санкт-Петербург, ул. Косметологов, 15\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 16690.00, 0.00, 250.00, 16940.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '43', NULL, NULL, NULL, 'website', '2026-03-12 11:53:47', '2026-03-12 11:53:47'),
(23, 'ORD-2026-00012', 13, 2, 1, 1, 'Колчин Владислав Сергеевич', 'colchin.vl4d@yandex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', '-', 16690.00, 0.00, 300.00, 16990.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'website', '2026-03-12 11:55:57', '2026-03-12 11:55:57'),
(25, 'ORD-2026-00013', 13, 1, 1, 1, 'Колчин Владислав Сергеевич', 'colchin.vl4d@yandex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 2680.00, 0.00, 300.00, 2980.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, 'по карте', NULL, NULL, NULL, 'website', '2026-04-04 11:26:53', '2026-04-04 11:26:53'),
(26, 'ORD-2026-00014', 13, 4, 2, 1, 'Колчин Владислав Сергеевич', 'colchin.vl4d@yandex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 1990.00, 0.00, 300.00, 2290.00, 1, 0.00, '2026-04-18 12:45:00', NULL, '', NULL, NULL, '-', '', NULL, NULL, 'website', '2026-04-16 14:03:55', '2026-04-18 09:45:39'),
(27, 'ORD-2026-00015', 17, 1, 2, 2, 'Кузнецова Айлин Николаевна', 'user12@ex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 42580.00, 0.00, 0.00, 42580.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '-', NULL, NULL, NULL, 'website', '2026-04-18 10:53:13', '2026-04-18 10:53:13'),
(28, 'ORD-2026-00016', 18, 1, 4, 2, 'Миронов Егор Миронович', 'user3@ex.ru', '+78121234567', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 4480.00, 0.00, 0.00, 4480.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, 'нет', NULL, NULL, NULL, 'website', '2026-04-18 14:21:50', '2026-04-18 14:21:50'),
(29, 'ORD-2026-00017', 19, 3, 6, 3, 'Лаврентьева Эмилия Владимировна', 'user123@ex.ru', '+78121234567', 'Санкт-Петербург, Лиговский пр., 50\r\nСанкт-Петербург', 'Санкт-Петербург', '223456', 'нет', 22900.00, 0.00, 250.00, 23150.00, 1, 0.00, '2026-04-25 13:32:00', NULL, '', NULL, NULL, '', 'оплачено', NULL, NULL, 'website', '2026-04-25 10:32:24', '2026-04-25 10:32:40'),
(30, 'ORD-2026-00018', 15, 4, 3, 1, 'Борисов Юрий Николаевич', 'use1r@ex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50, Санкт-Петербург\r\nСанкт-Петербург 223456', 'Санкт-Петербург', '223456', 'нет', 390.00, 0.00, 300.00, 690.00, 1, 0.00, '2026-05-16 14:31:00', NULL, '', NULL, NULL, 'нет', '', NULL, NULL, 'website', '2026-05-16 11:31:02', '2026-05-16 11:31:37'),
(31, 'ORD-2026-00019', 13, 1, 2, 1, 'Колчин Владислав Сергеевич', 'colchin.vl4d@yandex.ru', '+79087193593', 'Санкт-Петербург, Лиговский пр., 50, Санкт-Петербург\r\nСанкт-Петербург 223456', 'Санкт-Петербург', '223456', 'нет', 19380.00, 0.00, 300.00, 19680.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'website', '2026-05-27 18:31:52', '2026-05-27 18:31:52'),
(32, 'ORD-2026-00020', 13, 1, 1, 2, 'Колчин Владислав Сергеевич', 'colchin.vl4d@yandex.ru', '+79123456712', '443', 'печора', '43', '43', 35180.00, 0.00, 0.00, 35180.00, 0, 0.00, NULL, NULL, NULL, NULL, NULL, '43', NULL, NULL, NULL, 'website', '2026-06-15 10:08:42', '2026-06-15 10:08:42'),
(33, 'ORD-2026-00021', 17, 3, 1, 1, 'Кузнецова Айлин Николаевна', 'user12@ex.ru', '+79081193593', 'Санкт-Петербург, Лиговский пр., 50, Санкт-Петербург\r\nСанкт-Петербург 223456', 'Санкт-Петербург', '223456', 'нет', 21860.00, 0.00, 300.00, 22160.00, 1, 0.00, '2026-06-16 20:41:00', NULL, '', NULL, NULL, 'нет', '', NULL, NULL, 'website', '2026-06-16 17:41:03', '2026-06-16 17:41:49');

--
-- Триггеры `orders`
--
DELIMITER $$
CREATE TRIGGER `before_order_insert` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
    DECLARE year_val VARCHAR(4);
    DECLARE seq_num INT;
    
    SET year_val = YEAR(NOW());
    
    -- Получаем следующий порядковый номер для текущего года
    SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, 10) AS UNSIGNED)), 0) + 1
    INTO seq_num
    FROM orders
    WHERE order_number LIKE CONCAT('ORD-', year_val, '-%');
    
    -- Формируем номер заказа: ORD-2024-00123
    SET NEW.order_number = CONCAT('ORD-', year_val, '-', LPAD(seq_num, 5, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `order_coupons`
--

CREATE TABLE `order_coupons` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `coupon_id` int NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(200) NOT NULL COMMENT 'Название товара на момент заказа',
  `product_price` decimal(10,2) NOT NULL COMMENT 'Цена товара на момент заказа',
  `quantity` int NOT NULL DEFAULT '1',
  `discount_percent` decimal(5,2) DEFAULT '0.00' COMMENT 'Скидка на этот товар в %',
  `discount_amount` decimal(10,2) DEFAULT '0.00' COMMENT 'Сумма скидки на этот товар',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Итоговая стоимость (цена * количество - скидка)',
  `product_image` varchar(500) DEFAULT NULL,
  `product_weight` varchar(50) DEFAULT NULL,
  `product_sku` varchar(100) DEFAULT NULL COMMENT 'Артикул товара',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `discount_percent`, `discount_amount`, `subtotal`, `product_image`, `product_weight`, `product_sku`, `created_at`) VALUES
(15, 12, 14, 'Бальзам для губ FlaxLips', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/бальзам_для_губ.jpg', NULL, NULL, '2026-02-13 09:26:57'),
(16, 12, 25, 'Мицеллярная вода FlaxWater', 2190.00, 1, 0.00, 0.00, 2190.00, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0076_1_MVupsnK.jpg', NULL, NULL, '2026-02-13 09:26:57'),
(17, 12, 23, 'Крем для рук FlaxHandCream', 1090.00, 1, 0.00, 0.00, 1090.00, 'https://flaxtap.ru/media/products/images/крем_для_рук_Flaxtap_f7TABt2.jpg', NULL, NULL, '2026-02-13 09:26:57'),
(18, 12, 12, 'Черный пакет FlaxTap', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/пакет_черный.jpeg', NULL, NULL, '2026-02-13 09:26:57'),
(19, 13, 23, 'Крем для рук FlaxHandCream', 1090.00, 1, 0.00, 0.00, 1090.00, 'https://flaxtap.ru/media/products/images/крем_для_рук_Flaxtap_f7TABt2.jpg', NULL, NULL, '2026-02-13 09:31:28'),
(20, 13, 13, 'Маска Черника FlaxFaceMask', 1490.00, 3, 0.00, 0.00, 4470.00, 'https://flaxtap.ru/media/products/images/чернич_маск.JPG', NULL, NULL, '2026-02-13 09:31:28'),
(21, 13, 3, 'Пенка для кожи склонной к высыпаниям FlaxFoam', 2290.00, 1, 0.00, 0.00, 2290.00, 'https://flaxtap.ru/media/products/images/%D0%BF%D0%B5%D0%BD%D0%BA%D0%B0_%D0%BA%D0%BE%D0%B6%D0%B0_%D1%81%D0%BA%D0%BB%D0%BE%D0%BD%D0%BD%D0%B0_%D0%BA__%D0%B2%D1%8B%D1%81%D1%8B%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC.jpg', NULL, NULL, '2026-02-13 09:31:28'),
(22, 13, 28, 'Обучение FlaxLift', 17000.00, 1, 0.00, 0.00, 17000.00, 'https://flaxtap.ru/media/products/images/кругляш_лифт.jpeg', NULL, NULL, '2026-02-13 09:31:28'),
(23, 13, 27, 'Обучение + Двойной набор FlaxLift', 22900.00, 1, 0.00, 0.00, 22900.00, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, NULL, '2026-02-13 09:31:28'),
(24, 13, 15, 'Очищающий гель с экстрактами грибов FlaxGel', 2390.00, 1, 0.00, 0.00, 2390.00, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0095.jpg', NULL, NULL, '2026-02-13 09:31:28'),
(25, 13, 11, 'Оранжевый пакет FlaxTap', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/пакет_оранжевый.jpeg', NULL, NULL, '2026-02-13 09:31:28'),
(26, 13, 17, 'Универсальное средство FlaxSpa', 2190.00, 1, 0.00, 0.00, 2190.00, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0100.jpg', NULL, NULL, '2026-02-13 09:31:28'),
(27, 13, 12, 'Черный пакет FlaxTap', 390.00, 2, 0.00, 0.00, 780.00, 'https://flaxtap.ru/media/products/images/пакет_черный.jpeg', NULL, NULL, '2026-02-13 09:31:28'),
(28, 13, 24, 'Тканевая маска с экстрактом кактуса FlaxMask', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/233232.jpg', NULL, NULL, '2026-02-13 09:31:28'),
(29, 14, 27, 'Обучение + Двойной набор FlaxLift', 22900.00, 1, 0.00, 0.00, 22900.00, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, NULL, '2026-02-13 09:33:56'),
(30, 14, 26, 'Обучение + Набор FlaxLift', 16690.00, 1, 0.00, 0.00, 16690.00, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, NULL, '2026-02-13 09:33:56'),
(31, 14, 28, 'Обучение FlaxLift', 17000.00, 1, 0.00, 0.00, 17000.00, 'https://flaxtap.ru/media/products/images/кругляш_лифт.jpeg', NULL, NULL, '2026-02-13 09:33:56'),
(32, 15, 15, 'Очищающий гель с экстрактами грибов FlaxGel', 2390.00, 1, 0.00, 0.00, 2390.00, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0095.jpg', NULL, NULL, '2026-02-13 09:35:24'),
(33, 15, 27, 'Обучение + Двойной набор FlaxLift', 22900.00, 1, 0.00, 0.00, 22900.00, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, NULL, '2026-02-13 09:35:24'),
(34, 16, 27, 'Обучение + Двойной набор FlaxLift', 22900.00, 1, 0.00, 0.00, 22900.00, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, NULL, '2026-02-13 09:36:21'),
(35, 16, 24, 'Тканевая маска с экстрактом кактуса FlaxMask', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/233232.jpg', NULL, NULL, '2026-02-13 09:36:21'),
(36, 17, 21, 'Крем-масло для тела FlaxButter', 2290.00, 1, 0.00, 0.00, 2290.00, 'https://flaxtap.ru/media/products/images/photo_2025-12-19_12-16-39.jpg', NULL, NULL, '2026-03-12 11:41:32'),
(37, 17, 11, 'Оранжевый пакет FlaxTap', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/пакет_оранжевый.jpeg', NULL, NULL, '2026-03-12 11:41:32'),
(38, 17, 12, 'Черный пакет FlaxTap', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/пакет_черный.jpeg', NULL, NULL, '2026-03-12 11:41:32'),
(39, 18, 16, 'Маска для губ FlaxLips', 790.00, 1, 0.00, 0.00, 790.00, 'https://flaxtap.ru/media/products/images/ночная_маска_для_губ_flaxtap.jpg', NULL, NULL, '2026-03-12 11:42:09'),
(40, 19, 26, 'Обучение + Набор FlaxLift', 16690.00, 1, 0.00, 0.00, 16690.00, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, NULL, '2026-03-12 11:42:54'),
(41, 20, 6, 'Маска Брусника FlaxFaceMask', 1000.00, 1, 0.00, 0.00, 1000.00, 'https://flaxtap.ru/media/products/images/бруснич_маск.JPG', NULL, NULL, '2026-03-12 11:45:34'),
(42, 21, 23, 'Крем для рук FlaxHandCream', 1090.00, 1, 0.00, 0.00, 1090.00, 'https://flaxtap.ru/media/products/images/крем_для_рук_Flaxtap_f7TABt2.jpg', NULL, NULL, '2026-03-12 11:48:10'),
(43, 22, 26, 'Обучение + Набор FlaxLift', 16690.00, 1, 0.00, 0.00, 16690.00, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, NULL, '2026-03-12 11:53:47'),
(44, 23, 26, 'Обучение + Набор FlaxLift', 16690.00, 1, 0.00, 0.00, 16690.00, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, NULL, '2026-03-12 11:55:57'),
(46, 25, 11, 'Оранжевый пакет FlaxTap', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/пакет_оранжевый.jpeg', NULL, NULL, '2026-04-04 11:26:53'),
(47, 25, 18, 'Бамбуковое скраб-мыло FlaxSoapBamboo', 2290.00, 1, 0.00, 0.00, 2290.00, 'https://flaxtap.ru/media/products/images/бамбук_новый.jpg', NULL, NULL, '2026-04-04 11:26:53'),
(48, 26, 31, 'FlaxTap Parfum (ФлаксТап Парфюм) 14 Pulse(Пульс) (ЧЗ)', 1990.00, 1, 0.00, 0.00, 1990.00, 'https://flaxtap.ru/media/products/images/14.jpg', NULL, NULL, '2026-04-16 14:03:55'),
(49, 27, 14, 'Бальзам для губ FlaxLips', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/бальзам_для_губ.jpg', NULL, NULL, '2026-04-18 10:53:13'),
(50, 27, 27, 'Обучение + Двойной набор FlaxLift', 22900.00, 1, 0.00, 0.00, 22900.00, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, NULL, '2026-04-18 10:53:13'),
(51, 27, 28, 'Обучение FlaxLift', 17000.00, 1, 0.00, 0.00, 17000.00, 'https://flaxtap.ru/media/products/images/кругляш_лифт.jpeg', NULL, NULL, '2026-04-18 10:53:13'),
(52, 27, 19, 'Дренажный коктейль FlaxFit', 2290.00, 1, 0.00, 0.00, 2290.00, 'https://flaxtap.ru/media/products/images/Дренажныи_коктеиль_FlaxFit_Gu6xAXo.jpeg', NULL, NULL, '2026-04-18 10:53:13'),
(53, 28, 3, 'Пенка для кожи склонной к высыпаниям FlaxFoam', 2290.00, 1, 0.00, 0.00, 2290.00, 'https://flaxtap.ru/media/products/images/%D0%BF%D0%B5%D0%BD%D0%BA%D0%B0_%D0%BA%D0%BE%D0%B6%D0%B0_%D1%81%D0%BA%D0%BB%D0%BE%D0%BD%D0%BD%D0%B0_%D0%BA__%D0%B2%D1%8B%D1%81%D1%8B%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC.jpg', NULL, NULL, '2026-04-18 14:21:50'),
(54, 28, 17, 'Универсальное средство FlaxSpa', 2190.00, 1, 0.00, 0.00, 2190.00, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0100.jpg', NULL, NULL, '2026-04-18 14:21:50'),
(55, 29, 27, 'Обучение + Двойной набор FlaxLift', 22900.00, 1, 0.00, 0.00, 22900.00, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, NULL, '2026-04-25 10:32:24'),
(56, 30, 14, 'Бальзам для губ FlaxLips', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/бальзам_для_губ.jpg', NULL, NULL, '2026-05-16 11:31:02'),
(57, 31, 28, 'Обучение FlaxLift', 17000.00, 1, 0.00, 0.00, 17000.00, 'https://flaxtap.ru/media/products/images/кругляш_лифт.jpeg', NULL, NULL, '2026-05-27 18:31:52'),
(58, 31, 14, 'Бальзам для губ FlaxLips', 390.00, 1, 0.00, 0.00, 390.00, 'https://flaxtap.ru/media/products/images/бальзам_для_губ.jpg', NULL, NULL, '2026-05-27 18:31:52'),
(59, 31, 31, 'FlaxTap Parfum (ФлаксТап Парфюм) 14 Pulse(Пульс) (ЧЗ)', 1990.00, 1, 0.00, 0.00, 1990.00, 'https://flaxtap.ru/media/products/images/14.jpg', NULL, NULL, '2026-05-27 18:31:52'),
(60, 32, 13, 'Маска Черника FlaxFaceMask', 1490.00, 1, 0.00, 0.00, 1490.00, 'https://flaxtap.ru/media/products/images/чернич_маск.JPG', NULL, NULL, '2026-06-15 10:08:42'),
(61, 32, 26, 'Обучение + Набор FlaxLift', 16690.00, 1, 0.00, 0.00, 16690.00, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, NULL, '2026-06-15 10:08:42'),
(62, 32, 28, 'Обучение FlaxLift', 17000.00, 1, 0.00, 0.00, 17000.00, 'https://flaxtap.ru/media/products/images/кругляш_лифт.jpeg', NULL, NULL, '2026-06-15 10:08:42'),
(63, 33, 9, 'Маска Клубника FlaxFaceMask', 1490.00, 1, 0.00, 0.00, 1490.00, 'https://flaxtap.ru/media/products/images/клубн_маск_nMMlsWq.JPG', NULL, NULL, '2026-06-16 17:41:03'),
(64, 33, 31, 'FlaxTap Parfum (ФлаксТап Парфюм) 14 Pulse(Пульс) (ЧЗ)', 1990.00, 1, 0.00, 0.00, 1990.00, 'https://flaxtap.ru/media/products/images/14.jpg', NULL, NULL, '2026-06-16 17:41:03'),
(65, 33, 26, 'Обучение + Набор FlaxLift', 16690.00, 1, 0.00, 0.00, 16690.00, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, NULL, '2026-06-16 17:41:03'),
(66, 33, 22, 'Скраб для тела Апельсиновый пирог FlaxScrub', 1690.00, 1, 0.00, 0.00, 1690.00, 'https://flaxtap.ru/media/products/images/photo_2025-12-08_16-29-20.jpg', NULL, NULL, '2026-06-16 17:41:03');

-- --------------------------------------------------------

--
-- Структура таблицы `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#3498db',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_statuses`
--

INSERT INTO `order_statuses` (`id`, `name`, `description`, `color`, `sort_order`, `created_at`) VALUES
(1, 'Новый', 'Заказ создан, ожидает обработки', '#3498db', 1, '2026-01-06 20:34:02'),
(2, 'Обработка', 'Заказ обрабатывается менеджером', '#f39c12', 2, '2026-01-06 20:34:02'),
(3, 'Подтвержден', 'Заказ подтвержден, готовится к отправке', '#2ecc71', 3, '2026-01-06 20:34:02'),
(4, 'Оплачен', 'Оплата получена', '#27ae60', 4, '2026-01-06 20:34:02'),
(5, 'В пути', 'Заказ передан в доставку', '#9b59b6', 5, '2026-01-06 20:34:02'),
(6, 'Доставлен', 'Заказ получен клиентом', '#1abc9c', 6, '2026-01-06 20:34:02'),
(7, 'Отменен', 'Заказ отменен', '#e74c3c', 7, '2026-01-06 20:34:02'),
(8, 'Возврат', 'Оформлен возврат товара', '#95a5a6', 8, '2026-01-06 20:34:02');

-- --------------------------------------------------------

--
-- Структура таблицы `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `old_status_id` int DEFAULT NULL,
  `new_status_id` int NOT NULL,
  `changed_by` int DEFAULT NULL COMMENT 'Кто изменил статус (user_id)',
  `change_reason` text COMMENT 'Причина изменения статуса',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `old_status_id`, `new_status_id`, `changed_by`, `change_reason`, `created_at`) VALUES
(19, 12, NULL, 1, 1, 'Заказ создан администратором', '2026-02-13 09:26:57'),
(20, 13, NULL, 3, 1, 'Заказ создан администратором', '2026-02-13 09:31:28'),
(21, 14, NULL, 4, 1, 'Заказ создан администратором', '2026-02-13 09:33:56'),
(22, 15, NULL, 6, 1, 'Заказ создан администратором', '2026-02-13 09:35:24'),
(23, 16, NULL, 1, 1, 'Заказ создан администратором', '2026-02-13 09:36:21'),
(24, 17, NULL, 1, 1, 'Заказ создан администратором', '2026-03-12 11:41:32'),
(25, 18, NULL, 1, 1, 'Заказ создан администратором', '2026-03-12 11:42:09'),
(26, 19, NULL, 5, 1, 'Заказ создан администратором', '2026-03-12 11:42:54'),
(27, 20, NULL, 1, 1, 'Заказ создан администратором', '2026-03-12 11:45:34'),
(28, 21, NULL, 1, 1, 'Заказ создан администратором', '2026-03-12 11:48:10'),
(29, 22, NULL, 6, 1, 'Заказ создан администратором', '2026-03-12 11:53:47'),
(30, 23, NULL, 2, 1, 'Заказ создан администратором', '2026-03-12 11:55:57'),
(33, 25, NULL, 1, 1, 'Заказ создан администратором', '2026-04-04 11:26:53'),
(34, 26, NULL, 1, 1, 'Заказ создан администратором', '2026-04-16 14:03:55'),
(35, 26, 1, 4, 1, 'Изменено администратором', '2026-04-18 09:45:39'),
(36, 27, NULL, 1, 1, 'Заказ создан администратором', '2026-04-18 10:53:13'),
(37, 28, NULL, 1, 1, 'Заказ создан администратором', '2026-04-18 14:21:50'),
(38, 29, NULL, 1, 13, 'Заказ создан администратором', '2026-04-25 10:32:24'),
(39, 29, 1, 3, 13, 'Изменено администратором', '2026-04-25 10:32:40'),
(40, 30, NULL, 1, 1, 'Заказ создан администратором', '2026-05-16 11:31:02'),
(41, 30, 1, 4, 1, 'Изменено администратором', '2026-05-16 11:31:37'),
(42, 31, NULL, 1, 1, 'Заказ создан администратором', '2026-05-27 18:31:52'),
(43, 32, NULL, 1, 1, 'Заказ создан администратором', '2026-06-15 10:08:42'),
(44, 33, NULL, 1, 1, 'Заказ создан администратором', '2026-06-16 17:41:03'),
(45, 33, 1, 3, 1, 'Изменено администратором', '2026-06-16 17:41:49');

-- --------------------------------------------------------

--
-- Структура таблицы `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `commission` decimal(5,2) DEFAULT '0.00',
  `sort_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `description`, `is_active`, `commission`, `sort_order`) VALUES
(1, 'Карта онлайн', 'Оплата банковской картой на сайте', 1, 0.00, 1),
(2, 'Картой при получении', 'Оплата картой курьеру или в пункте выдачи', 1, 0.00, 2),
(3, 'Наличными при получении', 'Оплата наличными при получении', 1, 0.00, 3),
(4, 'SberPay', 'Оплата через SberPay', 1, 0.00, 4),
(5, 'ЮMoney', 'Оплата через ЮMoney', 1, 0.00, 5),
(6, 'Tinkoff Pay', 'Оплата через Tinkoff Pay', 1, 0.00, 6);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `brand` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `short_description` text,
  `full_description` longtext,
  `image_url` varchar(500) NOT NULL,
  `current_price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `discount_percent` int DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL COMMENT 'Например: 320гр, 50мл',
  `skin_type` varchar(100) DEFAULT NULL COMMENT 'Тип кожи',
  `volume` varchar(50) DEFAULT NULL COMMENT 'Объем/размер',
  `composition` text COMMENT 'Состав продукта',
  `expiration` varchar(100) DEFAULT NULL COMMENT 'Срок годности',
  `storage_conditions` text COMMENT 'Условия хранения',
  `usage_method` text COMMENT 'Способ применения',
  `contraindications` text COMMENT 'Противопоказания',
  `animal_testing` varchar(100) DEFAULT NULL COMMENT 'Информация о тестировании на животных',
  `country_of_origin` varchar(100) DEFAULT NULL COMMENT 'Страна производства',
  `rating` decimal(3,2) DEFAULT '0.00',
  `review_count` int DEFAULT '0',
  `in_stock` tinyint(1) DEFAULT '1',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `delivery_time` varchar(50) DEFAULT '1-3 дня',
  `warranty` varchar(100) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand`, `name`, `slug`, `short_description`, `full_description`, `image_url`, `current_price`, `old_price`, `discount_percent`, `weight`, `skin_type`, `volume`, `composition`, `expiration`, `storage_conditions`, `usage_method`, `contraindications`, `animal_testing`, `country_of_origin`, `rating`, `review_count`, `in_stock`, `is_featured`, `is_bestseller`, `delivery_time`, `warranty`, `sort_order`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 4, 'FlaxScrub Professional', 'Сахарный скраб для тела FlaxScrub Манго', 'saharnyy-skrab-dlya-tela-flaxscrub-mango', 'Эффективное средство для бережного отшелушивания ороговевших клеток кожи. Очищает поры, стимулирует выведение токсинов, ускоряет обмен веществ.', 'Сахарный скраб для тела – это эффективное средство для бережного отшелушивания ороговевших клеток кожи. FlaxScrub очищает поры и стимулирует выведение токсинов, ускоряет обмен веществ, защищает кожу от негативного воздействия окружающей среды...', '/assets/media/products/527_sakharnyy-skrab-dlya-tela-flaxsc.jpg', 1390.00, 2950.00, 53, '320гр', 'Все типы', '320гр', 'Sugar, Glycine Soja Oil, Prunus Amygdalus Dulcis Oil, Cocos Nucifera Oil, Butyrospermum Parkii Butter, Glyceryl Stearate, PEG-7 Glyceryl Cocoate, Retinyl Palmitate, Tocopheryl Acetate, BHT, Parfum', '24 месяца в закрытом флаконе', 'Хранить вдали от отопительных приборов, при температуре от +5 до +25°C', 'Нанести небольшое количество скраба на сухую или влажную кожу. Массировать по лимфодренажным линиям. Тщательно смыть остатки водой.', 'Индивидуальная непереносимость компонентов', 'Не тестируется на животных', 'Россия', 4.80, 128, 1, 0, 0, '1-3 дня', NULL, 0, 'Сахарный скраб для тела FlaxScrub Манго - Каталог - FlaxTap', 'Сахарный скраб для тела FlaxScrub Манго - эффективное средство для бережного отшелушивания ороговевших клеток кожи. Очищает поры, стимулирует выведение токсинов, ускоряет обмен веществ.', NULL, '2026-01-06 20:29:30', '2026-01-06 20:29:30'),
(3, 1, 'FlaxFoam', 'Пенка для кожи склонной к высыпаниям FlaxFoam', 'oemya-dkh-ynez-ryknmmni-y-vhrhoamzhl-flaxfoam', 'FlaxFoam - умный уход для кожи, склонной к высыпаниям, с комплексом активных экстрактов.\r\n\r\nFlaxFoam нежно, но глубоко очищает кожу, удаляя загрязнения и излишки себума без пересушивания и сохраняя естественный защитный барьер.', '<p style=\"border: 0px solid; font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\"><br></p><p style=\"border: 0px solid; font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\"><span style=\"border: 0px solid; font-size: 14px;\"><span style=\"border: 0px solid; font-family: &quot;Times New Roman&quot;, Times, serif;\"><span style=\"border: 0px solid; font-weight: bolder;\">Противопоказания:&nbsp;</span>Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.</span></span></p><p style=\"border: 0px solid; font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\"><span style=\"border: 0px solid; font-size: 14px;\"><span style=\"border: 0px solid; font-family: &quot;Times New Roman&quot;, Times, serif;\"><span style=\"border: 0px solid; font-weight: bolder;\">Не тестируется на животных!</span></span></span></p>', 'https://flaxtap.ru/media/products/images/%D0%BF%D0%B5%D0%BD%D0%BA%D0%B0_%D0%BA%D0%BE%D0%B6%D0%B0_%D1%81%D0%BA%D0%BB%D0%BE%D0%BD%D0%BD%D0%B0_%D0%BA__%D0%B2%D1%8B%D1%81%D1%8B%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC.jpg', 2290.00, NULL, NULL, '200', 'Сухая', '200', 'Состав: Вода, кокоглюкозид, кокамидопропилбетаин, кокоилизетионат натрия,  ниацинамид, гидроксиэтилмочевина, гидролат мяты, гидролат зеленого чая, экстракт риса бурого, экстракт сфагнума, экстракт кипрея, экстракт фенхеля, экстракт фиалки, экстракт таволги, экстракт ромашки, экстракт кардамона, поликватерниум-10, пантенол, молочная кислота, метилпарабен, пропилпарабен, диазолидинил мочевина, отдушка.', '24', '12', 'Нанесите небольшое количество пенки на влажное лицо, помассируйте и смойте теплой водой.', 'нет', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', 'упк', 0, 'куп', 'куку', 'пкупам', '2026-01-08 20:53:57', '2026-02-08 13:35:59'),
(6, 1, 'Маска Брусника FlaxFaceMask', 'Маска Брусника FlaxFaceMask', 'larya-bptrmzya-flaxfacemask', 'Маска Брусника FlaxFaceMask', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Маска Брусника FlaxFaceMask</h3>', 'https://flaxtap.ru/media/products/images/бруснич_маск.JPG', 1000.00, 1790.00, 44, '', '', '', '', '', '', '', '', '', '', 0.00, 0, 1, 1, 1, '1-3 дня', '', 0, '', '', '', '2026-01-31 15:06:39', '2026-01-31 16:14:53'),
(8, 2, 'FlaxTap', 'Большой пакет с рисунком FlaxTap', 'bnkscni-oayes-r-pzrtmynl-flaxtap', 'Пакет бумажный с рисунком FlaxTap 20*10*22см', '<p><em style=\"border: 0px solid; font-family: &quot;Times New Roman&quot;, Times, serif; font-size: 14px;\"><span style=\"border: 0px solid; font-weight: bolder;\">Пакет бумажный с рисунком&nbsp;FlaxTap 20*10*22см</span></em></p>', 'https://flaxtap.ru/media/products/images/пакет_на_сайт_с_рисунком_Dh6Orhx.jpg', 390.00, NULL, NULL, '-', '-', '20*10*22см', '-', '-', '-', '-', '-', '-', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '', 0, 'Пакет бумажный с рисунком FlaxTap 20*10*22см', 'Пакет бумажный с рисунком FlaxTap 20*10*22см', 'Пакет ', '2026-02-13 07:29:53', '2026-02-13 07:29:53'),
(9, 1, 'FlaxFaceMask', 'Маска Клубника FlaxFaceMask', 'larya-yktbmzya-flaxfacemask', 'FlaxFaceMask Клубника – мгновенное сияние и свежесть для Вашей кожи! Экстракт клубники, богатый фруктовыми кислотами, мягко обновляет и выравнивает тон, придавая коже естественное сияние.', '<p>FlaxFaceMask Клубника</p>', 'https://flaxtap.ru/media/products/images/клубн_маск_nMMlsWq.JPG', 1490.00, 1790.00, 17, '320гр.', 'Все типы', '100мл.', 'вода, пропиленгликоль, гидроксиэтилмочевина, пантенол, глицерин, натрий РСА, глюкоза, мочевина, глутаминовая кислота, лизин, глицин, аллантоин, молочная кислота, сухой экстракт клубники, метилпарабен, пропилпарабен, диазолидинил мочевина, отдушка, карбомер, триэтаноламин.', '36 месяца', 'При температуре от +5 до +10', 'Нанести маску тонким слоем на чистую, сухую, протонизированную кожу. Оставить на 10-15 минут. По истечению времени смыть маску теплой водой. Протонизировать и нанести крем по типу кожи.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '', 0, 'Маска Клубника FlaxFaceMask', 'Маска Клубника FlaxFaceMask', 'FlaxFaceMask', '2026-02-13 07:34:11', '2026-02-13 07:35:39'),
(10, 3, 'Маска Брусника FlaxFaceMask', 'Маска Брусника FlaxFaceMask', 'larya-bptrmzya-fflaxfacemask', 'Маска Брусника FlaxFaceMask', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Маска Брусника FlaxFaceMask</h3>', 'https://flaxtap.ru/media/products/images/бруснич_маск.JPG', 1490.00, 1790.00, 17, '320гр.', 'Все типы', '100мл.', 'вода, пропиленгликоль, гидроксиэтилмочевина, пантенол, глицерин, натрий РСА, глюкоза, мочевина, глутаминовая кислота, лизин, глицин, аллантоин, молочная кислота, биоферментированная брусника, метилпарабен, пропилпарабен, диазолидинил мочевина, отдушка, карбомер, триэтаноламин.', '36 месяца', 'При температуре от +5 до +10', 'Нанести маску тонким слоем на чистую, сухую, протонизированную кожу. Оставить на 10-15 минут. По истечению времени смыть маску теплой водой. Протонизировать и нанести крем по типу кожи.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Маска Брусника FlaxFaceMask', 'Маска Брусника FlaxFaceMask', 'Маска Брусника FlaxFaceMask', '2026-02-13 07:40:20', '2026-02-13 07:40:20'),
(11, 2, 'Оранжевый пакет FlaxTap', 'Оранжевый пакет FlaxTap', 'npameevhi-oayes-flaxtap', 'Оранжевый пакет с логотипом FlaxTap 20*10*22см', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Оранжевый пакет FlaxTap</h3>', 'https://flaxtap.ru/media/products/images/пакет_оранжевый.jpeg', 390.00, NULL, NULL, '-', '-', '20*10*22см', '-', '-', '-', '-', '-', '-', 'Россия', 0.00, 0, 1, 0, 0, '1-3 дня', '12 мес.', 0, 'Оранжевый пакет FlaxTap', 'Оранжевый пакет FlaxTap', 'Оранжевый пакет FlaxTap', '2026-02-13 07:43:21', '2026-02-13 07:43:21'),
(12, 2, 'Черный пакет FlaxTap', 'Черный пакет FlaxTap', 'cepmhi-oayes-flaxtap', 'Черный пакет с логотипом Flaxtap 20*10*22см', '<p>Черный пакет с логотипом Flaxtap 20*10*22см</p>', 'https://flaxtap.ru/media/products/images/пакет_черный.jpeg', 390.00, NULL, NULL, '-', '-', '20*10*22см', '-', '-', '-', '-', '-', '-', '-', 0.00, 0, 1, 0, 0, '1-3 дня', '12 мес.', 0, 'Черный пакет FlaxTap', 'Черный пакет FlaxTap', 'Черный пакет FlaxTap', '2026-02-13 07:44:33', '2026-02-13 07:44:33'),
(13, 4, 'Маска Черника FlaxFaceMask', 'Маска Черника FlaxFaceMask', 'larya-cepmzya-zflaxfacemask', 'Маска Черника FlaxFaceMask', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Маска Черника FlaxFaceMask</h3>', 'https://flaxtap.ru/media/products/images/чернич_маск.JPG', 1490.00, 1790.00, 17, '320гр.', 'Все типы', '100мл.', 'вода, пропиленгликоль, гидроксиэтилмочевина, пантенол, глицерин, натрий РСА, глюкоза, мочевина, глутаминовая кислота, лизин, глицин, аллантоин, молочная кислота, биоферментированная черника, метилпарабен, пропилпарабен, диазолидинил мочевина, отдушка, карбомер, триэтаноламин.', '36 месяца', 'При температуре от +5 до +10', 'Нанести маску тонким слоем на чистую, сухую, протонизированную кожу. Оставить на 10-15 минут. По истечению времени смыть маску теплой водой. Протонизировать и нанести крем по типу кожи.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Маска Черника FlaxFaceMask', 'Маска Черника FlaxFaceMask', 'Маска Черника FlaxFaceMask', '2026-02-13 07:48:21', '2026-02-13 07:48:21'),
(14, 5, 'Бальзам для губ FlaxLips', 'Бальзам для губ FlaxLips', 'baksjal-dkh-gtb-flaxlips', 'Бальзам для губ FlaxLips', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Бальзам для губ FlaxLips</h3>', 'https://flaxtap.ru/media/products/images/бальзам_для_губ.jpg', 390.00, NULL, NULL, '120гр.', 'Все типы', '120гр.', 'соевый воск, масло ши, масло авокадо, масло манго, масло макадамии, пантенол, витамин Е, изопропилмиристат, гидролизированный микрокристаллический воск, парафин, жидкий парафин, сукралоза, парфюм.', '24 месяца', 'При температуре от +5 до +10', 'Наносить на кожу губ для увлажнения по мере необходимости.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт, прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12', 0, 'Бальзам для губ FlaxLips', 'Бальзам для губ FlaxLips', 'Бальзам для губ FlaxLips', '2026-02-13 07:50:32', '2026-02-13 07:50:32'),
(15, 5, 'Очищающий гель с экстрактами грибов FlaxGel', 'Очищающий гель с экстрактами грибов FlaxGel', 'nczhachzi-geks-r-hyrspaysalz-gpzbnv-flaxgel', 'Очищающий гель с экстрактами грибов FlaxGel', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Очищающий гель с экстрактами грибов FlaxGel</h3>', 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0095.jpg', 2390.00, 3390.00, 29, '320гр.', 'Все типы', '100мл.', 'Вода, кокамидопропил бетаин, лаурил глюкозид, лаурет сульфат натрия, бетаин, ППГ-3 каприлиловый эфир, экстракт кордицепса китайского, экстракт шиитаке ,экстракт тремеллы фукусовидной, экстракт ганодермы лакированной (рейши), поликватерниум-10, молочная кислота, метилпарабен, пропилпарабен, диазолидинил мочевина, отдушка.', '36 месяца', 'При температуре от +5 до +10', 'Вспеньте небольшое количество геля в ладонях. Нанесите массажными движениями на влажную кожу лица, избегая область вокруг глаз. Тщательно смойте водой.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Очищающий гель с экстрактами грибов FlaxGel', 'Очищающий гель с экстрактами грибов FlaxGel', 'Очищающий гель с экстрактами грибов FlaxGel', '2026-02-13 07:54:33', '2026-02-13 07:54:33'),
(16, 4, 'Маска для губ FlaxLips', 'Маска для губ FlaxLips', 'larya-dkh-gtb-flaxlips', 'Маска для губ FlaxLips', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Маска для губ FlaxLips</h3>', 'https://flaxtap.ru/media/products/images/ночная_маска_для_губ_flaxtap.jpg', 790.00, NULL, NULL, '320гр.', 'Все типы', '100мл.', 'Масло ши, масло авокадо, масло жожоба, соевый воск, пчелиный воск, масло кокоса, цетиловый спирт, мика, вазелин, отдушка.', '36 месяца', 'При температуре от +5 до +10', 'Нанести маску на чистую, сухую и/или проскрабированную кожу губ. Не смывать.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт, прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 0, '1-3 дня', '12 мес.', 0, 'Маска для губ FlaxLips', 'Маска для губ FlaxLips', 'Маска для губ FlaxLips', '2026-02-13 08:00:38', '2026-02-13 08:00:38'),
(17, 3, 'Универсальное средство FlaxSpa', 'Универсальное средство FlaxSpa', 'tmzvepraksmne-rpedrsvn-flaxspa', 'Универсальное средство FlaxSpa', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Универсальное средство FlaxSpa</h3>', 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0100.jpg', 2190.00, 3190.00, 31, '320гр.', 'Все типы', '100мл.', 'вазелиновое масло, парафин, вазелин, масло ши, витамин Е, отдушка.', '36 месяца', 'При температуре от +5 до +10', 'Нанесите FlaxSpa на очищенную и протинизированную кожу рук, ног или других сухих участков тела (для наилучшего эффекта нанесите под FlaxSpa сыворотку или крем). Наденьте сверху полиэтиленовые или термо-перчатки (для рук) и оставьте на 20-30 минут. Удалите остатки средства сухой салфеткой и избегайте контакта с водой 1-2 часа.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '12 мес.', 0, 'Универсальное средство FlaxSpa', 'Универсальное средство FlaxSpa', 'Универсальное средство FlaxSpa', '2026-02-13 08:27:38', '2026-02-13 08:27:38'),
(18, 1, 'Бамбуковое скраб-мыло FlaxSoapBamboo', 'Бамбуковое скраб-мыло FlaxSoapBamboo', 'balbtynvne-rypab-lhkn-flaxsoapbamboo', 'Бамбуковое скраб-мыло FlaxSoapBamboo', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Бамбуковое скраб-мыло FlaxSoapBamboo</h3>', 'https://flaxtap.ru/media/products/images/бамбук_новый.jpg', 2290.00, 3290.00, 30, '320гр.', 'Все типы', '100мл.', '', '36 месяца', 'При температуре от +5 до +10', 'небольшое количество густого мыла нанести на спонж или руки и тщательно вспенить. Выполнить процедуру очищения кожи. Тщательно смыть проточной водой. Рекомендуется использовать после данной процедуры очищения - крем/увлажняющий лосьон для рук и тела той же серии. Густое мыло идеально для бани, SPA-процедур и сауны. Если использовать в качестве шампуня – то достаточно нанести небольшое количество средства на корни волос – растереть массирующими движениями и смыть. Прекрасно работает в сочетании с сывороткой-спреем для волос FlaxHair, а также с маской-желе FlaxJell.', 'нету', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '12 мес.', 0, 'Бамбуковое скраб-мыло FlaxSoapBamboo', 'Бамбуковое скраб-мыло FlaxSoapBamboo', 'Бамбуковое скраб-мыло FlaxSoapBamboo', '2026-02-13 08:31:14', '2026-02-13 08:31:14'),
(19, 2, 'Дренажный коктейль FlaxFit', 'Дренажный коктейль FlaxFit', 'dpemaemhi-ynyseiks-flaxfit', 'Дренажный коктейль FlaxFit', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Дренажный коктейль FlaxFit</h3>', 'https://flaxtap.ru/media/products/images/Дренажныи_коктеиль_FlaxFit_Gu6xAXo.jpeg', 2290.00, 3290.00, 30, '320гр.', 'Все типы', '100мл.', 'В составе два уникальных таежных Сибирских компонента:\r\n- Хвоя пихты Сибирской - содержит полипренолы, ускоряющие восстановление клеток печени.\r\n- Кедровая клетчатка (мягкая пленка, покрывающая ядра кедрового ореха) - мощный антиоксидант.\r\n\r\nСостав: Пищевые волокна: пшеничные, камеди акации, морских водорослей, яблока; зелёный кофе; мука льняная; кедровая клетчатка; мука и семена чиа; корица; активированная хвоя пихты; ламинария; имбирь; душица; корень солодки; тысячелистник; листья крапивы; листья подорожника; корень одуванчика; крушина; семена укропа; витаминный комплекс (таурин, кофеин, (В1), (В6), (В12), (В9), (РР), (В5), (Н), (С)).', '36 месяца', 'При температуре от +5 до +10', 'для приготовления напитка растворить содержимое 1 саше (14 гр) в  200-250 мл воды теплой или комнатной температуры (горячей водой нельзя!). Принимать 1-2 раза в день. Минимальный рекомендуемый курс очищения – 3-4 недели. Для увеличения эффективности во время курса необходимо пить 2-3 литра чистой воды ежедневно.', 'Беременность, период лактации, индивидуальная непереносимость компонентов', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '12 мес.', 0, 'Дренажный коктейль FlaxFit', 'Дренажный коктейль FlaxFit', 'Дренажный коктейль FlaxFit', '2026-02-13 08:34:00', '2026-02-13 08:34:00'),
(20, 2, 'Напиток FlaxDetox', 'Напиток FlaxDetox', 'maozsny-flaxdetox', 'Напиток FlaxDetox', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Напиток FlaxDetox</h3>', 'https://flaxtap.ru/media/products/images/напиток_детокс.jpeg', 2290.00, 3290.00, 30, '320гр.', 'Все типы', '100мл.', 'Растворимые пищевые волокна (инулин и полидекстроза), мякоть лимона, яблоко, псиллиум, стабилизаторы: альгинат натрия (экстракт морских водорослей), гуаровая камедь (экстракт смолы дерева), ксантановая (природное пищевое волокно), куркума, подсластитель (инулин, сукралоза), экстракт эхинацеи, топинамбур, экстракт асаи, экстракт артишока, Поливитаминный энергетический комплекс: таурин, кофеин, В1, В6, В12, В9, РР, В5, Н, С; Минеральный комплекс: кальций, калий, магний, железо, цинк, медь, марганец, йод, селен.', '36 месяца', 'При температуре от +5 до +10', 'Для приготовления напитка развести содержимое 1 саше (10 г) в 200-250 мл воды теплой или комнатной температуры (горячую воду использовать нельзя). Принимать до трех раз в день. Оптимальное время приема - за 20-30 минут до еды, можно использовать в качестве перекуса. Минимальный рекомендованный курс - 3-4 недели. Для увеличения эффективности во время курса необходимо выпивать суточную норму воды.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт, прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Напиток FlaxDetox', 'Напиток FlaxDetox', 'Напиток FlaxDetox', '2026-02-13 08:50:32', '2026-02-13 08:50:32'),
(21, 1, 'Крем-масло для тела FlaxButter', 'Крем-масло для тела FlaxButter', 'ypel-larkn-dkh-seka-flaxbutter', 'Крем-масло для тела FlaxButter', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Крем-масло для тела FlaxButter</h3>', 'https://flaxtap.ru/media/products/images/photo_2025-12-19_12-16-39.jpg', 2290.00, 3290.00, 30, '320гр.', 'Все типы', '100мл.', 'вода, органическое масло черной оливы, органическое масло ши, органическое масло арганы, органическое масло сладкого миндаля, органическое масло жожоба, органическое масло зародышей пшеницы, органическое масло авокадо, токоферол ацетат, коко-каприлат, каприлик/каприк триглицериды, глицерил стеарат, цетеариловый спирт, гидроксиэтилмочевина, ксантановая камедь, мика, кремний, полиакрилат натрия, глицерин феноксиэтанол/этилгексилглицерин, тетранатриевая соль ЭДТА, цетеарет-12, отдушка.', '36 месяца', 'При температуре от +5 до +10', 'Нанесите крем-масло на чистую, сухую или слегка влажную кожу, равномерно распределите легкими массажными движениями до полного впитывания.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт, прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Крем-масло для тела FlaxButter', 'Крем-масло для тела FlaxButter', 'Крем-масло для тела FlaxButter', '2026-02-13 08:53:40', '2026-02-13 08:53:40'),
(22, 3, 'Скраб для тела Апельсиновый пирог FlaxScrub', 'Скраб для тела Апельсиновый пирог FlaxScrub', 'rypab-dkh-seka-aoeksrzmnvhi-ozpng-flaxscrub', 'Скраб для тела Апельсиновый пирог FlaxScrub', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Скраб для тела Апельсиновый пирог FlaxScrub</h3>', 'https://flaxtap.ru/media/products/images/photo_2025-12-08_16-29-20.jpg', 1690.00, 2690.00, 37, '320гр.', 'Все типы', '100мл.', 'Сахар, вазелиновое масло, масло кокоса, масло ши, моностеарат глицерина, ПЭГ-7 глицерил кокоат, абразив косточки грецкого ореха, токоферол ацетат (витамин Е), отдушка, CI 15985', '36 месяца', 'При температуре от +5 до +10', 'Нанести небольшое количество скраба на влажную кожу. Массировать по лимфодренажным линиям. Тщательно смыть остатки водой.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 0, '1-3 дня', '12 мес.', 0, 'Скраб для тела Апельсиновый пирог FlaxScrub', 'Скраб для тела Апельсиновый пирог FlaxScrub', 'Скраб для тела Апельсиновый пирог FlaxScrub', '2026-02-13 08:57:22', '2026-02-13 08:57:22'),
(23, 4, 'Крем для рук FlaxHandCream', 'Крем для рук FlaxHandCream', 'ypel-dkh-pty-flaxhandcream', 'Крем для рук FlaxHandCream\r\n', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Крем для рук FlaxHandCream</h3><div><br></div>', 'https://flaxtap.ru/media/products/images/крем_для_рук_Flaxtap_f7TABt2.jpg', 1090.00, 2090.00, 48, '320гр.', 'Все типы', '100мл.', 'вода, гидроксиэтил мочевина, циклометикон,  масло авокадо, масло макадамии, аргановое масло, масло жожоба, масло семян граната, гиалуроновая кислота, молочная кислота, моностеарат глицерина, глицерин, изопропилмиристат, масло ши, стеариловый спирт, цетиловый спирт, ПЭГ-2 стеарат, изододекан, парафин, гидрогенизированное касторовое масло, этилгексилглицерин, каприлик/каприловый триглицерид, дипропиленгликоль, стеариловый диметикон, феноксиэтанол, отдушка.', '36 месяца', 'При температуре от +5 до +10', 'Нанесите крем на чистую кожу легкими массирующими движениями, втирайте до полного впитывания.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт, прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '12 мес.', 0, 'Крем для рук FlaxHandCream', 'Крем для рук FlaxHandCream\r\n', 'Крем для рук FlaxHandCream', '2026-02-13 09:12:12', '2026-02-13 09:12:12'),
(24, 4, 'Тканевая маска с экстрактом кактуса FlaxMask', 'Тканевая маска с экстрактом кактуса FlaxMask', 'syamevah-larya-r-hyrspaysnl-yaystra-flaxmask', 'Тканевая маска с экстрактом кактуса FlaxMask', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Тканевая маска с экстрактом кактуса FlaxMask</h3>', 'https://flaxtap.ru/media/products/images/233232.jpg', 390.00, 490.00, 20, '320гр.', 'Все типы', '100мл.', 'Вода, глицерин, экстракт стебля опунции индийской, глицерет-26, бетаин, карбомер, ксантановая камедь, 1,2-гександиол, бутиленгликоль, экстракт водоросли, экстракт кипрея узколистного, аргинин, экстракт плодов перца зубчатого, экстракт плодов аниса, экстракт цветков жимолости японской, экстракт грейпфрута, экстракт корня шлемника байкальского, динатриевая соль ЭДТА, гиалуронат натрия, феноксиэтанол.', '36 месяца', 'При температуре от +5 до +10', 'Очистите лицо от макияжа и загрязнений. Аккуратно разверните маску и нанесите на лицо. Через 15-20 минут снимите маску и распределите оставшуюся эссенцию массирующими движениями по лицу.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт - прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Тканевая маска с экстрактом кактуса FlaxMask', 'Тканевая маска с экстрактом кактуса FlaxMask', 'Тканевая маска с экстрактом кактуса FlaxMask', '2026-02-13 09:14:52', '2026-02-13 09:14:52'),
(25, 1, 'Мицеллярная вода FlaxWater', 'Мицеллярная вода FlaxWater', 'lzhekkhpmah-vnda-flaxwater', 'Мицеллярная вода FlaxWater\r\n', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Мицеллярная вода FlaxWater</h3><div><br></div>', 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0076_1_MVupsnK.jpg', 2190.00, 3090.00, 29, '320гр.', 'Все типы', '100мл.', 'Вода, децил глюкозид, глицерин, алоэ вера гель, пантенол, феноксиэтанол, этилгексилглицерин, молочная кислота, ЭДТА, отдушка.', '36 месяца', 'При температуре от +5 до +10', 'Нанесите средство на сухой ватный диск. Деликатно протирайте кожу меняя ватные диски до тех пор, пока они не останутся чистыми. Рекомендуется использовать один ватный диск для одной зоны лица.', 'Индивидуальная непереносимость компонентов. Если возникает раздражение, покраснение, дискомфорт, прекратите использование.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Мицеллярная вода FlaxWater', 'Мицеллярная вода FlaxWater\r\n', 'Мицеллярная вода FlaxWater', '2026-02-13 09:16:14', '2026-02-13 09:17:51'),
(26, 2, 'Обучение + Набор FlaxLift', 'Обучение + Набор FlaxLift', 'nbtcemze-mabnp-flaxlift', 'Обучение + Набор FlaxLift', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Обучение + Набор FlaxLift</h3>', 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', 16690.00, NULL, NULL, '320гр.', 'Все типы', '100мл.', 'Вода, сода, глицерин, пропиленгликоль, гидролат сосны, гидролат розы, экстракт солодки, экстракт конского каштана, экстракт эвкалипта, экстракт шиповника, ксантановая камедь, камфора, кофеин, феноксиэтанол, этилгексилглицерин, эфирное масло лимона, эфирное масло мандарина, эфирное масло мяты', '36 месяца', 'При температуре от +5 до +10', 'После прохождения курса Вы получаете сертификат нашей Академии на Ваше имя, по которому в дальнейшем Вы сможете приобретать наборы FlaxLift, а также всю нашу продукцию по более выгодным ценам!\r\nПосле оплаты обучения, Вам предоставят всю необходимую информацию о процедуре и её проведении, отправят методическое пособие и добавят в чат мастеров, где Вы сможете уточнить все интересующие Вас вопросы, а также в этом чате у Вас будет доступ к огромному фотобанку для Вашего контента, который можно использовать!!!', 'После прохождения курса Вы получаете сертификат нашей Академии на Ваше имя, по которому в дальнейшем Вы сможете приобретать наборы FlaxLift, а также всю нашу продукцию по более выгодным ценам!\r\nПосле оплаты обучения, Вам предоставят всю необходимую информацию о процедуре и её проведении, отправят методическое пособие и добавят в чат мастеров, где Вы сможете уточнить все интересующие Вас вопросы, а также в этом чате у Вас будет доступ к огромному фотобанку для Вашего контента, который можно использовать!!!', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '12 мес.', 0, 'Обучение + Набор FlaxLift', 'Обучение + Набор FlaxLift', 'Обучение + Набор FlaxLift', '2026-02-13 09:19:12', '2026-02-13 09:19:12'),
(27, 2, 'Обучение + Двойной набор FlaxLift', 'Обучение + Двойной набор FlaxLift', 'nbtcemze-dvnimni-mabnp-flaxlift', 'Обучение + Двойной набор FlaxLift', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Обучение + Двойной набор FlaxLift</h3>', 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', 22900.00, NULL, NULL, '320гр.', 'Все типы', '100мл.', 'Вода, сода, глицерин, пропиленгликоль, гидролат сосны, гидролат розы, экстракт солодки, экстракт конского каштана, экстракт эвкалипта, экстракт шиповника, ксантановая камедь, камфора, кофеин, феноксиэтанол, этилгексилглицерин, эфирное масло лимона, эфирное масло мандарина, эфирное масло мяты', '36 месяца', 'При температуре от +5 до +10', 'После прохождения курса Вы получаете сертификат нашей Академии на Ваше имя, по которому в дальнейшем Вы сможете приобретать наборы FlaxLift, а также всю нашу продукцию по более выгодным ценам!\r\nПосле оплаты обучения, Вам предоставят всю необходимую информацию о процедуре и её проведении, отправят методическое пособие и добавят в чат мастеров, где Вы сможете уточнить все интересующие Вас вопросы, а также в этом чате у Вас будет доступ к огромному фотобанку для Вашего контента, который можно использовать!!!', 'После прохождения курса Вы получаете сертификат нашей Академии на Ваше имя, по которому в дальнейшем Вы сможете приобретать наборы FlaxLift, а также всю нашу продукцию по более выгодным ценам!\r\nПосле оплаты обучения, Вам предоставят всю необходимую информацию о процедуре и её проведении, отправят методическое пособие и добавят в чат мастеров, где Вы сможете уточнить все интересующие Вас вопросы, а также в этом чате у Вас будет доступ к огромному фотобанку для Вашего контента, который можно использовать!!!', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 0, 1, '1-3 дня', '12 мес.', 0, 'Обучение + Двойной набор FlaxLift', 'Обучение + Двойной набор FlaxLift', 'Обучение + Двойной набор FlaxLift', '2026-02-13 09:20:39', '2026-02-13 09:20:39'),
(28, 2, 'Обучение FlaxLift', 'Обучение FlaxLift', 'nbtcemze-flaxlift', 'Обучение FlaxLift', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Обучение FlaxLift</h3>', 'https://flaxtap.ru/media/products/images/кругляш_лифт.jpeg', 17000.00, NULL, NULL, '-', '-', '-', 'Обучение FlaxLift', '-', '-', 'Обучение FlaxLift', 'Обучение FlaxLift', '-', '-', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Обучение FlaxLift', 'Обучение FlaxLift', 'Обучение FlaxLift', '2026-02-13 09:22:41', '2026-02-13 09:22:41'),
(29, 3, 'Гелевая лимонная маска FlaxGelMask', 'Гелевая лимонная маска FlaxGelMask', 'gekevah-kzlnmmah-larya-flaxgelmask', 'Гелевая лимонная маска FlaxGelMask', '<h3 class=\"text-2xl font-bold text-gray-900 mb-4\" style=\"border: 0px solid; margin-bottom: 16px; font-size: 24px; line-height: 1.33333; --tw-font-weight: 700; color: oklch(0.21 0.034 264.665); font-family: Inter, ui-sans-serif, &quot;system-ui&quot;, -apple-system, &quot;system-ui&quot;, &quot;Segoe UI&quot;, Roboto, &quot;Helvetica Neue&quot;, Arial, &quot;Noto Sans&quot;, &quot;sans-serif&quot;, &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Segoe UI Symbol&quot;, &quot;Noto Color Emoji&quot;;\">Гелевая лимонная маска FlaxGelMask</h3>', 'https://flaxtap.ru/media/products/images/лимонная_маска_Flaxta.jpeg', 2990.00, 3990.00, 25, '320гр.', 'Все типы', '100мл.', 'Вода, экстракт лимона, экстракт листьев мелии азедарах, экстракт цветка мелии азедарах, пантенол, бутиленгликоль, экстракт куркумы, экстракт цветов и листьев ж базилика, экстракт листьев кацимума, гиалуронат натрия, глицерин, экстракт хмеля, экстракт листьев лаванды, экстракт календулы, экстракт ромашки аптечной, экстракт кожуры лимона, экстракт семян огурца, экстракт листьев зелёного чая, экстракт яблока, экстракт спирулины платенсис, бензиловый спирт, гидроксид натрия, дегидроуксусная кислота.', '36 месяца', 'При температуре от +5 до +10', 'Нанести небольшое количество маски на чистую область лица и шеи. Оставить на 10 минут. Смыть теплой водой. Использовать 2-3 раза в неделю.', 'Индивидуальная непереносимость компонентов', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'Гелевая лимонная маска FlaxGelMask', 'Гелевая лимонная маска FlaxGelMask', 'Гелевая лимонная маска FlaxGelMask', '2026-02-13 09:25:18', '2026-02-13 09:25:18'),
(31, 3, 'FlaxTap Parfum', 'FlaxTap Parfum (ФлаксТап Парфюм) 14 Pulse(Пульс) (ЧЗ)', 'flaxtap-parfum-ukayrsao-oapucl-14-pulseotksr-cj', 'FlaxTap Parfum 14 Pulse - 10мл', '<p><em style=\"border: 0px solid; font-family: &quot;Times New Roman&quot;, Times, serif; font-size: 14px;\"><span style=\"border: 0px solid; font-weight: bolder;\">FlaxTap Parfum 14 Pulse - 10мл</span></em></p>', 'https://flaxtap.ru/media/products/images/14.jpg', 1990.00, NULL, NULL, '10мл', 'Все типы', '10мл', 'Alcohol Denat., Fragrance (Parfum), Ethylhexyl Methoxycinnamate, Butyl Methoxydibenzoylmethane, Ethylhexyl Salicylate, Linalool, Limonene, Citronellol, Citral, Geraniol, Cinnamal, Eugenol.', '36 мес', 'При температуре от +5 до +10', 'Небольшое количество нанести на тело, избегая попадания в глаза.', 'Индивидуальная непереносимость компонентов.', 'Не тестируется на животных!', 'Россия', 0.00, 0, 1, 1, 1, '1-3 дня', '12 мес.', 0, 'FlaxTap Parfum 14 Pulse - 10мл', 'FlaxTap Parfum 14 Pulse - 10мл', 'FlaxTap Parfum 14 Pulse - 10мл', '2026-04-16 14:03:12', '2026-04-16 14:03:12');

-- --------------------------------------------------------

--
-- Структура таблицы `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `attribute_name` varchar(100) NOT NULL,
  `attribute_value` text NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product_attributes`
--

INSERT INTO `product_attributes` (`id`, `product_id`, `attribute_name`, `attribute_value`, `sort_order`, `created_at`) VALUES
(1, 1, 'Бренд', 'FlaxScrub Professional', 1, '2026-01-06 20:29:30'),
(2, 1, 'Объем', '320гр', 2, '2026-01-06 20:29:30'),
(3, 1, 'Тип кожи', 'Все типы кожи', 3, '2026-01-06 20:29:30'),
(4, 1, 'Страна производства', 'Россия', 4, '2026-01-06 20:29:30'),
(5, 1, 'Срок годности', '24 месяца в закрытом флаконе', 5, '2026-01-06 20:29:30'),
(6, 1, 'Срок годности после вскрытия', '6 месяцев', 6, '2026-01-06 20:29:30'),
(7, 1, 'Условия хранения', 'Хранить вдали от отопительных приборов, при температуре от +5 до +25°C', 7, '2026-01-06 20:29:30'),
(8, 1, 'Тестирование', 'Не тестируется на животных', 8, '2026-01-06 20:29:30'),
(13, 3, 'са', 'ас', 0, '2026-02-08 13:35:59');

-- --------------------------------------------------------

--
-- Структура таблицы `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_main` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `alt_text`, `sort_order`, `is_main`, `created_at`) VALUES
(1, 1, '/assets/media/products/527_sakharnyy-skrab-dlya-tela-flaxsc.jpg', 'Сахарный скраб для тела FlaxScrub Манго', 0, 1, '2026-01-06 20:29:30'),
(2, 1, '/assets/media/products/527_sakharnyy-skrab-dlya-tela-flaxsc.jpg', 'Фото 2', 0, 0, '2026-01-06 20:29:30'),
(3, 1, '/assets/media/products/527_sakharnyy-skrab-dlya-tela-flaxsc.jpg', 'Фото 3', 0, 0, '2026-01-06 20:29:30'),
(13, 3, 'https://flaxtap.ru/media/products/images/%D0%BF%D0%B5%D0%BD%D0%BA%D0%B0_%D0%BA%D0%BE%D0%B6%D0%B0_%D1%81%D0%BA%D0%BB%D0%BE%D0%BD%D0%BD%D0%B0_%D0%BA__%D0%B2%D1%8B%D1%81%D1%8B%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F%D0%BC.jpg', NULL, 0, 0, '2026-02-08 13:35:59'),
(14, 8, 'https://flaxtap.ru/media/products/images/пакет_на_сайт_с_рисунком_Dh6Orhx.jpg', NULL, 0, 0, '2026-02-13 07:29:53'),
(18, 9, 'https://flaxtap.ru/media/products/images/клубн_маск_nMMlsWq.JPG', NULL, 0, 0, '2026-02-13 07:35:39'),
(19, 10, 'https://flaxtap.ru/media/products/images/бруснич_маск.JPG', NULL, 0, 0, '2026-02-13 07:40:20'),
(20, 11, 'https://flaxtap.ru/media/products/images/пакет_оранжевый.jpeg', NULL, 0, 0, '2026-02-13 07:43:21'),
(21, 12, 'https://flaxtap.ru/media/products/images/пакет_черный.jpeg', NULL, 0, 0, '2026-02-13 07:44:33'),
(22, 13, 'https://flaxtap.ru/media/products/images/чернич_маск.JPG', NULL, 0, 0, '2026-02-13 07:48:22'),
(23, 14, 'https://flaxtap.ru/media/products/images/бальзам_для_губ.jpg', NULL, 0, 0, '2026-02-13 07:50:32'),
(24, 15, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0095.jpg', NULL, 0, 0, '2026-02-13 07:54:33'),
(25, 16, 'https://flaxtap.ru/media/products/images/ночная_маска_для_губ_flaxtap.jpg', NULL, 0, 0, '2026-02-13 08:00:38'),
(26, 17, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0100.jpg', NULL, 0, 0, '2026-02-13 08:27:38'),
(27, 18, 'https://flaxtap.ru/media/products/images/бамбук_новый.jpg', NULL, 0, 0, '2026-02-13 08:31:14'),
(28, 19, 'https://flaxtap.ru/media/products/images/Дренажныи_коктеиль_FlaxFit_Gu6xAXo.jpeg', NULL, 0, 0, '2026-02-13 08:34:00'),
(29, 20, 'https://flaxtap.ru/media/products/images/напиток_детокс.jpeg', NULL, 0, 0, '2026-02-13 08:50:32'),
(30, 21, 'https://flaxtap.ru/media/products/images/photo_2025-12-19_12-16-39.jpg', NULL, 0, 0, '2026-02-13 08:53:40'),
(31, 22, 'https://flaxtap.ru/media/products/images/photo_2025-12-08_16-29-20.jpg', NULL, 0, 0, '2026-02-13 08:57:22'),
(32, 23, 'https://flaxtap.ru/media/products/images/крем_для_рук_Flaxtap_f7TABt2.jpg', NULL, 0, 0, '2026-02-13 09:12:12'),
(33, 24, 'https://flaxtap.ru/media/products/images/233232.jpg', NULL, 0, 0, '2026-02-13 09:14:52'),
(35, 25, 'https://flaxtap.ru/media/products/images/IMG-20251013-WA0076_1_MVupsnK.jpg', NULL, 0, 0, '2026-02-13 09:17:51'),
(36, 26, 'https://flaxtap.ru/media/products/images/набор_Flaxlift.jpeg', NULL, 0, 0, '2026-02-13 09:19:12'),
(37, 27, 'https://flaxtap.ru/media/products/images/Двойной_набор_Lift_8TLipG2.jpg', NULL, 0, 0, '2026-02-13 09:20:39'),
(38, 28, 'https://flaxtap.ru/media/products/images/Декларация_FlaxLift_Гель_для_восстановления_тонуса_кожи.jpg', NULL, 0, 0, '2026-02-13 09:22:41'),
(39, 29, 'https://flaxtap.ru/media/products/images/Гелевая_лимонная_маска_FlaxGelMask.jpg', NULL, 0, 0, '2026-02-13 09:25:18');

-- --------------------------------------------------------

--
-- Структура таблицы `product_questions`
--

CREATE TABLE `product_questions` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text,
  `answered_by` int DEFAULT NULL,
  `is_answered` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `answered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `product_questions`
--

INSERT INTO `product_questions` (`id`, `product_id`, `user_id`, `user_name`, `user_email`, `question`, `answer`, `answered_by`, `is_answered`, `created_at`, `answered_at`) VALUES
(23, 1, NULL, 'TEST', 'test@gmail.com', 'TEST SQL', 'спасибо за ответ!', 1, 1, '2026-02-08 12:12:50', '2026-02-08 13:30:33'),
(24, 20, NULL, 'Ксения', 'user12443@ex.ru', 'Можно ли использовать Напиток FlaxDetox для беременных?', NULL, NULL, 0, '2026-02-13 08:51:27', NULL),
(25, 28, NULL, 'Эмилия', 'user123@ex.ru', 'А  сертификат о прохождении будет?', NULL, NULL, 0, '2026-02-13 09:55:21', NULL),
(26, 8, NULL, 'Эмилия', 'user123@ex.ru', 'Хороший пакет! ', NULL, NULL, 0, '2026-02-13 09:55:36', NULL),
(27, 6, NULL, 'Эмилия', 'user123@ex.ru', 'Можно ли применять аллергикам?', NULL, NULL, 0, '2026-02-13 09:56:05', NULL),
(28, 29, NULL, 'Эмилия', 'user123@ex.ru', 'Хороший товар!', NULL, NULL, 0, '2026-02-13 09:56:35', NULL),
(29, 8, NULL, 'Тестовый запрос 12.03.2025', 'colchin.vl4d@yandex.ru', 'Тестовый запрос 12.03.2025', NULL, NULL, 0, '2026-03-12 13:08:20', NULL),
(30, 8, NULL, 'Тестовый запрос 12.03.2025 6.5.0', 'colchin.vl4d@yandex.ru', 'Тестовый запрос 12.03.2025 6.5.0', 'спс', 1, 1, '2026-03-12 13:39:02', '2026-03-12 13:47:21'),
(31, 31, NULL, 'Владислав', 'colchin.vl4d@yandex.ru', 'Крутой парфюм!', 'Да, крутой', 1, 1, '2026-04-16 14:04:52', '2026-04-17 12:23:54');

-- --------------------------------------------------------

--
-- Структура таблицы `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `rating` int NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `comment` text,
  `helpful_yes` int DEFAULT '0',
  `helpful_no` int DEFAULT '0',
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `stores`
--

CREATE TABLE `stores` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Название магазина/точки',
  `type` enum('shop','warehouse_shop','pickup_point') DEFAULT 'shop' COMMENT 'Тип точки',
  `address` text NOT NULL COMMENT 'Полный адрес',
  `city` varchar(100) NOT NULL DEFAULT 'Санкт-Петербург',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Широта для карты',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Долгота для карты',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Телефон точки',
  `email` varchar(100) DEFAULT NULL COMMENT 'Email точки',
  `manager_name` varchar(100) DEFAULT NULL COMMENT 'Имя менеджера',
  `working_hours_weekdays` varchar(100) DEFAULT '9:00-20:00' COMMENT 'Пн-Пт',
  `working_hours_saturday` varchar(100) DEFAULT '10:00-18:00' COMMENT 'Суббота',
  `working_hours_sunday` varchar(100) DEFAULT '10:00-18:00' COMMENT 'Воскресенье',
  `working_hours_notes` text COMMENT 'Особые примечания по времени работы',
  `is_24_7` tinyint(1) DEFAULT '0' COMMENT 'Круглосуточно',
  `description` text COMMENT 'Описание точки',
  `facilities` text COMMENT 'Удобства (пример: парковка, примерочные и т.д.)',
  `area_size` decimal(10,2) DEFAULT NULL COMMENT 'Площадь в м²',
  `has_parking` tinyint(1) DEFAULT '0' COMMENT 'Есть парковка',
  `parking_spots` int DEFAULT '0' COMMENT 'Количество парковочных мест',
  `is_wheelchair_accessible` tinyint(1) DEFAULT '0' COMMENT 'Доступно для инвалидов-колясочников',
  `max_order_weight` decimal(10,2) DEFAULT NULL COMMENT 'Максимальный вес заказа для самовывоза (кг)',
  `storage_temperature` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Температурный режим хранения',
  `has_refrigeration` tinyint(1) DEFAULT '0' COMMENT 'Есть холодильное оборудование',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Точка активна',
  `is_default` tinyint(1) DEFAULT '0' COMMENT 'Точка по умолчанию',
  `sort_order` int DEFAULT '0' COMMENT 'Порядок сортировки',
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL COMMENT 'Кто создал запись',
  `updated_by` int DEFAULT NULL COMMENT 'Кто обновил запись'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `stores`
--

INSERT INTO `stores` (`id`, `name`, `type`, `address`, `city`, `latitude`, `longitude`, `phone`, `email`, `manager_name`, `working_hours_weekdays`, `working_hours_saturday`, `working_hours_sunday`, `working_hours_notes`, `is_24_7`, `description`, `facilities`, `area_size`, `has_parking`, `parking_spots`, `is_wheelchair_accessible`, `max_order_weight`, `storage_temperature`, `has_refrigeration`, `is_active`, `is_default`, `sort_order`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(1, 'Основной магазин', 'shop', 'Санкт-Петербург, ул. Косметологов, 15', 'Санкт-Петербург', 59.93860000, 30.31410000, '+7 (812) 123-45-67', 'use1213r@ex.ru', 'Миронов П.Е.', '9:00-20:00', '10:00-18:00', '10:00-18:00', '-', 0, 'Основной розничный магазин сети FlaxTap. Широкий ассортимент косметики и профессиональных средств.', 'Парковка, примерочные, консультация косметолога, зона отдыха', 1.00, 1, 4, 1, NULL, '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\OSPanel\\domains\\flaxtap\\admin\\stores\\edit.php</b> on line <b>414</b><br />', 0, 1, 1, 1, 'Основной магазин FlaxTap в Санкт-Петербурге', 'Посетите наш основной магазин по адресу: ул. Косметологов, 15. Широкий выбор косметики, профессиональные консультации.', 'Основной магазин FlaxTap в Санкт-Петербурге', '2026-01-06 20:39:31', '2026-02-13 09:48:13', NULL, 1),
(2, 'Магазин на Невском', 'shop', 'Санкт-Петербург, Невский пр., 100', 'Санкт-Петербург', 59.93110000, 30.36090000, '+7 (812) 234-56-78', 'use1213r@ex.ru', 'Миронов П.Е.', '10:00-22:00', '10:00-22:00', '10:00-22:00', '-', 0, 'Магазин в центре города на Невском проспекте. Удобное расположение, большой выбор профессиональной косметики.', 'Парковка, бесплатный Wi-Fi, зона тестирования, кофейня', 1.00, 1, 4, 1, NULL, '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\OSPanel\\domains\\flaxtap\\admin\\stores\\edit.php</b> on line <b>414</b><br />', 0, 1, 0, 2, 'Магазин FlaxTap на Невском проспекте', 'Магазин косметики FlaxTap в центре Санкт-Петербурга на Невском проспекте, 100. Работаем до 22:00.', 'Магазин FlaxTap на Невском проспекте', '2026-01-06 20:39:31', '2026-02-13 09:49:01', NULL, 1),
(3, 'Склад-магазин на Лиговке', 'warehouse_shop', 'Санкт-Петербург, Лиговский пр., 50', 'Санкт-Петербург', 59.93430000, 30.33510000, '+7 (812) 345-67-89', 'use1213r@ex.ru', 'Миронов П.Е.', '8:00-18:00', '9:00-16:00', '10:00-18:00', '-', 0, 'Складской магазин с оптовыми ценами. Большие объемы, хранение в правильных температурных условиях.', 'Парковка для грузового транспорта, погрузочная площадка, терминал оплаты', 4.00, 1, 4, 0, 45.00, '+15°C до +25°C', 1, 1, 0, 3, 'Склад-магазин FlaxTap на Лиговском проспекте', 'Складской магазин косметики FlaxTap с оптовыми ценами. Работаем с профессионалами и розничными покупателями.', 'Склад-магазин FlaxTap на Лиговском проспекте', '2026-01-06 20:39:31', '2026-02-13 09:50:26', NULL, 1),
(4, 'Пункт выдачи на Васильевском острове', 'pickup_point', 'Санкт-Петербург, В.О., 6-я линия, 25', 'Санкт-Петербург', 43.00000000, 23.00000000, '+7 (812) 456-78-90', 'use1213r@ex.ru', 'Миронов П.Е.', '10:00-20:00', '10:00-18:00', '10:00-18:00', '-', 0, 'Пункт выдачи заказов на Васильевском острове. Удобно для жителей В.О.', 'нет', 3.00, 1, 3, 0, NULL, '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\OSPanel\\domains\\flaxtap\\admin\\stores\\edit.php</b> on line <b>414</b><br />', 0, 1, 0, 4, 'Пункт выдачи на Васильевском острове', 'Пункт выдачи на Васильевском острове', 'Пункт выдачи на Васильевском острове', '2026-01-06 20:39:31', '2026-02-13 09:51:17', NULL, 1),
(11, 'Магазин г.Печора РК11', 'shop', 'ул.Свободы', 'Печора', 59.00000000, 30.00000000, '+78121234567', 'use1213r@ex.ru', 'Лаврентьева Эмилия Владимировна', '9:00-20:00', '10:00-18:00', '10:00-18:00', '-', 0, 'Магазин г.Печора', '-', 10.00, 1, 1, 0, NULL, '', 0, 1, 0, 0, 'М - Магазин FlaxTap', 'М по адресу:', 'М - Магазин FlaxTap', '2026-02-13 09:53:38', '2026-04-17 11:23:45', 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `store_images`
--

CREATE TABLE `store_images` (
  `id` int NOT NULL,
  `store_id` int NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `store_reviews`
--

CREATE TABLE `store_reviews` (
  `id` int NOT NULL,
  `store_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `rating` int NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `comment` text,
  `pros` text COMMENT 'Плюсы',
  `cons` text COMMENT 'Минусы',
  `is_approved` tinyint(1) DEFAULT '0',
  `helpful_yes` int DEFAULT '0',
  `helpful_no` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `store_staff`
--

CREATE TABLE `store_staff` (
  `id` int NOT NULL,
  `store_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL COMMENT 'Должность',
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `training_requests`
--

CREATE TABLE `training_requests` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `social_link` varchar(500) DEFAULT NULL COMMENT 'Ссылка на соцсеть',
  `message` text NOT NULL,
  `course_type` varchar(100) DEFAULT NULL COMMENT 'Тип курса',
  `status` enum('new','contacted','enrolled','rejected') DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0' COMMENT 'Прочитана ли заявка',
  `notes` text COMMENT 'Заметки администратора',
  `assigned_to` int DEFAULT NULL COMMENT 'Кому назначена заявка',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal' COMMENT 'Приоритет заявки',
  `updated_by` int DEFAULT NULL COMMENT 'Кто обновил заявку'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `training_requests`
--

INSERT INTO `training_requests` (`id`, `name`, `phone`, `email`, `social_link`, `message`, `course_type`, `status`, `ip_address`, `user_agent`, `created_at`, `updated_at`, `is_read`, `notes`, `assigned_to`, `priority`, `updated_by`) VALUES
(1, 'Иванова Анна Сергеевна', '+7 (999) 123-45-67', 'anna.ivanova@example.com', 'https://vk.com/anna_ivanova', 'Добрый день! Интересуюсь базовым курсом FlaxTap®. Хотела бы узнать подробнее о программе обучения, стоимости и ближайших датах старта. Есть ли возможность оплаты в рассрочку? Также интересует, выдается ли сертификат международного образца?', 'Базовый курс FlaxTap®', 'enrolled', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-01-05 11:30:22', '2026-02-02 10:56:58', 1, NULL, NULL, 'high', NULL),
(2, 'Петров Дмитрий Владимирович', '+7 (912) 345-67-89', 'dmitry.petrov@workmail.com', 'https://instagram.com/dima_petrov_beauty', 'Здравствуйте! Я работаю косметологом 3 года, хочу повысить квалификацию. Видел ваши работы на выставке CosmoProf. Интересует продвинутый курс с углубленным изучением коррекции. Есть ли возможность пройти обучение онлайн? Требуется ли предварительная подготовка? Готов предоставить портфолио своих работ.', 'Продвинутый курс', 'enrolled', '192.168.1.101', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1', '2026-01-06 08:45:10', '2026-01-08 11:03:05', 1, 'Клиент заинтересован, перезвонил сегодня в 10:00. Договорились о созвоне с преподавателем в пятницу в 15:00. Отправил на почту программу курса и требования1.', 1, 'urgent', 1),
(4, 'Козлов Алексей Николаевич', '+7 (903) 456-78-90', 'alex.kozlov@gmail.com', NULL, 'Интересует индивидуальное обучение. Не могу подстроиться под групповые занятия из-за плотного графика. Возможны ли занятия в выходные дни? Какой минимальный срок обучения? Готов рассмотреть интенсивный курс. Также хотел бы узнать о стоимости индивидуального обучения с Мариной Беляевой.', 'Индивидуальное обучение', 'enrolled', '192.168.1.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0', '2026-01-07 13:10:45', '2026-03-18 08:03:01', 1, 'нет', NULL, 'high', 1),
(5, 'Федорова Ольга Викторовна', '+7 (916) 789-01-23', 'olga.fedorova@mail.ru', 'https://vk.com/olga_fedorova_pmu', 'Добрый вечер! Записывалась на базовый курс на декабрь, но заболела. Возможно ли перенести обучение на февраль? Уже внесла предоплату 10000 руб. Также хотела бы уточнить по поводу набора для практики - что в него входит и можно ли докупить дополнительные инструменты?', 'Базовый курс FlaxTap®', 'enrolled', '192.168.1.104', 'Mozilla/5.0 (Linux; Android 13; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36', '2026-01-08 05:45:12', '2026-01-11 15:44:17', 1, 'Клиентка переносит обучение с декабря. Предоплата уже внесена. Согласовали новую дату - 10 февраля. Отправила обновленное расписание и список инструментов для докупки.', 1, 'normal', NULL),
(6, 'Николаева Марина Дмитриевна', '+7 (925) 123-45-67', 'marina.nikolaeva@protonmail.com', NULL, 'Здравствуйте! Я из Екатеринбурга, ищу представителя/преподавателя в своем городе. На сайте увидела, что у вас есть представители в разных городах. Как связаться с представителем в Екатеринбурге? Есть ли возможность пройти обучение локально или только в Москве/СПб? Какие документы нужны для зачисления?', 'Базовый курс FlaxTap®', 'rejected', '192.168.1.105', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36', '2026-01-08 09:15:30', '2026-02-02 12:48:39', 1, 'Клиентка из Екатеринбурга. На данный момент нет действующих преподавателей в ее городе. Предложили онлайн-обучение или поездку в Москву. Клиент отказался. Заявка отклонена.', NULL, 'low', 1),
(11, 'Колчин Владислав Сергеевич', '+7 (908) 719-35-93', 'colchin.vl4d@yandex.ru', 'wcdewed', 'Здравствуйте! Интересуюсь курсом: Базовый курс FlaxTap®. Хотел(а) бы узнать подробности о программе, стоимости и датах начала.', 'Обучение FlaxTap', 'contacted', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 YaBrowser/25.12.0.0 Safari/537.36', '2026-02-02 11:06:40', '2026-02-02 11:09:21', 1, 'vf', 1, 'high', 1),
(12, 'Борисов Юрий', '+7 (908) 719-35-93', 'colchin.vl4d@yandex.ru', 'wcdewed', 'Здравствуйте! Интересуюсь курсом: Базовый курс FlaxTap®. Хотел(а) бы узнать подробности о программе, стоимости и датах начала.', 'Обучение FlaxTap', 'new', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 YaBrowser/25.12.0.0 Safari/537.36', '2026-02-02 12:45:54', '2026-02-02 12:45:54', 0, NULL, NULL, 'normal', NULL),
(15, 'Лаврентьева Эмилия Владимировна', '+7 (912) 345-67-81', 'user123@ex.ru', 'wcdewed', 'Здравствуйте! Интересуюсь курсом: Продвинутый курс. Хотел(а) бы узнать подробности о программе, стоимости и датах начала.', 'Обучение FlaxTap', 'new', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 YaBrowser/25.12.0.0 Safari/537.36', '2026-02-13 09:54:24', '2026-02-13 09:54:24', 0, NULL, NULL, 'normal', NULL),
(16, 'Тестовый запрос 12.03.2025', '+7 (908) 719-35-93', 'colchin.vl4d@yandex.ru', 'wcdewed', 'Здравствуйте! Интересуюсь курсом: Базовый курс FlaxTap®. Хотел(а) бы узнать подробности о программе, стоимости и датах начала.', 'Обучение FlaxTap', 'enrolled', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36', '2026-03-12 13:10:22', '2026-04-19 09:09:19', 1, '', 1, 'urgent', 1),
(18, 'Колчин Владислав Сергеевич', '+7 (908) 719-35-93', 'colchin.vl4d@yandex.ru', 'wcdewed18', 'Здравствуйте! Интересуюсь курсом: Продвинутый курс. Хотел(а) бы узнать подробности о программе, стоимости и датах начала.', 'Обучение FlaxTap', 'enrolled', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36', '2026-04-18 10:51:59', '2026-04-18 14:20:05', 1, '-', 13, 'high', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Время последнего входа',
  `login_count` int DEFAULT '0' COMMENT 'Количество входов',
  `last_password_change` timestamp NULL DEFAULT NULL COMMENT 'Время последней смены пароля',
  `remember_token` varchar(100) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `email`, `telephone`, `role`, `created_at`, `updated_at`, `last_login_at`, `login_count`, `last_password_change`, `remember_token`, `token_expires_at`, `is_active`) VALUES
(1, 'Администратор', '$2y$12$TMr2yJVBsBb4aUYctA4dWO5yghaYky.tRowVPqpp/LT7zMC4dnw/m', 'admin@example.com', '+79991234567', 'admin', '2026-01-06 20:18:55', '2026-06-16 17:39:38', '2026-06-16 17:39:38', 131, '2026-03-12 13:11:45', NULL, NULL, 1),
(13, 'Колчин Владислав Сергеевич', '$2y$10$GEXTR480NDOaGJ6LbRMyLuxJyeV0U/xnJMkwJdigHb7Z3x9m0daJK', 'colchin.vl4d@yandex.ru', '+79087193593', 'admin', '2026-02-12 18:56:13', '2026-04-25 15:03:47', '2026-04-25 15:03:47', 3, '2026-02-12 18:56:13', NULL, NULL, 1),
(14, 'Зубкова Юлия Викторовна', '$2y$10$v5h7KO311zGEr8wfLAJJYumDwMhCGNpLRV2/rll0tc24Za/0s3vKS', 'user@ex.ru', '+79123456781', 'user', '2026-02-13 07:17:59', '2026-02-13 07:17:59', NULL, 0, '2026-02-13 07:17:59', NULL, NULL, 1),
(15, 'Борисов Юрий Николаевич', '$2y$10$g1cbvwbvFPDsuYrVkxPyROs/hqE8knV/kiO8aexPwcrPvyUuSSx7O', 'use1r@ex.ru', '+79123456781', 'user', '2026-02-13 07:21:33', '2026-02-13 07:21:33', NULL, 0, '2026-02-13 07:21:33', NULL, NULL, 1),
(16, 'Григорьева Анна Викторовна', '$2y$10$7Kju.wyA8As4mF1lHGhu.eW/Wv1xP0aK7Ae6R6cisPBlAlYP.x50.', 'user1@ex.ru', '+79123456781', 'user', '2026-02-13 07:22:31', '2026-02-13 07:22:31', NULL, 0, '2026-02-13 07:22:31', NULL, NULL, 1),
(17, 'Кузнецова Айлин Николаевна', '$2y$10$0UCd0r.mhHkGmpkHJw7ejO7h5hxo6OoNqetxIZod2pqvtmbWwKcvq', 'user12@ex.ru', '+79123456781', 'user', '2026-02-13 07:23:14', '2026-02-13 07:23:14', NULL, 0, '2026-02-13 07:23:14', NULL, NULL, 1),
(18, 'Миронов Егор Миронович', '$2y$10$Lbb2U.pLpTDi6XVNLp5F9u0UIhMmzBFMBvhIYrJ01PdpKqcmeHCsO', 'user3@ex.ru', '+79123456781', 'user', '2026-02-13 07:23:42', '2026-02-13 07:23:42', NULL, 0, '2026-02-13 07:23:42', NULL, NULL, 1),
(19, 'Лаврентьева Эмилия Владимировна', '$2y$10$uWIhfklytiHd4q6.Ti.Ph.1yJqDWoss9BFi8x.alqXbiyaE22swBC', 'user123@ex.ru', '+79123456781', 'user', '2026-02-13 07:24:04', '2026-02-13 07:24:04', NULL, 0, '2026-02-13 07:24:04', NULL, NULL, 1),
(20, 'Лапин Тимофей Павлович', '$2y$10$2ZjQUr4cp6HeXbwmR4MhQ.OrvMSn1BntX9XSI1MXsVI9sI8Fw/M/.', 'use1213r@ex.ru', '+79123456781', 'user', '2026-02-13 07:24:28', '2026-02-13 07:24:28', NULL, 0, '2026-02-13 07:24:28', NULL, NULL, 1),
(21, 'Рожкова Ксения Макаровна', '$2y$10$Rpg3rVrNQ/8EvLrzIdhnV.uQ9BWG0BfU5Iusp8J50r5lmTgISAIJi', 'user12443@ex.ru', '+79123456781', 'user', '2026-02-13 07:24:56', '2026-02-13 07:24:56', NULL, 0, '2026-02-13 07:24:56', NULL, NULL, 1),
(22, 'Егоров Денис Артёмович', '$2y$10$uBzqQ0qrNEIl3UzRXsKiMOoeB3BuYI9A3ydEqW7bGfm2ivn4mL6zi', 'use1213342r@ex.ru', '+79123456781', 'user', '2026-02-13 07:25:27', '2026-02-13 07:26:01', NULL, 0, '2026-02-13 07:25:27', NULL, NULL, 1),
(23, 'Тестовый запрос 12.03.2025', '$2y$12$mJO81epxJtUikjp2Z4yq1.oHiS4G/rC4Y6/zZ8s1fjg/8/LPkpkJm', 'colchin.vl42d@yandex.ru', '+79087193593', 'user', '2026-03-12 13:12:53', '2026-03-12 13:12:53', NULL, 0, '2026-03-12 13:12:53', NULL, NULL, 1),
(25, 'TEST', '$2y$12$5zqSpKITvkFXo6yPOaa0AuJSjNxaUCqugQPN9AlT8V5/IHi5VxFhO', 'test@gmail.com', '+79087193593', 'user', '2026-04-06 14:03:36', '2026-04-17 12:22:00', '2026-04-17 12:22:00', 1, '2026-04-06 14:03:36', NULL, NULL, 1),
(26, 'тест', '$2y$12$GftYmCD1yrOfOnA4ceYR8e2Dpkwe.JeIE.k7EIzyyMVhErp1Yee9G', 'user123@e2x.ru', '+7921377656', 'user', '2026-04-16 13:56:45', NULL, NULL, 0, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(100) NOT NULL COMMENT 'Название адреса (Дом, Работа и т.д.)',
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `notes` text COMMENT 'Комментарии для курьера',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `email_notifications` tinyint(1) DEFAULT '1',
  `sms_notifications` tinyint(1) DEFAULT '0',
  `promo_notifications` tinyint(1) DEFAULT '1',
  `language` varchar(10) DEFAULT 'ru',
  `currency` varchar(10) DEFAULT 'RUB',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancies`
--

CREATE TABLE `vacancies` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL COMMENT 'Название вакансии',
  `slug` varchar(200) NOT NULL COMMENT 'URL-адрес вакансии',
  `description` text COMMENT 'Описание вакансии',
  `responsibilities` text COMMENT 'Обязанности',
  `requirements` text COMMENT 'Требования к кандидату',
  `benefits` text COMMENT 'Что мы предлагаем',
  `work_type` enum('office','remote','hybrid') DEFAULT 'office' COMMENT 'Тип работы: офис/удаленно/гибрид',
  `employment_type` enum('full_time','part_time','freelance','internship') DEFAULT 'full_time' COMMENT 'Тип занятости',
  `experience_level` enum('no_experience','junior','middle','senior','lead') DEFAULT 'middle' COMMENT 'Уровень опыта',
  `salary_from` decimal(10,2) DEFAULT NULL COMMENT 'Зарплата от',
  `salary_to` decimal(10,2) DEFAULT NULL COMMENT 'Зарплата до',
  `salary_currency` varchar(10) DEFAULT '₽' COMMENT 'Валюта зарплаты',
  `salary_type` enum('monthly','hourly','project') DEFAULT 'monthly' COMMENT 'Тип оплаты',
  `salary_note` varchar(255) DEFAULT NULL COMMENT 'Примечание к зарплате (например, "+ бонусы")',
  `is_negotiable` tinyint(1) DEFAULT '0' COMMENT 'Зарплата по договоренности',
  `location_city` varchar(100) DEFAULT NULL COMMENT 'Город',
  `location_address` text COMMENT 'Адрес офиса',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Широта для карты',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Долгота для карты',
  `is_remote` tinyint(1) DEFAULT '0' COMMENT 'Удаленная работа',
  `education` varchar(100) DEFAULT NULL COMMENT 'Требования к образованию',
  `languages` text COMMENT 'Требуемые языки',
  `skills` text COMMENT 'Ключевые навыки',
  `department` varchar(100) DEFAULT NULL COMMENT 'Отдел/Департамент',
  `category_id` int DEFAULT NULL COMMENT 'Категория вакансии',
  `status` enum('active','draft','archived','closed') DEFAULT 'draft' COMMENT 'Статус вакансии',
  `priority` enum('urgent','normal','low') DEFAULT 'normal' COMMENT 'Приоритет вакансии',
  `badges` varchar(255) DEFAULT NULL COMMENT 'Бейджи вакансии (срочно, новая и т.д.)',
  `views_count` int DEFAULT '0' COMMENT 'Количество просмотров',
  `applications_count` int DEFAULT '0' COMMENT 'Количество откликов',
  `working_hours` varchar(100) DEFAULT NULL COMMENT 'График работы',
  `schedule_notes` text COMMENT 'Примечания к графику',
  `selection_process` text COMMENT 'Описание процесса отбора',
  `application_deadline` date DEFAULT NULL COMMENT 'Срок подачи заявки',
  `contact_person` varchar(100) DEFAULT NULL COMMENT 'Контактное лицо',
  `contact_email` varchar(100) DEFAULT NULL COMMENT 'Email для откликов',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT 'Телефон для связи',
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` text,
  `meta_keywords` text,
  `is_published` tinyint(1) DEFAULT '0' COMMENT 'Опубликована ли вакансия',
  `published_at` datetime DEFAULT NULL COMMENT 'Дата публикации',
  `created_by` int DEFAULT NULL COMMENT 'Кто создал вакансию',
  `updated_by` int DEFAULT NULL COMMENT 'Кто обновил вакансию',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `vacancies`
--

INSERT INTO `vacancies` (`id`, `title`, `slug`, `description`, `responsibilities`, `requirements`, `benefits`, `work_type`, `employment_type`, `experience_level`, `salary_from`, `salary_to`, `salary_currency`, `salary_type`, `salary_note`, `is_negotiable`, `location_city`, `location_address`, `latitude`, `longitude`, `is_remote`, `education`, `languages`, `skills`, `department`, `category_id`, `status`, `priority`, `badges`, `views_count`, `applications_count`, `working_hours`, `schedule_notes`, `selection_process`, `application_deadline`, `contact_person`, `contact_email`, `contact_phone`, `meta_title`, `meta_description`, `meta_keywords`, `is_published`, `published_at`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(12, 'Химик-технолог в лабораторию косметики', 'khimik-tekhnolog-v-laboratoriyu-kosmetiki', '<p>FlaxTap расширяет научно-исследовательскую лабораторию. Мы ищем увлеченного химика-технолога для разработки инновационных формул уходовой косметики (кремы, сыворотки, маски). Вы будете работать над созданием продуктов, которые задают тренды на рынке косметологии.</p>', '— Разработка рецептур новых косметических средств с нуля;\r\n— Модификация и улучшение существующих формул;\r\n— Подбор сырья, работа с поставщиками;\r\n— Проведение тестов стабильности и микробиологии;\r\n— Ведение технической документации (ТУ, паспорта).', '— Высшее химическое/биотехнологическое образование;\r\n— Опыт работы в косметической/фармацевтической лаборатории от 2 лет;\r\n— Знание основ коллоидной химии и технологии эмульсий;\r\n— Уверенный пользователь ПК, работа с базами сырья;\r\n— Желание учиться и следить за новинками ингредиентов.', '— Работа в современной лаборатории с новейшим оборудованием;\r\n— Возможность публикации патентов и научных статей;\r\n— ДМС со стоматологией;\r\n— Корпоративные скидки на продукцию;\r\n— Гибкий старт рабочего дня.', 'office', 'full_time', 'middle', 120000.00, 180000.00, '₽', 'monthly', 'нет', 0, 'Санкт-Петербург', 'ул. Косметологов, д. 15 (лабораторный корпус)', NULL, NULL, 0, 'Высшее (специалитет/магистратура)', 'Английский (чтение технической документации)', 'Разработка косметических средств, Коллоидная химия, Эмульгаторы, Стабилометрия, Аналитический контроль', 'R&D лаборатория', NULL, 'active', 'urgent', 'Срочно, Новая лаборатория', 0, 0, '5/2', NULL, 'испытание', '2026-06-30', 'Миронов Егор Миронович', 'use1213342r@ex.ru', 'нет', 'Химик-технолог косметики - Вакансия FlaxTap', 'Работа в R&D лаборатории FlaxTap. Разработка кремов, сывороток, масок. Конкурентная зарплата, ДМС, современное оборудование.', 'нет', 1, '2026-02-15 09:00:00', 1, 1, '2026-02-13 08:39:00', '2026-02-13 08:46:43'),
(13, 'SMM-маркетолог (Beauty-тематика)', 'smm-marketolog-beauty', '<p>FlaxTap — бренд №1 среди косметологов. Мы ищем креативного SMM-специалиста, который усилит наше присутствие в Instagram и VK. Важно: вы должны разбираться в косметологии или гореть желанием погрузиться в тему.</p>', '— Разработка контент-стратегии на месяц/квартал;\r\n— Написание постов, сценариев для Reels;\r\n— Координация съемок (предметная и процессная);\r\n— Взаимодействие с блогерами и инфлюенсерами;\r\n— Аналитика метрик и оптимизация контента.', '— Опыт работы SMM-менеджером от 1 года (портфолио обязательно);\r\n— Отличное знание Instagram и VK (таргетинг НЕ требуется, только контент);\r\n— Грамотный письменный русский язык;\r\n— Понимание визуального стиля; \r\n— Будет плюсом: опыт в beauty/медицине/косметологии.', '— Работа в крутой команде с собственной фотостудией;\r\n— Бесплатная продукция и тест-драйвы новинок;\r\n— Бесплатное обучение у косметологов;\r\n— Частично удаленный формат после испытательного;\r\n— Молодой дружный коллектив.', 'hybrid', 'full_time', 'junior', 70000.00, 90000.00, '₽', 'monthly', 'нет', 0, 'Санкт-Петербург', 'ул. Косметологов, д. 15', NULL, NULL, 1, 'Среднее профессиональное или выше', 'Русский язык', 'SMM, Instagram, Reels, Копирайтинг, Визуал', 'Маркетинг', NULL, 'active', 'normal', NULL, 0, 0, '5/2', NULL, 'нет', '2026-07-30', 'Миронов Егор Миронович', 'use1213342r@ex.ru', '+79123456781', 'SMM-маркетолог в бренд косметики - FlaxTap', 'Ведение Instagram/VK для профессиональной косметики. Контент, Reels, работа с блогерами. Зарплата до 90 000 ₽.', 'нет', 1, '2026-02-14 12:00:00', 1, 1, '2026-02-13 08:39:00', '2026-02-13 08:45:43'),
(14, 'PHP-разработчик (Yii2 / Laravel)', 'php-razrabotchik-yii2-laravel', '<p>Наш интернет-магазин активно растет. Планируется переезд на новую архитектуру и внедрение ERP-системы. Ищем разработчика для поддержки текущего функционала (PHP 8.1, Yii2/Laravel, MySQL) и разработки новых модулей.</p>', '— Разработка и доработка backend-части интернет-магазина;\r\n— Оптимизация SQL-запросов;\r\n— Интеграция с платежными системами и службами доставки (СДЭК, Boxberry);\r\n— Работа с API маркетплейсов;\r\n— Рефакторинг legacy-кода.', '— Коммерческий опыт от 2 лет на PHP 7.4+;\r\n— Фреймворки: Yii2 или Laravel;\r\n— Уверенное знание MySQL, умение писать сложные запросы;\r\n— Git, Composer;\r\n— Понимание ООП и SOLID.', '— Удаленная работа или гибрид;\r\n— Современный стек: PHP 8.1/8.2, Docker, GitLab;\r\n— Оплата профессиональной литературы/курсов;\r\n— Рабочее оборудование (ноутбук Mac/Windows);\r\n— Возможность влиять на технические решения.', 'remote', 'full_time', 'middle', 150000.00, 200000.00, '₽', 'monthly', 'нет', 1, 'Санкт-Петербург', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\OSPanel\\domains\\flaxtap\\admin\\vacancies\\edit.php</b> on line <b>524</b><br />', NULL, NULL, 1, 'Высшее (IT)', 'Английский (технический)', 'PHP, Yii2, Laravel, MySQL, Git, Docker, REST API', 'IT-отдел', NULL, 'active', 'normal', 'Удаленка', 0, 0, '5/2', NULL, 'испытание', '2026-07-22', 'Колчин Владислав Сергеевич', 'use1213342r@ex.ru', '+79123456781', 'PHP разработчик (Yii2/Laravel) - FlaxTap', 'Удаленная работа. Разработка и поддержка интернет-магазина косметики. PHP 8, MySQL, Yii2/Laravel.', '-', 1, '2026-02-13 10:00:00', 1, 1, '2026-02-13 08:39:00', '2026-02-13 08:43:26'),
(15, 'Преподаватель курсов (Перманентный макияж)', 'prepodavatel-kursov-permanentnyy-makiyazh', '<p>Учебный центр FlaxTap объявляет набор преподавателей по перманентному макияжу. Мы ищем действующего мастера с опытом преподавания для проведения авторских курсов (базовый и продвинутый уровни).</p>', '— Проведение теоретических и практических занятий;\r\n— Разработка учебных материалов и презентаций;\r\n— Оценка работ учеников, обратная связь;\r\n— Участие в днях открытых дверей;\r\n— Поддержание связи с выпускниками.', '— Действующий мастер ПМ с опытом от 5 лет;\r\n— Наличие сертификатов и портфолио;\r\n— Опыт преподавания от 1 года;\r\n— Коммуникабельность, умение доступно объяснять;\r\n— Готовность к командировкам (регионы РФ).', '— Доход напрямую зависит от количества учеников (процент + оклад);\r\n— Предоставление учебного класса, моделей, расходников;\r\n— Бесплатное повышение квалификации;\r\n— Гибкий график (можно совмещать с работой в салоне).', 'office', 'part_time', 'senior', 5000.00, 15000.00, '₽', 'project', 'нет', 0, 'Санкт-Петербург', 'ул. Косметологов, д. 15 (Учебный центр)', NULL, NULL, 0, 'Среднее специальное (медицинское/косметологическое)', 'Русский язык', 'Перманентный макияж, Обучение, ПМ, Татуаж', 'Учебный центр', NULL, 'active', 'urgent', 'Гибкий график, Премии', 0, 0, '5/2', NULL, 'испытание', '2026-09-24', 'Миронов Егор Миронович', 'use1213342r@ex.ru', '+79123456781', 'Преподаватель ПМ - Учебный центр FlaxTap', 'Ведение курсов по перманентному макияжу. Высокий процент, собственный класс. Действующим мастерам.', 'нет', 1, '2026-02-16 11:30:00', 1, 1, '2026-02-13 08:39:00', '2026-02-13 08:42:24'),
(16, 'Продавец-консультант (розница)', 'prodavets-konsultant-roznitsa', '<p>В связи с открытием новой точки на Невском проспекте, мы ищем продавца-консультанта для флагманского магазина. Если вы любите косметику и умеете общаться с людьми — вы наш человек!</p>', '— Консультирование покупателей в зале;\r\n— Помощь в подборе средств по типу кожи/проблеме;\r\n— Поддержание порядка на витринах и стоке;\r\n— Участие в инвентаризациях;\r\n— Работа в кассе (1С, АТОЛ).', '— Приветствуется опыт в beauty-ритейле (Золотое яблоко, Рив Гош, ЛЭтуаль);\r\n— Знание косметических брендов;\r\n— Активная жизненная позиция;\r\n— Желание учиться и знать продукт идеально.', '— Официальное трудоустройство;\r\n— График 2/2 (с 10:00 до 22:00);\r\n— Высокий % с продаж + оклад;\r\n— Корпоративная косметика;\r\n— Обучение за счет компании.', 'office', 'full_time', 'junior', 45000.00, 80000.00, '₽', 'monthly', 'нет', 0, 'Санкт-Петербург', 'Невский пр., д. 100 (Магазин на Невском)', NULL, NULL, 0, 'Среднее общее', 'Русский язык', 'Розничные продажи, Консультирование, Касса, 1С', 'Розничная сеть', NULL, 'active', 'urgent', 'Открытие новой точки', 0, 0, '5/2', NULL, '-', '2026-07-03', 'Миронов Егор Миронович', 'use1213r@ex.ru', '+79123456781', 'Продавец-консультант косметики - FlaxTap', 'Работа в флагманском магазине на Невском. График 2/2, зарплата до 80 000 ₽. Любовь к косметике обязательна.', '-', 1, '2026-02-12 08:45:00', 1, 1, '2026-02-13 08:39:00', '2026-02-13 08:41:18');

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_applications`
--

CREATE TABLE `vacancy_applications` (
  `id` int NOT NULL,
  `vacancy_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL COMMENT 'ID пользователя (если зарегистрирован)',
  `full_name` varchar(100) NOT NULL COMMENT 'ФИО кандидата',
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birth_date` date DEFAULT NULL COMMENT 'Дата рождения',
  `city` varchar(100) DEFAULT NULL COMMENT 'Город проживания',
  `expected_salary` decimal(10,2) DEFAULT NULL COMMENT 'Ожидаемая зарплата',
  `experience_years` int DEFAULT NULL COMMENT 'Опыт работы (лет)',
  `last_position` varchar(100) DEFAULT NULL COMMENT 'Последняя должность',
  `last_company` varchar(100) DEFAULT NULL COMMENT 'Последнее место работы',
  `education_level` varchar(100) DEFAULT NULL COMMENT 'Уровень образования',
  `portfolio_url` varchar(500) DEFAULT NULL COMMENT 'Ссылка на портфолио',
  `linkedin_url` varchar(500) DEFAULT NULL COMMENT 'Ссылка на LinkedIn',
  `github_url` varchar(500) DEFAULT NULL COMMENT 'Ссылка на GitHub',
  `cover_letter` text COMMENT 'Сопроводительное письмо',
  `resume_path` varchar(500) DEFAULT NULL COMMENT 'Путь к файлу резюме',
  `resume_original_name` varchar(255) DEFAULT NULL COMMENT 'Оригинальное имя файла',
  `status` enum('new','reviewed','interview','rejected','accepted') DEFAULT 'new',
  `status_notes` text COMMENT 'Комментарии по статусу',
  `recruiter_notes` text COMMENT 'Заметки рекрутера',
  `rating` int DEFAULT NULL COMMENT 'Оценка кандидата (1-5)',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `source` varchar(50) DEFAULT 'website' COMMENT 'Источник отклика',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `vacancy_applications`
--

INSERT INTO `vacancy_applications` (`id`, `vacancy_id`, `user_id`, `full_name`, `email`, `phone`, `birth_date`, `city`, `expected_salary`, `experience_years`, `last_position`, `last_company`, `education_level`, `portfolio_url`, `linkedin_url`, `github_url`, `cover_letter`, `resume_path`, `resume_original_name`, `status`, `status_notes`, `recruiter_notes`, `rating`, `ip_address`, `user_agent`, `source`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'Лукин Иван', 'ivanov1121223@example.co1m', '+72133242423', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Менеджер по продажам12', NULL, NULL, 'new', NULL, NULL, NULL, NULL, NULL, 'website', '2026-02-01 13:22:00', '2026-02-01 13:22:00'),
(5, 16, NULL, 'Егоров Денис Артёмович', 'use1213342r@ex.ru', '+79123456781', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Продавец-консультант (розница)', NULL, NULL, 'new', NULL, NULL, NULL, NULL, NULL, 'website', '2026-02-13 08:39:28', '2026-02-13 08:39:28'),
(6, 13, NULL, 'Тестовый запрос 12.03.2025', 'colchin.vl4d@yandex.ru', '+79087193593', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SMM-маркетолог (Beauty-тематика)', NULL, NULL, 'new', NULL, NULL, NULL, NULL, NULL, 'website', '2026-03-12 13:09:42', '2026-03-12 13:09:42'),
(7, 15, NULL, 'Тестовый запрос 12.03.2025 6.5.0', 'user123@ex.ru', '+79087193593', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Преподаватель курсов (Перманентный макияж)', NULL, NULL, 'new', NULL, NULL, NULL, NULL, NULL, 'website', '2026-03-12 13:40:08', '2026-03-12 13:40:08');

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_application_history`
--

CREATE TABLE `vacancy_application_history` (
  `id` int NOT NULL,
  `application_id` int NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int DEFAULT NULL COMMENT 'Кто изменил статус',
  `change_notes` text COMMENT 'Причина изменения',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_categories`
--

CREATE TABLE `vacancy_categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Название категории',
  `slug` varchar(100) NOT NULL COMMENT 'URL категории',
  `description` text COMMENT 'Описание категории',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Иконка категории',
  `parent_id` int DEFAULT NULL COMMENT 'Родительская категория',
  `sort_order` int DEFAULT '0' COMMENT 'Порядок сортировки',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Активна ли категория',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_selection_stages`
--

CREATE TABLE `vacancy_selection_stages` (
  `id` int NOT NULL,
  `vacancy_id` int NOT NULL,
  `stage_number` int NOT NULL COMMENT 'Номер этапа',
  `stage_name` varchar(100) NOT NULL COMMENT 'Название этапа',
  `description` text COMMENT 'Описание этапа',
  `duration_days` int DEFAULT NULL COMMENT 'Примерная длительность этапа',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Активен ли этап',
  `sort_order` int DEFAULT '0' COMMENT 'Порядок сортировки',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_skills`
--

CREATE TABLE `vacancy_skills` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL COMMENT 'Категория навыка',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_skill_pivot`
--

CREATE TABLE `vacancy_skill_pivot` (
  `vacancy_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `level` enum('basic','intermediate','advanced') DEFAULT 'basic',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_tags`
--

CREATE TABLE `vacancy_tags` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(20) DEFAULT '#3498db',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `vacancy_tag_pivot`
--

CREATE TABLE `vacancy_tag_pivot` (
  `vacancy_id` int NOT NULL,
  `tag_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_coupons_code` (`code`),
  ADD KEY `idx_coupons_is_active` (`is_active`);

--
-- Индексы таблицы `delivery_methods`
--
ALTER TABLE `delivery_methods`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_news_status` (`status`),
  ADD KEY `idx_news_published_at` (`published_at`),
  ADD KEY `idx_news_is_featured` (`is_featured`),
  ADD KEY `idx_news_category_id` (`category_id`),
  ADD KEY `idx_news_author_id` (`author_id`),
  ADD KEY `idx_news_created_at` (`created_at`),
  ADD KEY `idx_news_views_count` (`views_count`);

--
-- Индексы таблицы `news_attachments`
--
ALTER TABLE `news_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_attachments_news_id` (`news_id`);

--
-- Индексы таблицы `news_authors`
--
ALTER TABLE `news_authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `news_categories`
--
ALTER TABLE `news_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `news_comments`
--
ALTER TABLE `news_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_comments_news_id` (`news_id`),
  ADD KEY `idx_news_comments_user_id` (`user_id`),
  ADD KEY `idx_news_comments_parent_id` (`parent_id`),
  ADD KEY `idx_news_comments_is_approved` (`is_approved`),
  ADD KEY `idx_news_comments_created_at` (`created_at`);

--
-- Индексы таблицы `news_gallery`
--
ALTER TABLE `news_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_gallery_news_id` (`news_id`);

--
-- Индексы таблицы `news_news_tags`
--
ALTER TABLE `news_news_tags`
  ADD PRIMARY KEY (`news_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Индексы таблицы `news_related`
--
ALTER TABLE `news_related`
  ADD PRIMARY KEY (`news_id`,`related_news_id`),
  ADD KEY `related_news_id` (`related_news_id`);

--
-- Индексы таблицы `news_subscribers`
--
ALTER TABLE `news_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_news_subscribers_email` (`email`),
  ADD KEY `idx_news_subscribers_is_active` (`is_active`);

--
-- Индексы таблицы `news_tags`
--
ALTER TABLE `news_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_news_tags_slug` (`slug`),
  ADD KEY `idx_news_tags_is_active` (`is_active`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `delivery_method_id` (`delivery_method_id`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_status_id` (`status_id`),
  ADD KEY `idx_orders_order_number` (`order_number`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_customer_email` (`customer_email`),
  ADD KEY `idx_orders_customer_phone` (`customer_phone`);

--
-- Индексы таблицы `order_coupons`
--
ALTER TABLE `order_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `coupon_id` (`coupon_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order_id` (`order_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`);

--
-- Индексы таблицы `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_status_id` (`old_status_id`),
  ADD KEY `new_status_id` (`new_status_id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_order_status_history_order_id` (`order_id`),
  ADD KEY `idx_order_status_history_created_at` (`created_at`);

--
-- Индексы таблицы `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_price` (`current_price`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_in_stock` (`in_stock`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `product_questions`
--
ALTER TABLE `product_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `answered_by` (`answered_by`);

--
-- Индексы таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_stores_city` (`city`),
  ADD KEY `idx_stores_is_active` (`is_active`),
  ADD KEY `idx_stores_is_default` (`is_default`),
  ADD KEY `idx_stores_type` (`type`),
  ADD KEY `idx_stores_sort_order` (`sort_order`),
  ADD KEY `idx_stores_lat_lng` (`latitude`,`longitude`);

--
-- Индексы таблицы `store_images`
--
ALTER TABLE `store_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_images_store_id` (`store_id`);

--
-- Индексы таблицы `store_reviews`
--
ALTER TABLE `store_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_store_reviews_store_id` (`store_id`),
  ADD KEY `idx_store_reviews_rating` (`rating`);

--
-- Индексы таблицы `store_staff`
--
ALTER TABLE `store_staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_staff_store_id` (`store_id`);

--
-- Индексы таблицы `training_requests`
--
ALTER TABLE `training_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_assigned_to` (`assigned_to`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`);

--
-- Индексы таблицы `vacancies`
--
ALTER TABLE `vacancies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_vacancies_status` (`status`),
  ADD KEY `idx_vacancies_published` (`is_published`),
  ADD KEY `idx_vacancies_category` (`category_id`),
  ADD KEY `idx_vacancies_work_type` (`work_type`),
  ADD KEY `idx_vacancies_created_at` (`created_at`),
  ADD KEY `idx_vacancies_department` (`department`),
  ADD KEY `idx_vacancies_salary_from` (`salary_from`),
  ADD KEY `idx_vacancies_salary_to` (`salary_to`);

--
-- Индексы таблицы `vacancy_applications`
--
ALTER TABLE `vacancy_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_vacancy_applications_vacancy_id` (`vacancy_id`),
  ADD KEY `idx_vacancy_applications_status` (`status`),
  ADD KEY `idx_vacancy_applications_created_at` (`created_at`),
  ADD KEY `idx_vacancy_applications_email` (`email`);

--
-- Индексы таблицы `vacancy_application_history`
--
ALTER TABLE `vacancy_application_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Индексы таблицы `vacancy_categories`
--
ALTER TABLE `vacancy_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_vacancy_categories_parent_id` (`parent_id`),
  ADD KEY `idx_vacancy_categories_slug` (`slug`);

--
-- Индексы таблицы `vacancy_selection_stages`
--
ALTER TABLE `vacancy_selection_stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vacancy_id` (`vacancy_id`);

--
-- Индексы таблицы `vacancy_skills`
--
ALTER TABLE `vacancy_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `vacancy_skill_pivot`
--
ALTER TABLE `vacancy_skill_pivot`
  ADD PRIMARY KEY (`vacancy_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Индексы таблицы `vacancy_tags`
--
ALTER TABLE `vacancy_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `vacancy_tag_pivot`
--
ALTER TABLE `vacancy_tag_pivot`
  ADD PRIMARY KEY (`vacancy_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `delivery_methods`
--
ALTER TABLE `delivery_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `news_attachments`
--
ALTER TABLE `news_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `news_authors`
--
ALTER TABLE `news_authors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `news_categories`
--
ALTER TABLE `news_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `news_comments`
--
ALTER TABLE `news_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `news_gallery`
--
ALTER TABLE `news_gallery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `news_subscribers`
--
ALTER TABLE `news_subscribers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `news_tags`
--
ALTER TABLE `news_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT для таблицы `order_coupons`
--
ALTER TABLE `order_coupons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT для таблицы `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT для таблицы `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT для таблицы `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT для таблицы `product_questions`
--
ALTER TABLE `product_questions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT для таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `store_images`
--
ALTER TABLE `store_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `store_reviews`
--
ALTER TABLE `store_reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `store_staff`
--
ALTER TABLE `store_staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `training_requests`
--
ALTER TABLE `training_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT для таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `vacancies`
--
ALTER TABLE `vacancies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `vacancy_applications`
--
ALTER TABLE `vacancy_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `vacancy_application_history`
--
ALTER TABLE `vacancy_application_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `vacancy_categories`
--
ALTER TABLE `vacancy_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `vacancy_selection_stages`
--
ALTER TABLE `vacancy_selection_stages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `vacancy_skills`
--
ALTER TABLE `vacancy_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `vacancy_tags`
--
ALTER TABLE `vacancy_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`),
  ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `news_authors` (`id`),
  ADD CONSTRAINT `news_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `news_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `news_attachments`
--
ALTER TABLE `news_attachments`
  ADD CONSTRAINT `news_attachments_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `news_comments`
--
ALTER TABLE `news_comments`
  ADD CONSTRAINT `news_comments_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `news_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `news_comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `news_comments` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `news_gallery`
--
ALTER TABLE `news_gallery`
  ADD CONSTRAINT `news_gallery_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `news_news_tags`
--
ALTER TABLE `news_news_tags`
  ADD CONSTRAINT `news_news_tags_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `news_news_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `news_tags` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `news_related`
--
ALTER TABLE `news_related`
  ADD CONSTRAINT `news_related_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `news_related_ibfk_2` FOREIGN KEY (`related_news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `order_statuses` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`delivery_method_id`) REFERENCES `delivery_methods` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_coupons`
--
ALTER TABLE `order_coupons`
  ADD CONSTRAINT `order_coupons_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_coupons_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_status_history_ibfk_2` FOREIGN KEY (`old_status_id`) REFERENCES `order_statuses` (`id`),
  ADD CONSTRAINT `order_status_history_ibfk_3` FOREIGN KEY (`new_status_id`) REFERENCES `order_statuses` (`id`),
  ADD CONSTRAINT `order_status_history_ibfk_4` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `product_questions`
--
ALTER TABLE `product_questions`
  ADD CONSTRAINT `product_questions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_questions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_questions_ibfk_3` FOREIGN KEY (`answered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `stores_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `store_images`
--
ALTER TABLE `store_images`
  ADD CONSTRAINT `store_images_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `store_reviews`
--
ALTER TABLE `store_reviews`
  ADD CONSTRAINT `store_reviews_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `store_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `store_staff`
--
ALTER TABLE `store_staff`
  ADD CONSTRAINT `store_staff_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `vacancies`
--
ALTER TABLE `vacancies`
  ADD CONSTRAINT `vacancies_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `vacancy_categories` (`id`),
  ADD CONSTRAINT `vacancies_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vacancies_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `vacancy_applications`
--
ALTER TABLE `vacancy_applications`
  ADD CONSTRAINT `vacancy_applications_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacancy_applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `vacancy_application_history`
--
ALTER TABLE `vacancy_application_history`
  ADD CONSTRAINT `vacancy_application_history_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `vacancy_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacancy_application_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `vacancy_categories`
--
ALTER TABLE `vacancy_categories`
  ADD CONSTRAINT `vacancy_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `vacancy_categories` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `vacancy_selection_stages`
--
ALTER TABLE `vacancy_selection_stages`
  ADD CONSTRAINT `vacancy_selection_stages_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `vacancy_skill_pivot`
--
ALTER TABLE `vacancy_skill_pivot`
  ADD CONSTRAINT `vacancy_skill_pivot_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacancy_skill_pivot_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `vacancy_skills` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `vacancy_tag_pivot`
--
ALTER TABLE `vacancy_tag_pivot`
  ADD CONSTRAINT `vacancy_tag_pivot_ibfk_1` FOREIGN KEY (`vacancy_id`) REFERENCES `vacancies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacancy_tag_pivot_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `vacancy_tags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
