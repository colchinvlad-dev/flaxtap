<div align="center">

# FlaxTap Admin

**Административная панель для управления данными интернет-магазина косметики «Flax Tap»**

Дипломный проект по специальности **09.02.07 «Информационные системы и программирование»**

[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=for-the-badge&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![License](https://img.shields.io/badge/license-MIT-34d399?style=for-the-badge)](#лицензия)

[Возможности](#возможности) · [Стек](#технологический-стек) · [Структура](#структура-проекта) · [Установка](#установка-и-запуск)

</div>

---

## О проекте

**FlaxTap Admin** — это комплекс административных интерфейсов для централизованного управления данными веб-приложения «Магазин косметики Flax Tap».

Система автоматизирует ключевые бизнес-процессы компании:

- Управление товарным каталогом с иерархической структурой категорий
- Обработка и сопровождение клиентских заказов
- Ведение пользователей и разграничение прав доступа
- Публикация контента: новости, вакансии, информация о магазинах
- Учёт обучающихся по образовательным программам компании
- Анализ ключевых показателей бизнеса (дашборд с KPI)

**Цель проекта** — заменить разрозненные инструменты управления (Excel-таблицы, заметки в CRM, прямой доступ к БД) единой централизованной системой с интуитивным веб-интерфейсом.

### Ключевые результаты

| Метрика | Значение |
|---------|----------|
| Таблиц в базе данных | **44** |
| Модулей админ-панели | **9** |
| Файлов в проекте | **46** |
| Сокращение времени на добавление товара | с **15–20 мин** до **3–5 мин** |
| Полный цикл CRUD | для всех сущностей |

---

## Возможности

### Модули системы

- **Dashboard** — агрегированная статистика: пользователи, заказы, товары, новости, вакансии, магазины, график активности
- **Профиль** — управление учётной записью, смена пароля, настройка уведомлений
- **Пользователи** — CRUD с фильтрацией по роли, мягкое удаление, сброс пароля
- **Заказы** — центральный раздел: фильтрация по статусу/дате/сумме, поиск, изменение статусов, трек-номера, комментарии менеджера
- **Товары** — управление каталогом с иерархией категорий, характеристиками, изображениями, SEO-полями
- **Новости** — публикация с категориями, тегами, авторами, планированием даты выхода
- **Вакансии** — публикация позиций и обработка откликов кандидатов
- **Магазины** — справочник точек продаж с гео-координатами и часами работы
- **Обучение** — уникальный модуль проекта: заявки, обучающиеся, группы, расписание

### Специализированные скрипты

- **Экспорт заказов в Excel** (`orders/export.php`) — выгрузка с учётом фильтров
- **Генерация счетов** (`orders/invoice.php`) — печатная форма счёта на оплату
- **AJAX-обработка вопросов о товаре** (`products/get_questions.php`) — асинхронная загрузка в модальном окне
- **REST-like API для заявок** (`study/training_request_actions.php`) — управление статусами через JSON
- **Диагностика аутентификации** — проверка корректности хеширования паролей

### Безопасность

- **Аутентификация** на сессиях PHP с хешированием паролей (bcrypt)
- **Ролевая модель доступа** (RBAC) — `admin` / `user`, проверка прав при каждом защищённом запросе
- **Защита от SQL-инъекций** — подготовленные выражения и экранирование ввода
- **Защита от XSS** — экранирование вывода через `htmlspecialchars()`
- **Транзакционная обработка** заказов с логированием изменений статусов
- **Аудит действий** — файлы `admin_log.txt`, `login_attempts.txt`, `training_log.txt`

---

## Технологический стек

**Backend**

- **PHP 8.5** — серверная логика, работа с сессиями, PDO/mysqli
- **MySQL 8.4** — база данных с поддержкой ACID-транзакций, внешних ключей и индексов
- **Apache HTTP Server** — обработка HTTP-запросов, поддержка `.htaccess`

**Frontend**

- **HTML5** — семантическая разметка
- **CSS3** — Flexbox, Grid, адаптивная вёрстка
- **JavaScript** — интерактивные элементы, AJAX-запросы
- **Font Awesome 6.4.0** — библиотека векторных иконок

**Среда разработки**

- **Open Server Panel 6.5.0** — локальная разработка (Apache + PHP + MySQL в одной сборке)

---

## Структура проекта

```
flaxtap-admin/
├── admin/
│   ├── index.php                    # Dashboard — главная страница
│   ├── login.php                    # Авторизация
│   ├── logout.php                   # Выход из системы
│   ├── profile.php                  # Профиль администратора
│   ├── admin_log.txt                # Лог действий администраторов
│   ├── login_attempts.txt           # Лог попыток входа
│   ├── training_log.txt             # Лог операций с заявками
│   │
│   ├── assets/                      # Статические ресурсы
│   │   ├── style/                   # CSS-стили
│   │   └── js/                      # JavaScript-скрипты
│   │
│   ├── config/
│   │   └── database.php             # Подключение к БД, общие функции
│   │
│   ├── inc/                         # Переиспользуемые компоненты
│   │   ├── header.php               # Шапка (общая для всех страниц)
│   │   └── sidebar.php              # Боковое меню
│   │
│   ├── news/                        # Модуль новостей (CRUD)
│   ├── orders/                      # Модуль заказов (CRUD + экспорт + счета)
│   ├── products/                    # Модуль товаров (CRUD + AJAX)
│   ├── stores/                      # Модуль магазинов (CRUD)
│   ├── study/                       # Модуль обучения (CRUD + API)
│   ├── users/                       # Модуль пользователей (CRUD + смена пароля)
│   └── vacancies/                   # Модуль вакансий (CRUD + отклики)
│
└── db_flaxtap.sql                   # Дамп базы данных (44 таблицы)
```

Каждый модуль содержит стандартный набор файлов CRUD:

- `index.php` — список записей с фильтрацией и поиском
- `create.php` — форма создания
- `edit.php` — форма редактирования
- `view.php` — детальный просмотр
- `delete.php` — удаление (или мягкое удаление)

---

## База данных

**СУБД:** MySQL 8.4, движок **InnoDB**, кодировка **utf8mb4**.

**Общее количество таблиц:** 44, сгруппированных по функциональным модулям.

### Группы таблиц

| Группа | Таблицы |
|--------|---------|
| **Товары** | `categories`, `products`, `product_images`, `product_attributes`, `product_questions`, `product_reviews` |
| **Заказы и оплата** | `orders`, `order_items`, `order_statuses`, `order_status_history`, `payment_methods`, `delivery_methods`, `coupons`, `order_coupons` |
| **Пользователи и доступ** | `users`, `user_addresses`, `user_favorites`, `user_settings`, `cart` |
| **Обучение** | `training_requests` |
| **Новости** | `news`, `news_categories`, `news_authors`, `news_tags`, `news_news_tags`, `news_comments`, `news_gallery`, `news_attachments`, `news_related`, `news_subscribers` |
| **Вакансии** | `vacancies`, `vacancy_categories`, `vacancy_applications`, `vacancy_application_history`, `vacancy_skills`, `vacancy_skill_pivot`, `vacancy_tags`, `vacancy_tag_pivot` |
| **Магазины** | `stores`, `store_images`, `store_reviews`, `store_staff` |
| **Служебные** | `contact_messages` |

### Ключевые особенности БД

- **Нормализация до 3НФ** — исключена избыточность данных
- **Внешние ключи (FOREIGN KEY)** — ссылочная целостность
- **Каскадные операции** — `ON DELETE CASCADE`, `ON DELETE SET NULL`, `ON DELETE RESTRICT` в зависимости от бизнес-логики
- **Транзакции ACID** — для оформления заказов и обновления остатков
- **Индексы** — для внешних ключей, статусов, дат, цен, рейтингов, slug-полей
- **Уникальные ограничения** — на `slug`, `email`, `order_number`, составные ключи

### Иерархические структуры

- **Категории товаров** — через `parent_id` (self-reference), неограниченная вложенность
- **Комментарии к новостям** — древовидная структура через `parent_id`
- **Категории вакансий** — иерархическая структура через `parent_id`

### ER-диаграмма

Схема базы данных из **44 таблиц** с обозначенными связями «один-ко-многим» и «многие-ко-многим» (сводные таблицы `news_news_tags`, `news_related`, `vacancy_skill_pivot`, `vacancy_tag_pivot`) приведена в документации дипломного проекта.

---

## Установка и запуск

### Требования

- **PHP 8.5** или выше
- **MySQL 8.4** или выше
- **Apache 2.4** с модулем `mod_rewrite`
- **Open Server Panel** (рекомендуется) или XAMPP / MAMP / встроенный сервер PHP

### Шаги установки

**1. Клонировать репозиторий**

```bash
git clone https://github.com/colchinvlad-dev/flaxtap-admin.git
cd flaxtap-admin
```

**2. Развернуть базу данных**

Открой phpMyAdmin (или MySQL Workbench) и импортируй дамп:

```sql
CREATE DATABASE db_flaxtap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_flaxtap;
SOURCE db_flaxtap.sql;
```

**3. Настроить подключение к БД**

Открой `admin/config/database.php` и укажи параметры соединения:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_flaxtap');
define('DB_USER', 'root');
define('DB_PASS', '');
```

**4. Поместить проект в веб-директорию**

- **Open Server Panel**: `OSPanel/domains/flaxtap.local/admin/`
- **XAMPP**: `htdocs/flaxtap-admin/admin/`
- **Встроенный сервер PHP**:

```bash
php -S localhost:8000 -t admin/
```

**5. Открыть в браузере**

```
http://localhost/admin/login.php
```

### Учётные данные по умолчанию

Для входа в админ-панель используй данные администратора из таблицы `users`. Если дамп содержит тестового пользователя:

| Поле | Значение |
|------|----------|
| Email | `admin@example.com` |
| Пароль | `admin123` |

⚠️ **Обязательно смени пароль** после первого входа.

---

## Безопасность

### Аутентификация

- Пароли хранятся **только в виде хеша** (bcrypt)
- Сессии имеют ограниченное время жизни
- Логирование попыток входа в `login_attempts.txt`

### Авторизация

- **Ролевая модель (RBAC)**: `admin` (полный доступ) / `user` (только личный кабинет)
- Проверка прав на сервере при каждом защищённом запросе
- Скрытие элементов интерфейса **не является** мерой защиты

### Защита данных

- **SQL-инъекции** — подготовленные выражения + функция `clean_input()`
- **XSS-атаки** — экранирование вывода через `htmlspecialchars()`
- **CSRF** — токены для форм, изменяющих состояние
- **Транзакции** — атомарность взаимосвязанных операций

### Аудит

Логирование действий администраторов:

- `admin_log.txt` — CRUD-операции
- `login_attempts.txt` — попытки входа
- `training_log.txt` — операции с заявками на обучение
- `order_status_history` — история статусов заказов в БД

---

## Что можно улучшить

Идеи для дальнейшего развития проекта:

- [ ] **Интеграция с платёжными шлюзами** (ЮKassa, Тинькофф) для автоматизации онлайн-оплат
- [ ] **Система уведомлений** — email, SMS, Telegram-бот для оповещения о новых заказах и заявках
- [ ] **REST API** для интеграции с мобильным приложением
- [ ] **Двухфакторная аутентификация** (TOTP/SMS) для администраторов
- [ ] **Кеширование** через Redis — ускорение дашборда и отчётов
- [ ] **Виртуальный скроллинг** для каталога с тысячами позиций
- [ ] **Миграции БД** — отказ от SQL-дампов в пользу версионирования схемы
- [ ] **Тесты** — PHPUnit для критических сценариев (создание заказа, аутентификация)
- [ ] **CI/CD** — GitHub Actions для линтинга и автотестов
- [ ] **Тёмная тема** — через CSS-переменные и `prefers-color-scheme`

---

## Лицензия

MIT License — используй, форкай, меняй под себя.

```
Copyright (c) 2026 Vladislav Kolchin (dottore)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

<div align="center">

**Дипломный проект**

ГПОУ «Печорский промышленно-экономический техникум»
Специальность 09.02.07 «Информационные системы и программирование»

**Автор:** Колчин Владислав Сергеевич
**Год защиты:** 2026

[Telegram](https://t.me/vladkolchin00) · [Email](mailto:colchin.vl4d@yandex.ru) · [GitHub](https://github.com/colchinvlad-dev)

</div>
