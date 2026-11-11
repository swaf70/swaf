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

// Handle Delete
if (isset($_GET['action']) === true && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $project_id = (int)$_GET['id'];
    
    // Check for associated images before deletion
    $stmt = $pdo->prepare("SELECT image_url FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if ($project) {
        // Delete uploaded file if exists
        delete_uploaded_file($project['image_url']);
        
        // Delete from database using prepared statement
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        
        // Set success message
        $success = 'تم حذف المشروع بنجاح.';
    } else {
        $error = 'المشروع غير موجود.';
    }
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        // Handle Add Project
        if (isset($_POST['project_action']) && $_POST['project_action'] === 'add') {
            // Validate inputs
            $title = trim($_POST['title'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $tech_stack = trim($_POST['tech_stack'] ?? '');
            $project_url = trim($_POST['project_url'] ?? '');
            
            // Validate required fields
            $validation_errors = [];
            
            if (!validate_required($title, 'العنوان')) {
                $validation_errors[] = 'العنوان مطلوب.';
            }
            
            if (!validate_required($category, 'الفئة')) {
                $validation_errors[] = 'الفئة مطلوبة.';
            } elseif (!validate_category($category)) {
                $validation_errors[] = 'فئة غير صالحة.';
            }
            
            if (!validate_required($description, 'الوصف')) {
                $validation_errors[] = 'الوصف مطلوب.';
            }
            
            if (!validate_tech_stack($tech_stack)) {
                $validation_errors[] = 'تقنيات غير صالحة.';
            }
            
            // Handle file upload
            $image_result = safe_file_upload('project_image', ['image/jpeg', 'image/png', 'image/gif'], 2097152);
            
            if (!empty($validation_errors)) {
                $error = implode('<br>', $validation_errors);
            } elseif (!$image_result['success']) {
                $error = 'خطأ في رفع الصورة: ' . $image_result['error'];
            } else {
                // Insert new project using prepared statement
                $stmt = $pdo->prepare(
                    "INSERT INTO projects (title, category, description, tech_stack, image_url, project_url) VALUES (?, ?, ?, ?, ?, ?)"
                );
                
                $image_url = $image_result['filename'];
                
                $stmt->execute([
                    h($title),
                    h($category),
                    h($description),
                    h($tech_stack),
                    h($image_url),
                    h($project_url)
                ]);
                
                $success = 'تم إضافة المشروع بنجاح.';
            }
        }
        
        // Handle Edit Project
        if (isset($_POST['project_action']) && $_POST['project_action'] === 'edit') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            
            if ($project_id <= 0) {
                $error = 'معرف المشروع غير صالح.';
            } else {
                // Get existing project
                $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
                $stmt->execute([$project_id]);
                $existing = $stmt->fetch();
                
                if (!$existing) {
                    $error = 'المشروع غير موجود.';
                } else {
                    // Validate inputs
                    $title = trim($_POST['title'] ?? $existing['title']);
                    $category = trim($_POST['category'] ?? $existing['category']);
                    $description = trim($_POST['description'] ?? $existing['description']);
                    $tech_stack = trim($_POST['tech_stack'] ?? $existing['tech_stack']);
                    $project_url = trim($_POST['project_url'] ?? $existing['project_url']);
                    
                    // Handle file upload (optional replacement)
                    $image_url = $existing['image_url'];
                    
                    if (isset($_FILES['project_image']['name']) && $_FILES['project_image']['name'] !== '') {
                        $image_result = safe_file_upload('project_image', ['image/jpeg', 'image/png', 'image/gif'], 2097152);
                        
                        if ($image_result['success']) {
                            // Delete old image
                            delete_uploaded_file($existing['image_url']);
                            $image_url = $image_result['filename'];
                        } else {
                            $error = 'خطأ في رفع الصورة: ' . $image_result['error'];
                        }
                    }
                    
                    if (empty($error ?? '')) {
                        // Update project using prepared statement
                        $stmt = $pdo->prepare(
                            "UPDATE projects SET title = ?, category = ?, description = ?, tech_stack = ?, image_url = ?, project_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
                        );
                        
                        $stmt->execute([
                            h($title),
                            h($category),
                            h($description),
                            h($tech_stack),
                            h($image_url),
                            h($project_url),
                            $project_id
                        ]);
                        
                        $success = 'تم تحديث المشروع بنجاح.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المشاريع</title>
    
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
    <?php $active_page = 'projects'; ?>
    
    <!-- Page Content -->
    <div class="container">
        <div class="row">
            <!-- Left: Actions/Add Form -->
            <div class="col-12 col-md-4 mb-4">
                <div class="card card-custom">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-plus me-2"></i>
                            إضافة مشروع جديد
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="projects.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="project_action" value="add">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">اسم المشروع</label>
                                <input type="text" name="title" class="form-control form-control-custom"
                                       placeholder="مثال: بوابة ويب آمنة" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">الفئة</label>
                                <select name="category" class="form-select form-select-custom" required>
                                    <option value="">اختر فئة</option>
                                    <option value="Web Development">Web Development</option>
                                    <option value="Network Architecture">Network Architecture</option>
                                    <option value="Cybersecurity">Cybersecurity</option>
                                    <option value="Full-Stack">Full-Stack</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">الوصف</label>
                                <textarea name="description" class="form-control form-control-custom" rows="3"
                                          placeholder="وصف المشروع"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">تقنيات الاستخدام</label>
                                <small class="text-secondary">مفصول بفواصل (مثال: PHP, MySQL, Docker)</small>
                                <input type="text" name="tech_stack" class="form-control form-control-custom"
                                       placeholder="PHP, MySQL, Linux, Docker, Python">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">رابط المشروع</label>
                                <input type="url" name="project_url" class="form-control form-control-custom"
                                       placeholder="https://github.com/username/repo">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-image me-2 text-primary-cyan"></i>صورة المشروع</label>
                                <input type="file" name="project_image" class="form-control form-control-custom" accept="image/*">
                                <small class="text-secondary">الحد الأقصى 2MB (JPG, PNG, GIF)</small>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary-cyan">
                                    <i class="fas fa-save me-2"></i> حفظ المشروع
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Right: Projects Grid -->
            <?php
            // Fetch projects using prepared statement
            $stmt = $pdo->prepare("SELECT id, title, category, description, tech_stack, image_url, project_url, created_at FROM projects ORDER BY created_at DESC");
            $stmt->execute();
            $projects = $stmt->fetchAll();
            ?>
            <div class="col-12 col-md-8">
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-project-diagram me-2"></i>
                            قائمة المشاريع
                        </h6>
                        <span class="badge rounded-pill user-chip">
                            عدد المشاريع: <?= h(count($projects)) ?>
                        </span>
                    </div>
                    <div class="card-body">
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
                        
                        <?php
                        if (empty($projects)): ?>
                            <div class="empty-state py-5">
                                <i class="fas fa-folder-open fa-3x text-primary-cyan mb-3"></i>
                                <div class="fw-bold">لا توجد مشاريع بعد</div>
                                <small class="text-secondary">أضف مشروعك الأول من النموذج المجاور</small>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($projects as $project):
                                    $tech_stacks = $project['tech_stack'] ? array_map('trim', explode(',', $project['tech_stack'])) : [];
                                    
                                    $category_name = match($project['category']) {
                                        'Web Development' => 'تطوير ويب',
                                        'Network Architecture' => 'هندسة شبكات',
                                        'Cybersecurity' => 'أمن سيبراني',
                                        'Full-Stack' => 'Full-Stack',
                                        default => h($project['category'])
                                    };
                                    
                                    $image_path = $project['image_url'] ? '../uploads/projects/' . $project['image_url'] : '';
                                    ?>
                                    <div class="col-12 col-md-6 col-xl-6">
                                        <div class="card project-card card-custom h-100">
                                            <div class="project-card-img">
                                                <?php if ($image_path): ?>
                                                    <img src="<?= h($image_path) ?>" alt="<?= h($project['title']) ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-image"></i>
                                                <?php endif; ?>
                                                <span class="category-badge"><?= h($category_name) ?></span>
                                            </div>
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-2"><?= h($project['title']) ?></h6>
                                                <p class="small text-secondary mb-3">
                                                    <?= h(truncate($project['description'], 80)) ?>
                                                </p>
                                                <?php if (!empty($tech_stacks)): ?>
                                                    <div class="tech-chips mb-2">
                                                        <?php foreach ($tech_stacks as $tech): ?>
                                                            <span class="chip"><?= h($tech) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($project['project_url']): ?>
                                                    <a href="<?= h($project['project_url']) ?>" target="_blank" rel="noopener"
                                                       class="small text-primary-cyan">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        <?= h(truncate($project['project_url'], 35)) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-footer d-flex justify-content-between align-items-center">
                                                <small class="text-secondary">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?= h(format_date($project['created_at'], 'M d, Y')) ?>
                                                </small>
                                                <div class="btn-group btn-group-sm gap-1">
                                                    <a href="project_edit.php?id=<?= h($project['id']) ?>" class="action-btn" title="تعديل">
                                                        <i class="fas fa-edit text-primary-cyan"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?= h($project['id']) ?>" class="action-btn text-danger"
                                                       title="حذف"
                                                       onclick="return confirm('هل أنت متأكد من حذف هذا المشروع؟');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for delete confirmation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle delete confirmations
            const deleteLinks = document.querySelectorAll('.action-btn[data-confirm]');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm(this.getAttribute('data-confirm'))) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
<?php require_once __DIR__ . '/includes/footer.php' ?>