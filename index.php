<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Require login to access the site
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Set page title
$page_title = 'سكيور تيك (SecureTech) - حلول أمن المعلومات وتقنية الشبكات';

// Set active page for header
$active_page = 'home';
?>
<?php require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'home'; ?>
    
    <!-- Hero Section -->
    <section id="hero" class="hero-section py-5 py-lg-8">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-last">
                    <div class="hero-content">
                        <span class="hero-badge small fw-bold mb-3">شبكة أمان</span>
                        <h1 class="display-4 fw-bold mt-4">
                            <?= h('نوفّر حلول أمن المعلومات وتقنية الشبكات بمعايير عالمية') ?>
                        </h1>
                        <p class="lead text-secondary mb-4">
                            <?= h('سكيور تيك (SecureTech) — خبرة في الأمن السيبراني، هندسة الشبكات، وتطوير تطبيقات ويب آمنة لحماية وتعزيز وجودك الرقمي') ?>
                        </p>
                        <div class="d-grid gap-2">
                            <a href="works.php" class="cta-button">
                                <i class="fas fa-grid me-2"></i>استعراض الأعمال
                            </a>
                            <a href="services.php" class="cta-button btn-outline-primary-cyan">
                                <i class="fas fa-hand-holding-usd me-2"></i>طلب خدمة
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-profile text-center">
                        <div class="profile-card hero-logo-card">
                            <img src="<?= site_url('assets/images/securetech-logo.svg') ?>"
                                 alt="<?= h('شعار سكيور تيك SecureTech') ?>"
                                 class="img-fluid hero-logo-img">
                            <div class="profile-info mt-4">
                                <h5 class="fw-bold"><?= h('سكيور تيك (SecureTech)') ?></h5>
                                <p class="text-secondary small"><?= h('درعٌ يحمي شبكاتك وحلول أمن المعلومات وتقنية الشبكات') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Dynamic Statistics Bar -->
    <section class="statistics-section py-5 bg-secondary-slate">
        <div class="container">
            <div class="row g-4">
                <!-- Years of Experience -->
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-number"><?= h(date('Y') - 2018) ?></div>
                        <div class="small text-secondary">سنوات خبرة</div>
                    </div>
                </div>
                <!-- Completed Projects -->
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-number"><?= h(12) ?></div>
                        <div class="small text-secondary">مشاريع منجزة</div>
                    </div>
                </div>
                <!-- Security Certifications -->
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-number"><?= h(5) ?></div>
                        <div class="small text-secondary">شهادات أمنية</div>
                    </div>
                </div>
                <!-- Clients Served -->
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center">
                        <div class="stat-number"><?= h(45) ?></div>
                        <div class="small text-secondary">عملاء راضون</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Explore Gateway -->
    <section class="explore-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title fw-bold mb-3">
                    <?= h('استكشف الحلول') ?>
                </h2>
                <p class="text-secondary mb-0">
                    <?= h('اكتشف مشاريعنا وخدماتنا المخصصة لأهدافك') ?>
                </p>
            </div>
            
            <div class="row g-4">
                <!-- Web Development Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="explore-card h-100 project-card">
                        <div class="project-badge">Web Development</div>
                        <h3 class="card-title h5 mb-3">تطوير تطبيقات الويب</h3>
                        <p class="card-text text-secondary small">
                            <?= h('تطبيقات ويب آمنة وقابلة للتطوير باستخدام PHP, MySQL, وتقنيات حديثة') ?>
                        </p>
                        <a href="services.php" class="stretched-link">
                            <i class="fas fa-arrow-right text-primary-cyan small"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Network Architecture Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="explore-card h-100 project-card">
                        <div class="project-badge">Network Architecture</div>
                        <h3 class="card-title h5 mb-3">هندسة شبكات</h3>
                        <p class="card-text text-secondary small">
                            <?= h('تصميم وتنفيذ شبكات آمنة للبنية التحتية للشركات') ?>
                        </p>
                        <a href="services.php" class="stretched-link">
                            <i class="fas fa-arrow-right text-primary-cyan small"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Cybersecurity Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="explore-card h-100 project-card">
                        <div class="project-badge">Cybersecurity</div>
                        <h3 class="card-title h5 mb-3">الأمان السيبراني</h3>
                        <p class="card-text text-secondary small">
                            <?= h('تقييم الثغرات وحماية الأنظمة من الاختراقات') ?>
                        </p>
                        <a href="services.php" class="stretched-link">
                            <i class="fas fa-arrow-right text-primary-cyan small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Projects Showcase -->
    <section class="projects-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title fw-bold mb-3">
                    <?= h('أعمالي المميزة') ?>
                </h2>
                <p class="text-secondary mb-0">
                    <?= h('مشاريع حديثة تم تطويرها بخبرة أمنية') ?>
                </p>
            </div>
            
            <div class="row g-4 portfolio-grid">
                <!-- Project cards will be rendered by PHP -->
                <?php
                // Fetch projects from database using prepared statement
                $stmt = $pdo->prepare("SELECT id, title, category, description, tech_stack, image_url, project_url FROM projects ORDER BY created_at DESC LIMIT 6");
                $stmt->execute();
                $projects = $stmt->fetchAll();
                
                if (empty($projects)) {
                    echo '<div class="col-12 text-center py-5">';
                    echo '<p class="text-secondary">لا توجد مشاريع معروضة حالياً.</p>';
                    echo '</div>';
                } else {
                    foreach ($projects as $project) {
                        $tech_stacks = $project['tech_stack'] ? explode(',', $project['tech_stack']) : [];
                        $tech_badges = '';
                        foreach ($tech_stacks as $tech) {
                            $tech = trim($tech);
                            $tech_badges .= '<span class="project-badge me-1">' . h($tech) . '</span>';
                        }
                        
                        $category_name = match($project['category']) {
                            'Web Development' => 'تطوير ويب',
                            'Network Architecture' => 'هندسة شبكات',
                            'Cybersecurity' => 'أمن سيبراني',
                            'Full-Stack' => 'Full-Stack',
                            default => h($project['category'])
                        };
                        
                        echo '<div class="col-md-6 col-lg-4">';
                        echo '<div class="project-card h-100">';
                        echo '<img src="uploads/projects/' . h($project['image_url'] ?? 'default.jpg') . '" 
                              alt="' . h($project['title'] . ' مشروع') . '" 
                              class="project-img img-fluid">';
                        echo '<div class="project-body p-3">';
                        echo '<h4 class="project-title mb-2">' . h($project['title']) . '</h4>';
                        echo '<p class="project-category text-secondary small mb-2">' . h($category_name) . '</p>';
                        echo '<div class="tech-tags mb-3">' . $tech_badges . '</div>';
                        echo '<p class="project-desc text-secondary small line-clamp-2">' . h(truncate($project['description'], 120)) . '</p>';
                        echo '<a href="' . h($project['project_url'] ?? '#') . '" class="stretched-link">';
                        echo '<i class="fas fa-arrow-up-right-small text-primary-cyan"></i>';
                        echo '</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>
<?php require_once __DIR__ . '/includes/footer.php' ?>