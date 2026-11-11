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

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $project_id = (int)($_POST['project_id'] ?? 0);
        
        if ($project_id > 0) {
            // Check for project
            $stmt = $pdo->prepare("SELECT image_url FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            $project = $stmt->fetch();
            
            if ($project) {
                // Delete uploaded file if exists
                delete_uploaded_file($project['image_url']);
                
                // Delete from database using prepared statement
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$project_id]);
                
                // Success
                $success = 'تم حذف المشروع بنجاح.';
            } else {
                $error = 'المشروع غير موجود.';
            }
        } else {
            $error = 'معرف مشروع غير صالح.';
        }
    }
} else {
    // Show confirmation page
    $project_id = (int)($_GET['id'] ?? 0);
    
    if ($project_id > 0) {
        $stmt = $pdo->prepare("SELECT id, title, image_url FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch();
        
        if ($project) {
            ?>
            <!DOCTYPE html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>تأكيد الحذف</title>
                
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
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8">
                            <div class="card card-custom">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        تأكيد الحذف
                                    </h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="alert alert-custom">
                                        <h6 class="mb-3">هل أنت متأكد من حذف هذا المشروع؟</h6>
                                        <p class="mb-4">
                                            <strong><?= h($project['title']) ?></strong>
                                        </p>
                                        <p class="text-secondary small mb-0">
                                            لن يمكن إلغاء هذا الإجراء. سيتم حذف الصورة المرفقة أيضاً إذا كانت موجودة.
                                        </p>
                                    </div>
                                    
                                    <form method="POST" action="project_delete.php">
                                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                        <input type="hidden" name="project_id" value="<?= h($project['id']) ?>">
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-2"></i> نعم، احذف المشروع
                                            </button>
                                            <a href="projects.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left me-2"></i> إلغاء
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php require_once __DIR__ . '/includes/footer.php' ?>
            </body>
            </html>
            <?php
        } else {
            die('المشروع غير موجود.');
        }
    } else {
        die('معرف مشروع غير صالح.');
    }
}