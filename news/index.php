<?php
ob_start(); // Включаем буферизацию вывода
include '../config/database.php';
// Функция для безопасного вывода
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Получаем все опубликованные новости
$limit = 6; // Лимит новостей на странице
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Получаем общее количество новостей для пагинации
$totalQuery = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
$totalResult = mysqli_query($connection, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalNews = $totalRow['total'];
$totalPages = ceil($totalNews / $limit);

// Получаем новости с информацией об авторах и категориях
$query = "
    SELECT 
        n.*, 
        a.name as author_name, 
        a.avatar_url as author_avatar,
        nc.name as category_name,
        nc.color as category_color
    FROM news n
    LEFT JOIN news_authors a ON n.author_id = a.id
    LEFT JOIN news_categories nc ON n.category_id = nc.id
    WHERE n.status = 'published'
    ORDER BY n.published_at DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($connection, $query);

// Проверяем, есть ли новости
$news = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $news[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новости - FlaxTap</title>
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/news.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "../inc/header.php"; ?>

    <!-- Герой-секция новостей -->
    <section class="news-hero">
        <div class="container">
            <div class="news-hero-content">
                <h1 class="news-hero-title">Новости и статьи</h1>
                <p class="news-hero-subtitle">Самые свежие новости из мира профессиональной косметологии, инновации, исследования и полезные советы от экспертов FlaxTap</p>
                
                <div class="news-search">
                    <form class="search-form" method="GET" action="search.php">
                        <input type="text" name="q" placeholder="Поиск по новостям...">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Найти
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Сетка новостей -->
    <section class="news-grid-section">
        <div class="container">
            <div class="news-filters">
                <button class="filter-btn active">Все новости</button>
                <!-- Фильтры по категориям можно добавить динамически -->
                <?php
                // Получаем все категории новостей
                $catQuery = "SELECT * FROM news_categories WHERE is_active = 1 ORDER BY sort_order";
                $catResult = mysqli_query($connection, $catQuery);
                if ($catResult) {
                    while ($cat = mysqli_fetch_assoc($catResult)) {
                        echo '<button class="filter-btn" data-category="' . $cat['id'] . '">' . escape($cat['name']) . '</button>';
                    }
                }
                ?>
            </div>
            
            <div class="news-grid">
                <?php if (empty($news)): ?>
                    <div class="no-news">
                        <p>Новостей пока нет</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($news as $item): ?>
                        <article class="news-card">
                            <div class="news-image">
                                <?php if (!empty($item['thumbnail_url'])): ?>
                                    <img src="<?php echo escape($item['thumbnail_url']); ?>" alt="<?php echo escape($item['title']); ?>">
                                <?php else: ?>
                                    <img src="../assets/media/news/default.jpg" alt="<?php echo escape($item['title']); ?>">
                                <?php endif; ?>
                                
                                <?php if (!empty($item['category_name'])): ?>
                                    <span class="news-category" style="background-color: <?php echo escape($item['category_color'] ?? '#3498db'); ?>">
                                        <?php echo escape($item['category_name']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($item['published_at'])): ?>
                                    <span class="news-date">
                                        <?php echo date('d.m.Y', strtotime($item['published_at'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="news-content">
                                <h3 class="news-title"><?php echo escape($item['title']); ?></h3>
                                
                                <?php if (!empty($item['excerpt'])): ?>
                                    <p class="news-excerpt"><?php echo escape($item['excerpt']); ?></p>
                                <?php endif; ?>
                                
                                <div class="news-meta">
                                    <div class="news-author">
                                        <div class="author-avatar">
                                            <?php if (!empty($item['author_avatar'])): ?>
                                                <img src="<?php echo escape($item['author_avatar']); ?>" alt="<?php echo escape($item['author_name']); ?>">
                                            <?php else: ?>
                                                <div class="author-avatar-default">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="author-name"><?php echo escape($item['author_name'] ?? 'Автор'); ?></span>
                                    </div>
                                    <a href="single.php?slug=<?php echo escape($item['slug']); ?>" class="read-more">
                                        Читать <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Пагинация -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= min(5, $totalPages); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="pagination-btn active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="pagination-btn"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($totalPages > 5): ?>
                        <span class="pagination-dots">...</span>
                        <a href="?page=<?php echo $totalPages; ?>" class="pagination-btn"><?php echo $totalPages; ?></a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Секция рассылки -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-container">
                <div class="newsletter-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h2 class="newsletter-title">Подпишитесь на наши новости</h2>
                <p class="newsletter-subtitle">Получайте первыми информацию о новинках, акциях и полезные материалы от экспертов FlaxTap</p>
                
                <form class="newsletter-form" method="POST" action="../actions/subscribe_news.php">
                    <input type="email" name="email" placeholder="Ваш email" required style="background-color: white;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Подписаться
                    </button>
                </form>
                
                <p class="privacy-note">Нажимая кнопку, вы соглашаетесь с <a href="/policy.php">политикой конфиденциальности</a></p>
            </div>
        </div>
    </section>

    <?php include "../inc/modal.php"; ?>
    <?php include "../inc/footer.php"; ?>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/news.js"></script>
    <script src="../assets/js/modals.js"></script>
    <script>
    // Фильтрация новостей по категориям
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.category;
            
            // Удаляем active у всех кнопок
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            // Добавляем active текущей кнопке
            this.classList.add('active');
            
            if (categoryId) {
                // Загрузка новостей по категории через AJAX
                fetch(`../actions/filter_news.php?category=${categoryId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.news-grid').innerHTML = html;
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                // Показать все новости
                window.location.reload();
            }
        });
    });
    </script>
</body>
</html>