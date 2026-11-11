<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Auth check - redirect to login if not logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

// Check admin role
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - <?= h($_SESSION['username'] ?? 'Admin') ?></title>
    
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
        <div class="page-header">
            <h1 class="h4 mb-1"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</h1>
            <div class="text-white-50 small">إحصائيات الموقع وأحدث النشاطات</div>
        </div>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>تم تحديث بيانات المستخدم بنجاح.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-custom alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>تم حذف المستخدم بنجاح.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <?php
            $stats = [];
            $queries = [
                'إجمالي المشاريع'   => "SELECT COUNT(*) AS c FROM projects",
                'إجمالي الخدمات'    => "SELECT COUNT(*) AS c FROM services",
                'الخدمات المتاحة'   => "SELECT COUNT(*) AS c FROM services WHERE status = 'Available'",
                'إجمالي الرسائل'    => "SELECT COUNT(*) AS c FROM messages",
                'رسائل غير مقروءة'  => "SELECT COUNT(*) AS c FROM messages WHERE is_read = 0",
                'إجمالي المستخدمين' => "SELECT COUNT(*) AS c FROM users",
            ];
            foreach ($queries as $label => $sql) {
                $stmt = $pdo->query($sql);
                $stats[$label] = (int)$stmt->fetch()['c'];
            }
            $icons = [
                'إجمالي المشاريع'   => ['fas fa-folder-open', 'projects.php'],
                'إجمالي الخدمات'    => ['fas fa-box', 'services.php'],
                'الخدمات المتاحة'   => ['fas fa-check-circle', 'services.php'],
                'إجمالي الرسائل'    => ['fas fa-envelope', 'messages.php'],
                'رسائل غير مقروءة'  => ['fas fa-envelope-open', 'messages.php'],
                'إجمالي المستخدمين' => ['fas fa-users', '#'],
            ];
            foreach ($stats as $label => $value) {
                [$icon, $link] = $icons[$label];
                $card = '
                        <div class="telemetry-card text-center">
                            <div class="small text-secondary mb-2"><i class="' . h($icon) . ' me-1"></i>' . h($label) . '</div>
                            <div class="telemetry-number">' . h($value) . '</div>
                        </div>';
                ?>
                <div class="col-12 col-md-4 col-lg-2">
                    <?php if ($link === '#'): ?>
                        <?= $card ?>
                    <?php else: ?>
                        <a href="<?= h($link) ?>" class="text-decoration-none d-block"><?= $card ?></a>
                    <?php endif; ?>
                </div>
                <?php
            }
            ?>
        </div>
        
        <div class="row">
            <!-- Users Table -->
            <div class="col-12 mb-4">
                <div class="card card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>مستخدمو الموقع</h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-cyan text-white"><?= h($stats['إجمالي المستخدمين']) ?> مستخدم</span>
                            <a href="profile.php#add-user" class="btn btn-sm btn-primary-cyan btn-custom">
                                <i class="fas fa-user-plus me-1"></i>إضافة مستخدم
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم المستخدم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الدور</th>
                                    <th>الحالة</th>
                                    <th>آخر دخول</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT id, username, email, role, is_active, last_login, created_at FROM users ORDER BY created_at DESC");
                                $users = $stmt->fetchAll();
                                
                                if (empty($users)) {
                                    echo '<tr><td colspan="8" class="text-center py-4">لا يوجد مستخدمون بعد.</td></tr>';
                                } else {
                                    $i = 1;
                                    foreach ($users as $user) {
                                        $roleText = $user['role'] === 'admin' ? 'مدير' : 'مستخدم';
                                        $roleClass = $user['role'] === 'admin'
                                            ? '<span class="badge rounded-pill bg-warning text-white">' . h($roleText) . '</span>'
                                            : '<span class="badge rounded-pill bg-info text-white">' . h($roleText) . '</span>';
                                        $activeClass = $user['is_active']
                                            ? '<span class="badge rounded-pill badge-read">نشط</span>'
                                            : '<span class="badge rounded-pill badge-unread">موقوف</span>';
                                        $is_self = (int)$user['id'] === (int)$_SESSION['user_id'];
                                        ?>
                                        <tr>
                                            <td><?= h($i) ?></td>
                                            <td><strong><?= h($user['username']) ?></strong></td>
                                            <td class="small"><?= h($user['email']) ?></td>
                                            <td><?= $roleClass ?></td>
                                            <td><?= $activeClass ?></td>
                                            <td class="small"><?= h(format_date($user['last_login'])) ?></td>
                                            <td class="small"><?= h(format_date($user['created_at'], 'M d, Y')) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm gap-1">
                                                    <a href="user_edit.php?id=<?= h($user['id']) ?>" class="action-btn" title="تعديل">
                                                        <i class="fas fa-edit text-primary-cyan"></i>
                                                    </a>
                                                    <?php if ($is_self): ?>
                                                        <span class="action-btn text-secondary d-inline-flex" title="لا يمكنك حذف حسابك">
                                                            <i class="fas fa-trash"></i>
                                                        </span>
                                                    <?php else: ?>
                                                        <form method="POST" action="user_edit.php"
                                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                                            <input type="hidden" name="user_id" value="<?= h($user['id']) ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="action-btn text-danger" title="حذف">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-clock me-2"></i>نشاط أخير</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>نوع النشاط</th>
                                    <th>التفاصيل</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch recent messages
                                $stmt = $pdo->prepare("SELECT id, sender_name, subject, created_at, is_read FROM messages ORDER BY created_at DESC LIMIT 5");
                                $stmt->execute();
                                $recent = $stmt->fetchAll();
                                
                                if (empty($recent)) {
                                    echo '<tr><td colspan="3" class="text-center py-4">لا توجد أنشطة مسجلة.</td></tr>';
                                } else {
                                    foreach ($recent as $item) {
                                        $readStatus = $item['is_read'] ? 'مقرؤ' : 'غير مقروء';
                                        $date = format_date($item['created_at']);
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?= $item['is_read'] ? 'success' : 'danger' ?> small">
                                                    <?= h($readStatus) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= h($item['sender_name'] ?? 'غير معروف') ?></strong><br>
                                                <?= h(truncate($item['subject'], 50)) ?>
                                            </td>
                                            <td><?= h($date) ?></td>
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