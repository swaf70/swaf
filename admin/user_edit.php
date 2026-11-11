<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
$error = $error ?? '';
$success = $success ?? '';

// Auth check
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

// Determine target user id (from GET or POST)
$target_id = (int)($_POST['user_id'] ?? $_GET['id'] ?? 0);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $action = $_POST['action'] ?? '';

        // ---------- Delete user ----------
        if ($action === 'delete') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id === (int)$_SESSION['user_id']) {
                $error = 'لا يمكنك حذف حسابك الحالي.';
            } else {
                $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $target = $stmt->fetch();
                if (!$target) {
                    $error = 'المستخدم غير موجود.';
                } elseif ($target['role'] === 'admin') {
                    $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'admin' AND is_active = 1")->fetch()['c'];
                    if ($count <= 1) {
                        $error = 'لا يمكن حذف آخر مدير في الموقع.';
                    }
                }
                if (!$error) {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    header('Location: index.php?deleted=1');
                    exit;
                }
            }
        }

        // ---------- Update user ----------
        if ($action === 'update') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = trim($_POST['role'] ?? 'user');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $new_password = (string)($_POST['new_password'] ?? '');
            $confirm_password = (string)($_POST['confirm_password'] ?? '');

            $validation_errors = [];

            if (!validate_required($username, 'اسم المستخدم')) {
                $validation_errors[] = 'اسم المستخدم مطلوب.';
            }
            if (!validate_required($email, 'البريد الإلكتروني')) {
                $validation_errors[] = 'البريد الإلكتروني مطلوب.';
            } elseif (!validate_email($email)) {
                $validation_errors[] = 'صيغة البريد الإلكتروني غير صحيحة.';
            }
            if (!in_array($role, ['admin', 'user'])) {
                $validation_errors[] = 'الصلاحية غير صالحة.';
            }
            if ($new_password !== '') {
                if (strlen($new_password) < 6) {
                    $validation_errors[] = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل.';
                } elseif ($new_password !== $confirm_password) {
                    $validation_errors[] = 'كلمتا المرور غير متطابقتين.';
                }
            }

            // Duplicate check (username / email, excluding self)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetch()) {
                $validation_errors[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً.';
            }

            // Guards: last admin + self role change
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $target = $stmt->fetch();

            if ($target && $target['role'] === 'admin' && $role !== 'admin') {
                $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'admin' AND is_active = 1")->fetch()['c'];
                if ($count <= 1) {
                    $validation_errors[] = 'لا يمكن إزالة صلاحية آخر مدير في الموقع.';
                }
            }
            if ((int)$user_id === (int)$_SESSION['user_id'] && $role !== 'admin') {
                $validation_errors[] = 'لا يمكنك إزالة صلاحياتك الإدارية من حسابك.';
            }

            if (empty($validation_errors)) {
                if ($new_password !== '') {
                    $hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, password_hash = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $is_active, $hash, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $is_active, $user_id]);
                }
                header('Location: index.php?updated=1');
                exit;
            }
            $error = implode('<br>', $validation_errors);
        }
    }
}

