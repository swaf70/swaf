<?php if (!isset($active_page)) { $active_page = ''; }
// Determine the active link from the current file name (reliable even if $active_page is set later)
$current_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav_active = static function (string $file) use ($current_file) {
    return ($current_file === $file) ? 'active' : '';
};
?>
<!-- Admin Horizontal Navbar -->
<nav class="admin-navbar">
    <div class="container d-flex flex-wrap align-items-center gap-3">
        <span class="navbar-brand mb-0">
            <i class="fas fa-user-shield me-2"></i>
            لوحة التحكم
        </span>
        <ul class="admin-nav ms-auto mb-0 list-unstyled d-flex flex-wrap align-items-center gap-1">
            <li>
                <a class="admin-nav-link <?= $nav_active('index.php') ?>" href="index.php">
                    <i class="fas fa-tachometer-alt"></i>لوحة التحكم
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= $nav_active('projects.php') ?>" href="projects.php">
                    <i class="fas fa-folder"></i>إدارة المشاريع
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= $nav_active('services.php') ?>" href="services.php">
                    <i class="fas fa-box"></i>الخدمات
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= $nav_active('messages.php') ?>" href="messages.php">
                    <i class="fas fa-envelope"></i>الرسائل
                </a>
            </li>
            <li>
                <a class="admin-nav-link <?= $nav_active('profile.php') ?>" href="profile.php">
                    <i class="fas fa-user"></i>الملف الشخصي
                </a>
            </li>
        </ul>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill user-chip">
                <i class="fas fa-user me-1"></i>
                <?= h($_SESSION['username'] ?? 'Admin') ?>
            </span>
            <a class="btn-custom btn-danger-custom" href="../logout.php">
                <i class="fas fa-sign-out-alt me-1"></i>تسجيل الخروج
            </a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="p-3 p-md-4 pt-3">