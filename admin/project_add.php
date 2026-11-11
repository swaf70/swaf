<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        // Validate inputs
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $tech_stack = trim($_POST['tech_stack'] ?? '');
        $project_url = trim($_POST['project_url'] ?? '');
        
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
        
        // Handle file upload (optional)
        $image_url = null;
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_result = safe_file_upload('project_image', ['image/jpeg', 'image/png', 'image/gif'], 2097152);
            
            if (!empty($validation_errors)) {
                $error = implode('<br>', $validation_errors);
            } elseif (!$image_result['success']) {
                $error = 'خطأ في رفع الصورة: ' . $image_result['error'];
            } else {
                $image_url = $image_result['filename'];
            }
        } elseif (!empty($validation_errors)) {
            $error = implode('<br>', $validation_errors);
        }
        
        if (empty($error) && empty($validation_errors)) {
            // Insert new project using prepared statement
            $stmt = $pdo->prepare(
                "INSERT INTO projects (title, category, description, tech_stack, image_url, project_url) VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                h($title),
                h($category),
                h($description),
                h($tech_stack),
                h($image_url),
                h($project_url)
            ]);
            
            // Redirect back to projects page
            header('Location: projects.php?success=added');
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
    <title>إضافة مشروع جديد</title>
    
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
            <h1 class="h4 mb-1"><i class="fas fa-plus-circle me-2"></i>إضافة مشروع جديد</h1>
            <div class="text-white-50 small">أضف مشروعاً جديداً إلى معرض أعمالك</div>
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
                        
                        <form action="project_add.php" method="POST" enctype="multipart/form-data" id="projectForm">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-heading me-2 text-primary-cyan"></i>اسم المشروع</label>
                                <input type="text" name="title" class="form-control form-control-custom"
                                       placeholder="مثال: بوابة ويب آمنة" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-tags me-2 text-primary-cyan"></i>الفئة</label>
                                <select name="category" class="form-select form-select-custom" required>
                                    <option value="">اختر فئة</option>
                                    <option value="Web Development">Web Development</option>
                                    <option value="Network Architecture">Network Architecture</option>
                                    <option value="Cybersecurity">Cybersecurity</option>
                                    <option value="Full-Stack">Full-Stack</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-align-right me-2 text-primary-cyan"></i>الوصف</label>
                                <textarea name="description" class="form-control form-control-custom" rows="4"
                                          placeholder="وصف المشروع ونطاق العمل"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-code me-2 text-primary-cyan"></i>تقنيات الاستخدام</label>
                                <small class="text-secondary d-block mb-1">مفصولة بفواصل (مثال: PHP, MySQL, Docker)</small>
                                <input type="text" name="tech_stack" class="form-control form-control-custom"
                                       placeholder="PHP, MySQL, Linux, Docker, Python">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2"><i class="fas fa-link me-2 text-primary-cyan"></i>رابط المشروع</label>
                                <input type="url" name="project_url" class="form-control form-control-custom"
                                       placeholder="https://github.com/username/repo">
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary-cyan btn-lg">
                                    <i class="fas fa-save me-2"></i> حفظ المشروع
                                </button>
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
                            <div class="empty-state py-5">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary-cyan mb-3"></i>
                                <div>معاينة الصورة ستظهر هنا</div>
                            </div>
                        </div>
                        <input type="file" name="project_image" id="project_image" class="form-control form-control-custom"
                               accept="image/*">
                        <small class="text-secondary d-block mt-2">الحد الأقصى 2MB (JPG, PNG, GIF)</small>
                    </div>
                </div>
                
                <div class="card card-custom mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-primary-cyan"></i>نصائح سريعة</h6>
                    </div>
                    <div class="card-body small">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>استخدم عنواناً واضحاً يصف المشروع</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>اذكر التقنيات المستخدمة بفواصل</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>صورة بدقة عالية تعكس المشروع</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>أضف رابطاً للمشروع إن أمكن</li>
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