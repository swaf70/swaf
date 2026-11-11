<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title ?? 'سكيور تيك (SecureTech) - حلول أمن المعلومات وتقنية الشبكات') ?></title>
    
    <link rel="icon" type="image/svg+xml" href="<?= site_url('assets/images/securetech-logo.svg') ?>">
    
    <!-- Bootstrap 5 RTL CSS (local) -->
    <link href="<?= site_url('assets/css/bootstrap.rtl.min.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome for icons (local) -->
    <link rel="stylesheet" href="<?= site_url('assets/css/all.min.css') ?>">
    
    <!-- Tajawal Font (RTL supported, local) -->
    <link href="<?= site_url('assets/css/tajawal.css') ?>" rel="stylesheet">
    
    <!-- Custom Light Luxurious Theme -->
    <link href="<?= site_url('assets/css/site-theme.css') ?>" rel="stylesheet">
</head>
<body>
    
    <?php if (!($hide_nav ?? false)): ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url('index.php') ?>">
                <img src="<?= site_url('assets/images/securetech-logo.svg') ?>" alt="<?= h('شعار سكيور تيك') ?>" class="navbar-logo">
                <span class="brand-text"><?= h('سكيور تيك (SecureTech)') ?></span>
                <span class="fw-light text-primary-cyan brand-tag">| حلول أمن المعلومات وتقنية الشبكات</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link<?php echo (($active_page ?? '') === 'home') ? ' active' : ''; ?>" href="<?= site_url('index.php') ?>">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo (($active_page ?? '') === 'works') ? ' active' : ''; ?>" href="<?= site_url('works.php') ?>">أعمالي</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo (($active_page ?? '') === 'services') ? ' active' : ''; ?>" href="<?= site_url('services.php') ?>">خدماتي</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo (($active_page ?? '') === 'contact') ? ' active' : ''; ?>" href="<?= site_url('contact.php') ?>">تواصل</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-shield me-2"></i><?= h($_SESSION['username'] ?? 'Admin') ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="<?= site_url('profile.php') ?>"><i class="fas fa-user-cog me-2"></i> الملف الشخصي</a>
                                <?php if (is_admin()): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?= site_url('admin/index.php') ?>"><i class="fas fa-tachometer-alt me-2"></i> لوحة التحكم</a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= site_url('logout.php') ?>"><i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('login.php') ?>"><i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="container my-5">
        <div class="row">
            <div class="col-12">