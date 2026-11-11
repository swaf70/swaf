<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
$success = $success ?? '';
$error = $error ?? '';

// Auth check
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        // Handle Add/Edit Service
        if (isset($_POST['service_action'])) {
            $service_action = trim($_POST['service_action']);
            $service_id = (int)($_POST['service_id'] ?? 0);
            $service_name = trim($_POST['service_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $status = trim($_POST['status'] ?? 'Available');
            
            // Validate
            $validation_errors = [];
            
            if (!validate_required($service_name, 'اسم الخدمة')) {
                $validation_errors[] = 'اسم الخدمة مطلوب.';
            }
            
            if (!validate_required($description, 'الوصف')) {
                $validation_errors[] = 'الوصف مطلوب.';
            }
            
            if (!validate_required($price) || $price < 0) {
                $validation_errors[] = 'السعر يجب أن يكون رقماً صحيحاً.';
            }
            
            if (!in_array($status, ['Available', 'Busy', 'Completed'])) {
                $validation_errors[] = 'حالة الخدمة غير صالحة.';
            }
            
            if (empty($validation_errors)) {
                if ($service_action === 'add') {
                    // Insert new service
                    $stmt = $pdo->prepare(
                        "INSERT INTO services (service_name, description, price, status) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->execute([h($service_name), h($description), $price, h($status)]);
                    $success = 'تم إضافة الخدمة بنجاح.';
                } elseif ($service_action === 'edit' && $service_id > 0) {
                    // Update service
                    $stmt = $pdo->prepare(
                        "UPDATE services SET service_name = ?, description = ?, price = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
                    );
                    $stmt->execute([h($service_name), h($description), $price, h($status), $service_id]);
                    $success = 'تم تحديث الخدمة بنجاح.';
                }
            } else {
                $error = implode('<br>', $validation_errors);
            }
        }
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $service_id = (int)($_POST['service_id'] ?? 0);
        
        if ($service_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
            $success = 'تم حذف الخدمة بنجاح.';
        } else {
            $error = 'معرف خدمة غير صالح.';
        }
    }
}

// Fetch services
$stmt = $pdo->prepare("SELECT id, service_name, description, price, status FROM services ORDER BY 
    CASE WHEN status = 'Available' THEN 1 WHEN status = 'Busy' THEN 2 ELSE 3 END, created_at");
$stmt->execute();
$services = $stmt->fetchAll();

// Service being edited
$edit_service = null;
if (isset($_GET['edit_service']) && (int)$_GET['edit_service'] > 0) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_service']]);
    $edit_service = $stmt->fetch();
    if (!$edit_service) {
        $edit_service = null;
    }
}
$is_editing = !empty($edit_service);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الخدمات</title>
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="../assets/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/css/all.min.css">
    
    <!-- Tajawal Font -->
    <link href="../assets/css/tajawal.css" rel="stylesheet">
    <link href='../assets/css/admin-theme.css' rel='stylesheet'>
</head>
<body>
    
<?php require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'services'; ?>
    
    <div class="container">
        <!-- Success/Error -->
        <?php if ($success): ?>
            <div class="alert alert-custom alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= h($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-custom alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= h($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Add/Edit Service Form -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card card-custom h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas <?= $is_editing ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                            <?= $is_editing ? 'تعديل خدمة' : 'إضافة خدمة جديدة' ?>
                        </h6>
                        <?php if ($is_editing): ?>
                            <a href="services.php" class="btn btn-sm btn-outline-secondary btn-custom">
                                <i class="fas fa-times me-1"></i>إلغاء التعديل
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <form action="services.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="service_action" value="<?= $is_editing ? 'edit' : 'add' ?>">
                            <?php if ($is_editing): ?>
                                <input type="hidden" name="service_id" value="<?= h($edit_service['id']) ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-tag me-2 text-primary-cyan"></i>اسم الخدمة</label>
                                <input type="text" name="service_name" class="form-control form-control-custom"
                                       value="<?= $is_editing ? h($edit_service['service_name']) : '' ?>"
                                       placeholder="مثال: اختبار الاختراق" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-align-right me-2 text-primary-cyan"></i>الوصف</label>
                                <textarea name="description" class="form-control form-control-custom" rows="3"
                                          placeholder="وصف تفصيلي للخدمة وما تتضمنه"><?= $is_editing ? h($edit_service['description']) : '' ?></textarea>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-money-bill me-2 text-primary-cyan"></i>السعر (يير)</label>
                                    <input type="number" name="price" class="form-control form-control-custom"
                                           value="<?= $is_editing ? h($edit_service['price']) : '' ?>"
                                           min="0" step="100" placeholder="مثال: 500" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-info-circle me-2 text-primary-cyan"></i>الحالة</label>
                                    <select name="status" class="form-select form-select-custom" required>
                                        <option value="">اختر حالة</option>
                                        <?php foreach (['Available', 'Busy', 'Completed'] as $st): ?>
                                            <option value="<?= h($st) ?>" <?= $is_editing && $edit_service['status'] === $st ? 'selected' : '' ?>><?= h($st) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary-cyan btn-lg">
                                    <i class="fas fa-save me-2"></i> <?= $is_editing ? 'تحديث الخدمة' : 'حفظ الخدمة' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-5">
                <div class="card card-custom mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2 text-primary-cyan"></i>حالة الخدمات</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $statusCounts = ['Available' => 0, 'Busy' => 0, 'Completed' => 0];
                        foreach ($services as $s) {
                            if (isset($statusCounts[$s['status']])) {
                                $statusCounts[$s['status']]++;
                            }
                        }
                        $labels = [
                            'Available' => ['متاحة', 'badge-read'],
                            'Busy'      => ['مشغولة', 'badge-unread'],
                            'Completed' => ['مكتملة', 'bg-warning text-white'],
                        ];
                        foreach ($statusCounts as $key => $count) {
                            [$label, $badge] = $labels[$key];
                            ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><span class="badge rounded-pill <?= $badge ?>"><?= h($label) ?></span></span>
                                <span class="fw-bold"><?= h($count) ?></span>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                
                <div class="card card-custom">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-primary-cyan"></i>نصائح سريعة</h6>
                    </div>
                    <div class="card-body small">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>اذكر نطاق الخدمة بدقة في الوصف</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>حدد سعراً واضحاً باليير</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>غيّر الحالة إلى Busy أثناء التنفيذ وCompleted عند النهاية</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Services Grid -->
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-box me-2"></i>
                    قائمة الخدمات
                </h6>
                <span class="badge rounded-pill user-chip">
                    عدد الخدمات: <?= h(count($services)) ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($services)): ?>
                    <div class="empty-state py-5">
                        <i class="fas fa-box-open fa-3x text-primary-cyan mb-3"></i>
                        <div class="fw-bold">لا توجد خدمات بعد</div>
                        <small class="text-secondary">أضف خدمتك الأولى من النموذج أعلاه</small>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php
                        $icons = [
                            'fas fa-cogs',
                            'fas fa-shield-alt',
                            'fas fa-server',
                            'fas fa-code',
                            'fas fa-network-wired',
                            'fas fa-tools',
                            'fas fa-database',
                            'fas fa-cloud'
                        ];
                        $i = 0;
                        foreach ($services as $service):
                            $statusClass = match($service['status']) {
                                'Available' => 'service-status-available',
                                'Busy' => 'service-status-busy',
                                'Completed' => 'service-status-completed',
                                default => 'service-status-available'
                            };
                            $statusText = match($service['status']) {
                                'Available' => 'متاحة',
                                'Busy' => 'مشغولة',
                                'Completed' => 'مكتملة',
                                default => h($service['status'])
                            };
                            $icon = $icons[$i % count($icons)];
                            ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card service-card card-custom h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="service-icon"><i class="<?= h($icon) ?>"></i></div>
                                            <span class="badge service-status-badge <?= h($statusClass) ?>">
                                                <?= h($statusText) ?>
                                            </span>
                                        </div>
                                        <h6 class="fw-bold mb-2"><?= h($service['service_name']) ?></h6>
                                        <p class="small text-secondary mb-3">
                                            <?= h(truncate($service['description'], 90)) ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                            <span class="fw-bold text-primary-cyan">
                                                <?= number_format($service['price'], 0, '', ',') ?> يير
                                            </span>
                                            <div class="btn-group btn-group-sm gap-1">
                                                <a href="services.php?edit_service=<?= h($service['id']) ?>"
                                                   class="action-btn" title="تعديل">
                                                    <i class="fas fa-edit text-primary-cyan"></i>
                                                </a>
                                                <form method="POST"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الخدمة؟');">
                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                    <input type="hidden" name="service_id" value="<?= h($service['id']) ?>">
                                                    <button type="submit" name="delete_service"
                                                            class="action-btn text-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $i++;
                        endforeach;
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
<?php require_once __DIR__ . '/includes/footer.php' ?>
</body>
</html>