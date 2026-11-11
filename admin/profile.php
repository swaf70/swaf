<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Auth check - redirect to login if not logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $action = $_POST['action'] ?? '';
        $my_id = (int)$_SESSION['user_id'];

        // ---------- Update own profile (username / email) ----------
        if ($action === 'update_self') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $validation_errors = [];

            if (!validate_required($username, 'اسم المستخدم')) {
                $validation_errors[] = 'اسم المستخدم مطلوب.';
            }
            if (!validate_required($email, 'البريد الإلكتروني')) {
                $validation_errors[] = 'البريد الإلكتروني مطلوب.';
            } elseif (!validate_email($email)) {
                $validation_errors[] = 'صيغة البريد الإلكتروني غير صحيحة.';
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $my_id]);
            if ($stmt->fetch()) {
                $validation_errors[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً.';
            }

            if (empty($validation_errors)) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $my_id]);
                $_SESSION['username'] = $username;
                $success = 'تم تحديث بياناتك بنجاح.';
            } else {
                $error = implode('<br>', $validation_errors);
            }
        }

        // ---------- Change own password (old / new / confirm) ----------
        if ($action === 'change_password') {
            $old_password = (string)($_POST['old_password'] ?? '');
            $new_password = (string)($_POST['new_password'] ?? '');
            $confirm_password = (string)($_POST['confirm_password'] ?? '');
            $validation_errors = [];

            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$my_id]);
            $row = $stmt->fetch();

            if ($old_password === '' || !password_verify($old_password, $row['password_hash'] ?? '')) {
                $validation_errors[] = 'كلمة المرور الحالية غير صحيحة.';
            }
            if (strlen($new_password) < 6) {
                $validation_errors[] = 'كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل.';
            }
            if ($new_password !== $confirm_password) {
                $validation_errors[] = 'كلمتا المرور غير متطابقتين.';
            }

            if (empty($validation_errors)) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hash, $my_id]);
                $success = 'تم تغيير كلمة المرور بنجاح.';
            } else {
                $error = implode('<br>', $validation_errors);
            }
        }

        // ---------- Add a new user (with role) ----------
        if ($action === 'add_user') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $new_password = (string)($_POST['password'] ?? '');
            $confirm_password = (string)($_POST['confirm_password'] ?? '');
            $role = trim($_POST['role'] ?? 'user');
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
            if (strlen($new_password) < 6) {
                $validation_errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
            }
            if ($new_password !== $confirm_password) {
                $validation_errors[] = 'كلمتا المرور غير متطابقتين.';
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $validation_errors[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً.';
            }

            if (empty($validation_errors)) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
                $stmt->execute([$username, $email, $hash, $role]);
                $success = 'تم إنشاء الحساب الجديد بنجاح.';
            } else {
                $error = implode('<br>', $validation_errors);
            }
        }
    }
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT id, username, email, role, created_at, last_login FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Preserve submitted values on error
$submitted_username = $_POST['username'] ?? ($user['username'] ?? '');
$submitted_email = $_POST['email'] ?? ($user['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي</title>
    <link href="../assets/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link href="../assets/css/tajawal.css" rel="stylesheet">
    <link href='../assets/css/admin-theme.css' rel='stylesheet'>
</head>
<body>
<?php $active_page = 'profile'; ?>
<?php require_once __DIR__ . '/includes/header.php' ?>

<div class="container max-width-max">
    <div class="page-header">
        <h1 class="h4 mb-1"><i class="fas fa-user-cog me-2"></i>الملف الشخصي</h1>
        <div class="text-white-50 small">إدارة بياناتك وكلمة المرور وإضافة مستخدمين</div>
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

    <div class="row g-4">
        <!-- Profile summary card -->
        <div class="col-12 col-lg-4">
            <div class="card card-custom">
                <div class="card-body p-4 text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h4 class="mt-3 mb-0"><?= h($user['username'] ?? 'Admin') ?></h4>
                    <span class="badge bg-warning text-white"><?= h($user['role'] ?? 'admin') === 'admin' ? 'مدير' : 'مستخدم' ?></span>

                    <div class="row g-3 text-start mt-3">
                        <div class="col-12">
                            <div class="info-label"><i class="fas fa-envelope me-2"></i>البريد الإلكتروني</div>
                            <div class="info-value"><?= h($user['email'] ?? '-') ?></div>
                        </div>
                        <div class="col-12">
                            <div class="info-label"><i class="fas fa-calendar me-2"></i>تاريخ الإنشاء</div>
                            <div class="info-value"><?= h(format_date($user['created_at'] ?? '', 'Y-m-d H:i')) ?></div>
                        </div>
                        <div class="col-12">
                            <div class="info-label"><i class="fas fa-clock me-2"></i>آخر دخول</div>
                            <div class="info-value"><?= h(format_date($user['last_login'] ?? '', 'Y-m-d H:i')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="row g-4">
                <!-- Update own info -->
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user-edit me-2"></i>تعديل بياناتي</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="update_self">

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-user me-2 text-primary-cyan"></i>اسم المستخدم</label>
                                        <input type="text" name="username" class="form-control form-control-custom"
                                               value="<?= h($submitted_username) ?>" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-envelope me-2 text-primary-cyan"></i>البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control form-control-custom"
                                               value="<?= h($submitted_email) ?>" required>
                                    </div>
                                </div>

                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-primary-cyan btn-custom">
                                        <i class="fas fa-save me-1"></i>حفظ البيانات
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change password -->
                <div class="col-12">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-key me-2"></i>تغيير كلمة المرور</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="change_password">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold mb-2"><i class="fas fa-lock me-2 text-primary-cyan"></i>كلمة المرور الحالية</label>
                                    <input type="password" name="old_password" class="form-control form-control-custom" autocomplete="current-password" required>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-key me-2 text-primary-cyan"></i>كلمة المرور الجديدة</label>
                                        <input type="password" name="new_password" class="form-control form-control-custom" autocomplete="new-password" required>
                                        <small class="text-secondary">6 أحرف على الأقل.</small>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-check-double me-2 text-primary-cyan"></i>تأكيد كلمة المرور الجديدة</label>
                                        <input type="password" name="confirm_password" class="form-control form-control-custom" autocomplete="new-password" required>
                                    </div>
                                </div>

                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-danger-custom btn-custom">
                                        <i class="fas fa-key me-1"></i>تغيير كلمة المرور
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Add new user -->
                <div class="col-12" id="add-user">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>إضافة مستخدم جديد</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="profile.php#add-user" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="add_user">

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-user me-2 text-primary-cyan"></i>اسم المستخدم</label>
                                        <input type="text" name="username" class="form-control form-control-custom" value="<?= h($_POST['username'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-envelope me-2 text-primary-cyan"></i>البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control form-control-custom" value="<?= h($_POST['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-lock me-2 text-primary-cyan"></i>كلمة المرور</label>
                                        <input type="password" name="password" class="form-control form-control-custom" autocomplete="new-password" required>
                                        <small class="text-secondary">6 أحرف على الأقل.</small>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-check-double me-2 text-primary-cyan"></i>تأكيد كلمة المرور</label>
                                        <input type="password" name="confirm_password" class="form-control form-control-custom" autocomplete="new-password" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-bold mb-2"><i class="fas fa-shield-alt me-2 text-primary-cyan"></i>الصلاحية</label>
                                        <select name="role" class="form-select form-select-custom">
                                            <option value="user" <?= ($_POST['role'] ?? '') === 'user' ? 'selected' : '' ?>>مستخدم</option>
                                            <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>مدير (بصلاحيات كاملة)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex mt-4">
                                    <button type="submit" class="btn btn-primary-cyan btn-custom">
                                        <i class="fas fa-user-plus me-1"></i>إنشاء الحساب
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php' ?>