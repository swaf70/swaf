<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect logged-in users away from register page
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Initialize CSRF token
generate_csrf_token();

$error = '';
$old = ['username' => '', 'email' => '', 'full_name' => ''];

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح. من فضلك حاول مرة أخرى.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $old['username'] = $username;
        $old['email'] = $email;

        // Validate required fields
        $validation_errors = [];

        if (!validate_required($username, 'اسم المستخدم')) {
            $validation_errors[] = 'اسم المستخدم مطلوب.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
            $validation_errors[] = 'اسم المستخدم يجب أن يكون 3-50 حرفاً (حروف أو أرقام أو _).';
        } elseif (strtolower($username) === 'admin') {
            $validation_errors[] = 'اسم المستخدم admin محجوز. اختر اسماً آخر.';
        }

        if (!validate_required($email, 'البريد الإلكتروني')) {
            $validation_errors[] = 'البريد الإلكتروني مطلوب.';
        } elseif (!validate_email($email)) {
            $validation_errors[] = 'صيغة البريد الإلكتروني غير صحيحة.';
        }

        if (!validate_required($password, 'كلمة المرور')) {
            $validation_errors[] = 'كلمة المرور مطلوبة.';
        } elseif (strlen($password) < 6) {
            $validation_errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
        }

        if ($password !== $confirm_password) {
            $validation_errors[] = 'تأكيد كلمة المرور غير متطابق.';
        }

        if (empty($validation_errors)) {
            // Check username/email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $validation_errors[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل.';
            }
        }

        if (!empty($validation_errors)) {
            $error = implode('<br>', $validation_errors);
        } else {
            // Hash password and insert new user
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, 'user', 1)"
            );
            $stmt->execute([$username, $email, $password_hash]);

            // Auto-login the new user and go straight to the homepage
            session_regenerate_id(true);
            $new_id = (int)$pdo->lastInsertId();
            $_SESSION['user_id'] = $new_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            $_SESSION['last_activity'] = time();
            $_SESSION['just_registered'] = true;

            header('Location: index.php');
            exit;
        }
    }
}
?>
<?php $hide_nav = true; require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'register'; ?>

    <main class="form-signin w-100 m-auto">
        <div class="card custom-card col-lg-5 col-md-7 mx-auto">
            <div class="card-body p-5">
                <h2 class="card-title mb-4 text-center">
                    <i class="fas fa-user-plus text-primary-cyan mb-3"></i>
                    إنشاء حساب جديد
                </h2>

                <?php if ($error): ?>
                    <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold mb-2">
                            <i class="fas fa-user me-2"></i>اسم المستخدم
                        </label>
                        <input type="text" name="username" class="form-control form-control-custom"
                               value="<?= h($old['username']) ?>"
                               placeholder="3-50 حرفاً (حروف، أرقام، _)"
                               required autocomplete="username">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold mb-2">
                            <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                        </label>
                        <input type="email" name="email" class="form-control form-control-custom"
                               value="<?= h($old['email']) ?>"
                               placeholder="example@domain.com"
                               required autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold mb-2">
                            <i class="fas fa-lock me-2"></i>كلمة المرور
                        </label>
                        <input type="password" name="password" class="form-control form-control-custom"
                               placeholder="6 أحرف على الأقل"
                               required autocomplete="new-password">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold mb-2">
                            <i class="fas fa-lock me-2"></i>تأكيد كلمة المرور
                        </label>
                        <input type="password" name="confirm_password" class="form-control form-control-custom"
                               placeholder="أعد إدخال كلمة المرور"
                               required autocomplete="new-password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="cta-button">
                            <i class="fas fa-user-plus me-2"></i> إنشاء الحساب
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <small class="text-secondary">
                        لديك حساب بالفعل؟ <a href="login.php" class="text-primary-cyan">سجل الدخول</a>
                    </small>
                </div>
            </div>
        </div>
    </main>
<?php require_once __DIR__ . '/includes/footer.php' ?>