// Load the target user after possible POST (so the form shows submitted values / fresh data)
$target_user = null;
if ($target_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$target_id]);
    $target_user = $stmt->fetch();
    if (!$target_user) {
        $target_user = null;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل مستخدم</title>
    <link href="../assets/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link href="../assets/css/tajawal.css" rel="stylesheet">
    <link href='../assets/css/admin-theme.css' rel='stylesheet'>
</head>
<body>

<?php require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'index'; ?>

    <div class="container max-width-max">
        <div class="page-header">
            <h1 class="h4 mb-1"><i class="fas fa-user-edit me-2"></i>تعديل بيانات المستخدم</h1>
            <div class="text-white-50 small">تعديل البيانات، الصلاحيات، وحالة الحساب</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= h($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!$target_user): ?>
            <div class="card card-custom">
                <div class="card-body empty-state py-5">
                    <i class="fas fa-user-slash fa-3x text-primary-cyan mb-3"></i>
                    <div class="fw-bold">المستخدم غير موجود</div>
                    <a href="index.php" class="btn btn-outline-secondary btn-custom mt-3">
                        <i class="fas fa-arrow-right me-1"></i>العودة للوحة التحكم
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php
            $is_self = (int)$target_user['id'] === (int)$_SESSION['user_id'];
            $submitted_username = $_POST['username'] ?? $target_user['username'];
            $submitted_email = $_POST['email'] ?? $target_user['email'];
            $submitted_role = $_POST['role'] ?? $target_user['role'];
            $submitted_active = isset($_POST['is_active']) ? 1 : (int)$target_user['is_active'];
            ?>
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user-cog me-2"></i>بيانات الحساب</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="user_edit.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="user_id" value="<?= h($target_user['id']) ?>">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-user me-2 text-primary-cyan"></i>اسم المستخدم</label>
                                    <input type="text" name="username" class="form-control form-control-custom"
                                           value="<?= h($submitted_username) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-envelope me-2 text-primary-cyan"></i>البريد الإلكتروني</label>
                                    <input type="email" name="email" class="form-control form-control-custom"
                                           value="<?= h($submitted_email) ?>" required>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-shield-alt me-2 text-primary-cyan"></i>الصلاحية</label>
                                        <select name="role" class="form-select form-select-custom" <?= $is_self ? 'disabled' : '' ?>>
                                            <option value="user" <?= $submitted_role === 'user' ? 'selected' : '' ?>>مستخدم</option>
                                            <option value="admin" <?= $submitted_role === 'admin' ? 'selected' : '' ?>>مدير</option>
                                        </select>
                                        <?php if ($is_self): ?>
                                            <small class="text-secondary">لا يمكنك تغيير صلاحية حسابك من هنا.</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-user-check me-2 text-primary-cyan"></i>حالة الحساب</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                                   <?= $submitted_active ? 'checked' : '' ?> <?= $is_self ? 'disabled' : '' ?>>
                                            <label class="form-check-label small fw-bold" for="is_active">الحساب نشط</label>
                                        </div>
                                        <?php if ($is_self): ?>
                                            <small class="text-secondary">لا يمكنك تعطيل حسابك بنفسك.</small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4 flex-wrap">
                                    <button type="submit" class="btn btn-primary-cyan btn-custom">
                                        <i class="fas fa-save me-1"></i>حفظ التغييرات
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary btn-custom">
                                        <i class="fas fa-arrow-right me-1"></i>عودة
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card card-custom mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-key me-2"></i>إعادة تعيين كلمة المرور</h6>
                        </div>
                        <div class="card-body">
                            <form action="user_edit.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="user_id" value="<?= h($target_user['id']) ?>">
                                <input type="hidden" name="username" value="<?= h($submitted_username) ?>">
                                <input type="hidden" name="email" value="<?= h($submitted_email) ?>">
                                <input type="hidden" name="role" value="<?= h($submitted_role) ?>">
                                <input type="hidden" name="is_active" value="1">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-lock me-2 text-primary-cyan"></i>كلمة مرور جديدة</label>
                                    <input type="password" name="new_password" class="form-control form-control-custom" autocomplete="new-password">
                                    <small class="text-secondary">اتركها فارغة للإبقاء على كلمة المرور الحالية (6 أحرف على الأقل).</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-check-double me-2 text-primary-cyan"></i>تأكيد كلمة المرور</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-custom" autocomplete="new-password">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-danger-custom btn-custom">
                                        <i class="fas fa-key me-1"></i>تعيين كلمة مرور جديدة
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card card-custom">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>معلومات الحساب</h6>
                        </div>
                        <div class="card-body small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="info-label">تاريخ الإنشاء</span>
                                <span class="fw-bold"><?= h(format_date($target_user['created_at'] ?? '', 'M d, Y')) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="info-label">آخر دخول</span>
                                <span class="fw-bold"><?= h(format_date($target_user['last_login'] ?? '', 'M d, Y h:i A')) ?></span>
                            </div>
                            <?php if (!$is_self): ?>
                                <form method="POST" action="user_edit.php" class="d-grid mt-3"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟');">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="user_id" value="<?= h($target_user['id']) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-custom">
                                        <i class="fas fa-trash me-1"></i>حذف المستخدم
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/includes/footer.php' ?>