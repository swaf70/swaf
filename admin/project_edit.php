<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Auth check
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

// Get project ID
$project_id = (int)($_GET['id'] ?? 0);
if ($project_id <= 0) {
    die('معرف المشروع غير صالح.');
}

// Fetch existing project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    die('المشروع غير موجود.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        // Validate inputs
        $title = trim($_POST['title'] ?? $project['title']);
        $category = trim($_POST['category'] ?? $project['category']);
        $description = trim($_POST['description'] ?? $project['description']);
        $tech_stack = trim($_POST['tech_stack'] ?? $project['tech_stack']);
        $project_url = trim($_POST['project_url'] ?? $project['project_url']);
        
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
        
        // Handle file upload (optional replacement)
        $image_url = $project['image_url'];
        
        if (isset($_FILES['project_image']['name']) && $_FILES['project_image']['name'] !== '') {
            $image_result = safe_file_upload('project_image', ['image/jpeg', 'image/png', 'image/gif'], 2097152);
            
            if ($image_result['success']) {
                // Delete old image
                delete_uploaded_file($project['image_url']);
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
            
            // Redirect back to projects page
            header('Location: projects.php?success=edited');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المشروع</title>
    
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
    
    <div class="container max-width-max">
        <div class="page-header">
            <h1 class="h4 mb-1"><i class="fas fa-edit me-2"></i>تعديل المشروع</h1>
            <div class="text-white-50 small">حدّث بيانات مشروعك الحالي</div>
        </div>
        
        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="card card-custom">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>تفاصيل المشروع</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= h($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form action="project_edit.php?id=<?= h($project['id']) ?>" method="POST" enctype="multipart/form-data" id="projectForm">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="project_id" value="<?= h($project['id']) ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-heading me-2 text-primary-cyan"></i>اسم المشروع</label>
                                <input type="text" name="title" class="form-control form-control-custom"
                                       value="<?= h($project['title']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-tags me-2 text-primary-cyan"></i>الفئة</label>
                                <select name="category" class="form-select form-select-custom" required>
                                    <option value="" <?= $project['category'] === '' ? 'selected' : '' ?>>اختر فئة</option>
                                    <option value="Web Development" <?= $project['category'] === 'Web Development' ? 'selected' : '' ?>>Web Development</option>
                                    <option value="Network Architecture" <?= $project['category'] === 'Network Architecture' ? 'selected' : '' ?>>Network Architecture</option>
                                    <option value="Cybersecurity" <?= $project['category'] === 'Cybersecurity' ? 'selected' : '' ?>>Cybersecurity</option>
                                    <option value="Full-Stack" <?= $project['category'] === 'Full-Stack' ? 'selected' : '' ?>>Full-Stack</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-align-right me-2 text-primary-cyan"></i>الوصف</label>
                                <textarea name="description" class="form-control form-control-custom" rows="4">
                                    <?= h($project['description']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-code me-2 text-primary-cyan"></i>تقنيات الاستخدام</label>
                                <small class="text-secondary d-block mb-1">مفصولة بفواصل (مثال: PHP, MySQL, Docker)</small>
                                <input type="text" name="tech_stack" class="form-control form-control-custom"
                                       value="<?= h($project['tech_stack'] ?? '') ?>" placeholder="PHP, MySQL, Linux, Docker, Python">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-link me-2 text-primary-cyan"></i>رابط المشروع</label>
                                <input type="url" name="project_url" class="form-control form-control-custom"
                                       value="<?= h($project['project_url'] ?? '') ?>" placeholder="https://github.com/username/repo">
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary-cyan btn-lg">
                                    <i class="fas fa-save me-2"></i> تحديث المشروع
                                </button>
                                <a href="projects.php" class="btn btn-outline-secondary mt-2">
                                    <i class="fas fa-arrow-left me-2"></i> إلغاء
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-5">
                <div class="card card-custom">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-image me-2"></i>صورة المشروع</h6>
                    </div>
                    <div class="card-body text-center">
                        <div id="imagePreview" class="mb-3">
                            <?php if ($project['image_url']): ?>
                                <img src="../uploads/projects/<?= h($project['image_url']) ?>"
                                     alt="<?= h($project['title']) ?>"
                                     class="img-fluid rounded shadow-sm" style="max-height:260px;object-fit:cover;">
                            <?php else: ?>
                                <div class="empty-state py-5">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary-cyan mb-3"></i>
                                    <div>لا توجد صورة حالياً</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="project_image" id="project_image" class="form-control form-control-custom"
                               accept="image/*">
                        <small class="text-secondary d-block mt-2">اتركه فارغاً للحفاظ على الصورة الحالية. الحد الأقصى 2MB (JPG, PNG, GIF)</small>
                    </div>
                </div>
                
                <div class="card card-custom mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-primary-cyan"></i>نصائح سريعة</h6>
                    </div>
                    <div class="card-body small">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>تأكد من صحة بيانات المشروع قبل الحفظ</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>حدّث التقنيات المستخدمة إن تغيرت</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>يمكنك تغيير الصورة باختيار ملف جديد</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('project_image').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML =
                        '<img src="' + e.target.result + '" class="img-fluid rounded shadow-sm" style="max-height:260px;object-fit:cover;">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
    
<?php require_once __DIR__ . '/includes/footer.php' ?>