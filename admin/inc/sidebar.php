   <?php
$admin = getAdminData(); 
?>
   <!-- Сайдбар -->
    <aside class="sidebar">
        <div class="logo">
            <h1>FlaxTap</h1>
            <p>Админ-панель</p>
        </div>
        
        <div class="admin-info">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
            </div>
            <div class="admin-name"><?php echo htmlspecialchars($admin['name']); ?></div>
            <div class="admin-email"><?php echo htmlspecialchars($admin['email']); ?></div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="/admin/index.php">
                    <i class="fas fa-home"></i>
                    <span>Главная</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/profile.php">
                    <i class="fas fa-user"></i>
                    <span>Профиль</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/users/">
                    <i class="fas fa-users"></i>
                    <span>Пользователи</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Заказы</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/vacancies/">
                    <i class="fas fa-briefcase"></i>
                    <span>Вакансии</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/news/">
                    <i class="fas fa-newspaper"></i>
                    <span>Новости</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/products/">
                    <i class="fas fa-box"></i>
                    <span>Товары</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/stores/">
                    <i class="fas fa-store"></i>
                    <span>Магазины</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/study/">
                    <i class="fas fa-users"></i>
                    <span>Обучение</span>
                </a>
            </li>
        </ul>
        
        <div class="logout">
            <a href="/admin/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Выйти</span>
            </a>
        </div>
    </aside>