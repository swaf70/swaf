<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Require login to access the site
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Initialize CSRF token
generate_csrf_token();

// Handle form submission
$formError = '';
$formSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $formError = 'خطأ في أمان الطلب. من فضلك حاول مرة أخرى.';
    } else {
        // Sanitize and validate form inputs
        $sender_name = trim($_POST['sender_name'] ?? '');
        $sender_email = trim($_POST['sender_email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        // Validate required fields
        $validation_errors = [];
        
        if (!validate_required($sender_name, 'اسم المرسل')) {
            $validation_errors[] = 'اسم المرسل مطلوب.';
        }
        
        if (!validate_required($sender_email, 'البريد الإلكتروني')) {
            $validation_errors[] = 'البريد الإلكتروني مطلوب.';
        } elseif (!validate_email($sender_email)) {
            $validation_errors[] = 'صيغة البريد الإلكتروني غير صحيحة.';
        }
        
        if (!validate_required($subject, 'الموضوع')) {
            $validation_errors[] = 'الموضوع مطلوب.';
        }
        
        if (!validate_required($message, 'الرسالة')) {
            $validation_errors[] = 'الرسالة مطلوبة.';
        } elseif (strlen($message) < 10) {
            $validation_errors[] = 'الرسالة يجب أن تحتوي على ما لا يقل عن 10 أحرف.';
        }
        
        if (empty($validation_errors)) {
            // Prepare statement to insert message
            $stmt = $pdo->prepare(
                "INSERT INTO messages (sender_name, sender_email, subject, message, is_read) VALUES (?, ?, ?, ?, 0)"
            );
            
            // Use prepared statement with parameterized queries (NO string concatenation)
            $stmt->execute([
                h($sender_name),
                h($sender_email),
                h($subject),
                h($message)
            ]);
            
            $formSuccess = 'شكراً لرسالتك! سنقوم بالرد عليك في أقرب وقت ممكن.';
            
            // Clear form by resetting
            $sender_name = '';
            $sender_email = '';
            $subject = '';
            $message = '';
        } else {
            $formError = implode('<br>', $validation_errors);
        }
    }
}
?>
<?php require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'contact'; ?>
    
    <!-- Contact Hero -->
    <section class="contact-hero py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="contact-info">
                        <span class="display-4 fw-bold">
                            <?= h('اتصل بنا') ?>
                        </span>
                        <p class="text-secondary mb-3">
                            <?= h('متاح للرد على استفساراتكم وطلبات الخدمات في أي وقت') ?>
                        </p>
                        <a href="https://wa.me/967771792570" target="_blank" rel="noopener" class="cta-button whatsapp-cta">
                            <i class="fab fa-whatsapp fs-5 me-2"></i>راسلنا مباشرة على واتساب
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="contact-form-card">
                        <form action="contact.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">
                                    <i class="fas fa-user me-2"></i>اسمك
                                </label>
                                <input type="text" name="sender_name" class="form-control form-control-custom"
                                       placeholder="ادخل اسمك الكامل" required autocomplete="name"
                                       value="<?= isset($sender_name) ? h($sender_name) : '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">
                                    <i class="fas fa-envelope me-2"></i>بريدك الإلكتروني
                                </label>
                                <input type="email" name="sender_email" class="form-control form-control-custom"
                                       placeholder="example@domain.com" required autocomplete="email"
                                       value="<?= isset($sender_email) ? h($sender_email) : '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">
                                    <i class="fas fa-subject me-2"></i>الموضوع
                                </label>
                                <input type="text" name="subject" class="form-control form-control-custom"
                                       placeholder="اسم الموضوع" required>
                                <small class="text-secondary">اختياري: وصف breve للموضوع</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-2">
                                    <i class="fas fa-text me-2"></i>رسالتك
                                </label>
                                <textarea name="message" class="form-control form-control-custom" rows="5"
                                          placeholder="ادخل رسالتك هنا..." required>
                                <?= isset($message) ? h($message) : '' ?></textarea>
                                <small class="text-secondary">ننصحك بكتابة تفاصيل المشكلة أو الطلب</small>
                            </div>
                            
                            <div class="d-grid">
                                <?php if ($formError): ?>
                                    <div class="alert alert-custom alert-dismissible fade show mb-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?= h($formError) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($formSuccess): ?>
                                    <div class="alert alert-custom alert-success alert-dismissible fade show mb-3" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <?= h($formSuccess) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="cta-button">
                                    <i class="fas fa-paper-plane me-2"></i>إرسال الرسالة
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact Details -->
    <section class="contact-details-section py-5 bg-secondary-slate">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="contact-item text-center">
                        <i class="fas fa-map-marker-alt text-primary-cyan fs-1 mb-3"></i>
                        <h5 class="fw-bold">الموقع</h5>
                        <p class="text-secondary small">جمهورية اليمن - صنعاء</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="contact-item text-center">
                        <i class="fas fa-phone text-primary-cyan fs-1 mb-3"></i>
                        <h5 class="fw-bold">اتصال</h5>
                        <p class="text-secondary small">+967 771792570</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="contact-item text-center">
                        <i class="fas fa-envelope text-primary-cyan fs-1 mb-3"></i>
                        <h5 class="fw-bold">بريد إلكتروني</h5>
                        <p class="text-secondary small">swaf8315@gmail.com</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <a class="contact-item contact-whatsapp text-center" href="https://wa.me/967771792570" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp fs-1 mb-3 d-block"></i>
                        <h5 class="fw-bold">واتساب</h5>
                        <span class="text-secondary small">اضغط للمحادثة الفورية</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php require_once __DIR__ . '/includes/footer.php' ?>