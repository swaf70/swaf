<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $action = $_POST['action'] ?? '';
        $my_id = (int)$_SESSION['user_id'];

        // ---------- Update own username ----------
        if ($action === 'update_username') {
            $username = trim($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $validation_errors = [];

            if (!validate_required($username, 'اسم المستخدم')) {
                $validation_errors[] = 'اسم المستخدم مطلوب.';
            }

            $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE id = ?");
            $stmt->execute([$my_id]);
            $row = $stmt->fetch();

            if ($password === '' || !password_verify($password, $row['password_hash'] ?? '')) {
                $validation_errors[] = 'كلمة المرور الحالية غير صحيحة.';
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $my_id]);
            if ($stmt->fetch()) {
                $validation_errors[] = 'اسم المستخدم مستخدم مسبقاً.';
            }

            if (empty($validation_errors)) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$username, $my_id]);
                $_SESSION['username'] = $username;
                $success = 'تم تحديث اسم المستخدم بنجاح.';
            } else {
                $error = implode('<br>', $validation_errors);
            }
        }

        // ---------- Change password (old / new / confirm) ----------
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
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$hash, $my_id]);
                $success = 'تم تغيير كلمة المرور بنجاح.';
            } else {
                $error = implode('<br>', $validation_errors);
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$page_title = 'الملف الشخصي';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?> | <?= h('سكيور تيك (SecureTech)') ?></title>
    <link href="assets/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link href="assets/css/tajawal.css" rel="stylesheet">
    <link href="assets/css/site-theme.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php' ?>

<div class="container py-5">
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= h($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center g-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4 h-100">
                <div class="profile-avatar mx-auto">
                    <i class="fas fa-user"></i>
                </div>
                <h5 class="fw-bold mt-3 mb-0"><?= h($user['username'] ?? '') ?></h5>
                <span class="text-secondary small"><?= h($user['email'] ?? '') ?></span>
                <div class="mt-3">
                    <span class="badge rounded-pill bg-primary-cyan text-white"><?= h($user['role'] ?? 'user') === 'admin' ? 'مدير' : 'مستخدم' ?></span>
                </div>
                <p class="text-secondary small mt-3 mb-0">
                    <i class="fas fa-calendar me-2"></i>تاريخ الانضمام: <?= h(format_date($user['created_at'] ?? '', 'Y-m-d')) ?>
                </p>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-user-edit me-2 text-primary-cyan"></i>تغيير اسم المستخدم</h6>
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="update_username">

                            <div class="mb-3">
                                <label class="form-label small fw-bold">اسم المستخدم</label>
                                <input type="text" name="username" class="form-control"
                                       value="<?= h($_POST['username'] ?? ($user['username'] ?? '')) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">كلمة المرور الحالية (للتأكيد)</label>
                                <input type="password" name="password" class="form-control" autocomplete="current-password" required>
                            </div>
                            <button type="submit" class="btn btn-primary-cyan btn-custom">
                                <i class="fas fa-save me-1"></i>حفظ اسم المستخدم
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-key me-2 text-primary-cyan"></i>تغيير كلمة المرور</h6>
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="change_password">

                            <div class="mb-3">
                                <label class="form-label small fw-bold">كلمة المرور الحالية</label>
                                <input type="password" name="old_password" class="form-control" autocomplete="current-password" required>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label small fw-bold">كلمة المرور الجديدة</label>
                                    <input type="password" name="new_password" class="form-control" autocomplete="new-password" required>
                                    <small class="text-secondary">6 أحرف على الأقل.</small>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label small fw-bold">تأكيد كلمة المرور الجديدة</label>
                                    <input type="password" name="confirm_password" class="form-control" autocomplete="new-password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary-cyan btn-custom">
                                <i class="fas fa-key me-1"></i>تغيير كلمة المرور
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php' ?>