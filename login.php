<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Initialize CSRF token
generate_csrf_token();

// Initialize error/success messages
$error = '';
$success = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح. من فضلك حاول مرة أخرى.';
    } else {
        // Sanitize and validate inputs
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate required fields
        if (!validate_required($username, 'اسم المستخدم')) {
            $error = 'اسم المستخدم مطلوب.';
        } elseif (!validate_required($password, 'كلمة المرور')) {
            $error = 'كلمة المرور مطلوبة.';
        } else {
            // Prepare statement to get user by username
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
if ($user && password_verify($password, $user['password_hash'])) {
                    // Successful login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();
                    
                    // Redirect to admin dashboard or home
                    header('Location: ' . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
                    exit;
                } else {
                    $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
                }
        }
    }
}
?>
<?php $hide_nav = true; require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'login'; ?>
    
    <main class="form-signin w-100 m-auto">
        <div class="card custom-card col-lg-5 col-md-7 mx-auto">
            <div class="card-body p-5">
                <h2 class="card-title mb-4 text-center">
                    <i class="fas fa-sign-in-alt text-primary-cyan mb-3"></i>
                    تسجيل الدخول
                </h2>
                
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        تم إنشاء حسابك بنجاح. سجل الدخول الآن.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= h($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold mb-2">
                            <i class="fas fa-user me-2"></i>اسم المستخدم أو البريد الإلكتروني
                        </label>
                        <input type="text" name="username" class="form-control form-control-custom" 
                               placeholder="ادخل اسم المستخدم أو البريد الإلكتروني"
                               required autocomplete="username">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold mb-2">
                            <i class="fas fa-lock me-2"></i>كلمة المرور
                        </label>
                        <input type="password" name="password" class="form-control form-control-custom"
                               placeholder="ادخل كلمة المرور" required autocomplete="current-password">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="cta-button">
                            <i class="fas fa-arrow-right me-2"></i> تسجيل الدخول
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <small class="text-secondary">
                            ليس لديك حساب؟ <a href="register.php" class="text-primary-cyan">سجل الآن</a>
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </main>
<?php require_once __DIR__ . '/includes/footer.php' ?>