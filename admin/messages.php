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

// Handle status update (mark as read/unread)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $message_id = (int)($_POST['message_id'] ?? 0);
        
        if ($message_id > 0) {
            // Toggle read status
            $stmt = $pdo->prepare("UPDATE messages SET is_read = NOT is_read WHERE id = ?");
            $stmt->execute([$message_id]);
            
            $success = 'تم تحديث الحالة بنجاح.';
        } else {
            $error = 'معرف الرسالة غير صالح.';
        }
    }
}

// Handle delete message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $error = 'رمز التحقق غير صالح.';
    } else {
        $message_id = (int)($_POST['message_id'] ?? 0);
        
        if ($message_id > 0) {
            // Get message info before deletion
            $stmt = $pdo->prepare("SELECT sender_name, subject FROM messages WHERE id = ?");
            $stmt->execute([$message_id]);
            $msg = $stmt->fetch();
            
            if ($msg) {
                // Delete from database using prepared statement
                $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
                $stmt->execute([$message_id]);
                
                $success = 'تم حذف الرسالة بنجاح.';
            } else {
                $error = 'الرسالة غير موجودة.';
            }
        } else {
            $error = 'معرف رسالة غير صالح.';
        }
    }
}

// Fetch messages
$stmt = $pdo->prepare("SELECT id, sender_name, sender_email, subject, message, is_read, created_at FROM messages ORDER BY created_at DESC");
$stmt->execute();
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الرسائل</title>
    
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
    <?php $active_page = 'messages'; ?>
    
    <div class="container">
        <!-- Success/Error messages -->
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
        
        <!-- Cards Summary -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card telemetry-card text-center">
                    <div class="small text-secondary mb-2">إجمالي الرسائل</div>
                    <h3 class="card-title mb-0"><?= count($messages) ?></h3>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card telemetry-card text-center">
                    <div class="small text-secondary mb-2">غير مقروءة</div>
                    <?php
                    $unread_stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages WHERE is_read = 0");
                    $unread_stmt->execute();
                    $unread = $unread_stmt->fetch()['total'];
                    ?>
                    <h3 class="card-title mb-0"><?= h($unread) ?></h3>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card telemetry-card text-center">
                    <div class="small text-secondary mb-2">مقروءة</div>
                    <?php
                    $read_stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages WHERE is_read = 1");
                    $read_stmt->execute();
                    $read = $read_stmt->fetch()['total'];
                    ?>
                    <h3 class="card-title mb-0"><?= h($read) ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Messages Table -->
        <div class="card card-custom">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i> الرسائل الواردة</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم المرسل</th>
                                <th>البريد الإلكتروني</th>
                                <th>الموضوع</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($messages)) {
                                echo '<tr><td colspan="7" class="text-center py-4">لا رسائل واردة.</td></tr>';
                            } else {
                                foreach ($messages as $msg) {
                                    $readStatus = $msg['is_read'] ? 'مقرؤ' : 'غير مقروء';
                                    $readBadge = $msg['is_read'] ? 'badge-read' : 'badge-unread';
                                    $date = format_date($msg['created_at']);
                                    ?>
                                    <tr>
                                        <td><?= h($msg['id']) ?></td>
                                        <td><?= h($msg['sender_name']) ?></td>
                                        <td><?= h($msg['sender_email']) ?></td>
                                        <td>
                                            <strong><?= h($msg['subject']) ?></strong>
                                            <br>
                                            <small class="text-secondary"><?= h(truncate($msg['message'], 50)) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= h($readBadge) ?> small">
                                                <?= h($readStatus) ?>
                                            </span>
                                        </td>
                                        <td><?= h($date) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <!-- Mark as Read/Unread -->
                                                <form method="POST" style="display:inline;" action="messages.php">
                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                    <input type="hidden" name="message_id" value="<?= h($msg['id']) ?>">
                                                    <button type="submit" name="update_status"
                                                            class="btn btn-<?= $msg['is_read'] ? 'primary-cyan' : 'danger' ?> text-<?= $msg['is_read'] ? 'default' : 'white' ?> small">
                                                        <i class="fas <?= $msg['is_read'] ? 'fa-eye' : 'fa-eye-slash' ?> me-1"></i>
                                                        <!--
                                                        <span class="d-none d-sm-inline">Mark as <?= $msg['is_read'] ? 'Unread' : 'Read' ?></span>
                                                        -->
                                                    </button>
                                                </form>
                                                
                                                <!-- Delete -->
                                                <form method="POST" style="display:inline;" action="messages.php" 
                                                      onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');">
                                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                    <input type="hidden" name="message_id" value="<?= h($msg['id']) ?>">
                                                    <button type="submit" name="delete_message"
                                                            class="btn btn-danger text-white small">
                                                        <i class="fas fa-trash me-1"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
<?php require_once __DIR__ . '/includes/footer.php' ?>
</body>
</html>