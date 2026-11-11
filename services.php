<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Require login to access the site
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page_title = 'خدماتي وأسعار الخدمات';
$active_page = 'services';
?>
<?php require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'services'; ?>
    
    <!-- Services Section -->
    <section class="services-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title fw-bold mb-3">
                    <?= h('خدماتي الاحترافية') ?>
                </h2>
<p class="text-secondary mb-0">
                        <?= h('خدمات أمن المعلومات والتقنية مصممة لحماية وتعزيز أعمال عملائنا') ?>
                    </p>
            </div>
            
            <div class="row g-4">
                <?php
                // Fetch services from database using prepared statement
                $stmt = $pdo->prepare("SELECT id, service_name, description, price, status FROM services ORDER BY 
                    CASE WHEN status = 'Available' THEN 1 WHEN status = 'Busy' THEN 2 ELSE 3 END, created_at");
                $stmt->execute();
                $services = $stmt->fetchAll();
                
                if (empty($services)) {
                    echo '<div class="col-12 text-center py-5">';
                    echo '<p class="text-secondary">لا توجد خدمات متاحة حالياً.</p>';
                    echo '</div>';
                } else {
                    foreach ($services as $service) {
                        $statusClass = match($service['status']) {
                            'Available' => 'btn-success',
                            'Busy' => 'btn-warning',
                            'Completed' => 'btn-secondary',
                            default => 'btn-outline-secondary'
                        };
                        $statusText = match($service['status']) {
                            'Available' => 'متاح',
                            'Busy' => 'مشغول',
                            'Completed' => 'مكتملة',
                            default => h($service['status'])
                        };
                        $statusColor = match($service['status']) {
                            'Available' => 'var(--success-green)',
                            'Busy' => 'var(--accent-cyan)',
                            'Completed' => 'var(--text-secondary)',
                            default => 'var(--primary-navy)'
                        };
                        ?>
                        <div class="col-md-6 col-lg-4 service-card">
                            <div class="service-icon mb-3">
                                <i class="fas fa-tools text-primary-cyan fs-1"></i>
                            </div>
                            <h3 class="card-title h5 mb-3"><?= h($service['service_name']) ?></h3>
                            <p class="card-text text-secondary small line-clamp-3">
                                <?= h($service['description']) ?>
                            </p>
                            
                            <div class="price-info mt-3">
                                <span class="price-currency text-primary-cyan">
                                    <?= number_format($service['price'], 0, '', ',') ?> YER
                                </span>
                                <span class="price-period text-secondary small"> / خدمة</span>
                            </div>
                            
                            <div class="status-badge mt-2">
                                <span class="badge <?= h($statusClass) ?>"><?= h($statusText) ?></span>
                            </div>
                            
                            <a href="javascript:void(0)" class="order-btn w-100 mt-3 btn-custom">
                                <i class="fas fa-hand-holding-usd me-2"></i>طلب الخدمة
                            </a>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
    
    <!-- Pricing Plans Section -->
    <section class="pricing-section py-5 bg-secondary-slate">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title fw-bold mb-3">
                    <?= h('خطط الأسعار') ?>
                </h2>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="pricing-card h-100">
                        <div class="pricing-header">
                            <h3 class="pricing-title">اختبار الاختراق</h3>
                            <span class="pricing-price">
                                <?= number_format(50000, 0, '', ',') ?> YER
                            </span>
                            <span class="pricing-period text-secondary small"> / تقييم شامل</span>
                        </div>
                        <ul class="pricing-features list-unstyled mb-0">
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>ثغرات التفتيش الشاملة</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>تقرير ثغرات مفصل</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>اختبار pénétration</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>اقتراحات التصحيح</li>
                        </ul>
                        <a href="javascript:void(0)" class="btn-custom w-100 mt-3">
                            <i class="fas fa-hand-holding-usd me-2"></i> احجز الخدمة
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="pricing-card h-100 bg-secondary-slate border-primary-cyan">
                        <div class="pricing-header">
                            <h3 class="pricing-title">إعداد البنية التحتية</h3>
                            <span class="pricing-price">
                                <?= number_format(35000, 0, '', ',') ?> YER
                            </span>
                            <span class="pricing-period text-secondary small"> / تثبيتnetwork</span>
                        </div>
                        <ul class="pricing-features list-unstyled mb-0">
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>تصميم الشبكة</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>التكوين الآمن</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>تركيب الأجهزة</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>اختبار الاتصال</li>
                        </ul>
                        <a href="javascript:void(0)" class="btn-custom w-100 mt-3">
                            <i class="fas fa-hand-holding-usd me-2"></i> احجز الخدمة
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="pricing-card h-100">
                        <div class="pricing-header">
                            <h3 class="pricing-title">التطوير الكامل</h3>
                            <span class="pricing-price">
                                <?= number_format(45000, 0, '', ',') ?> YER
                            </span>
                            <span class="pricing-period text-secondary small"> / تطبيق كامل</span>
                        </div>
                        <ul class="pricing-features list-unstyled mb-0">
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>تصميم الواجهة</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>تطوير الخلفية</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>قاعدة بيانات</li>
                            <li class="text-secondary small"><i class="fas fa-check text-primary-cyan me-2"></i>النشر والتشغيل</li>
                        </ul>
                        <a href="javascript:void(0)" class="btn-custom w-100 mt-3">
                            <i class="fas fa-hand-holding-usd me-2"></i> احجز الخدمة
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php require_once __DIR__ . '/includes/footer.php' ?>