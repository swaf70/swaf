# خطة مشروع موقع سكيور تيك (SecureTech) — الدليل الكامل والمفصّل

> منصة أمن معلومات وتقنية شبكات + لوحة تحكم إدارية — PHP / MySQL / Bootstrap 5 RTL
> آخر تحديث: 2026

---

## 1) نظرة عامة

| العنصر | القيمة |
|---|---|
| اسم الموقع | سكيور تيك (SecureTech) |
| الوصف | حلول أمن المعلومات وتقنية الشبكات بمعايير عالمية |
| نوع المشروع | موقع خدمات + لوحة تحكم إدارية كاملة |
| لغة البرمجة | PHP 8.2 |
| قاعدة البيانات | MySQL / MariaDB — `alsawaf_db` |
| الواجهات | Bootstrap 5 RTL + Font Awesome + خط Tajawal (كلها محلية بلا CDN) |
| بيئة التشغيل | XAMPP على Windows |
| مسار المشروع | `C:\xampp\htdocs\swaf` |
| رابط الموقع | http://localhost/swaf/ |
| رابط لوحة التحكم | http://localhost/swaf/admin/index.php |
| رابط تسجيل الدخول | http://localhost/swaf/login.php |
| البريد الرسمي | swaf8315@gmail.com |
| الهاتف / واتساب | +967 771792570 |
| الموقع الجغرافي | جمهورية اليمن - صنعاء |

### بيانات الدخول الافتراضية

```text
اسم المستخدم : admin
كلمة المرور  : Admin123!
الدور        : admin
البريد        : admin@alsawaf.dev
```

> يمكن للمدير تغيير اسم المستخدم والبريد وكلمة المرور من صفحة **الملف الشخصي** داخل لوحة التحكم.
> ويمكنه أيضاً إنشاء مستخدمين جدد وتحديد صلاحية كل واحد (مدير / مستخدم).

---

## 2) أوامر التشغيل والإعداد

### بدء خدمات XAMPP

```powershell
# تشغيل Apache و MySQL من لوحة تحكم XAMPP
C:\xampp\xampp-control.exe
```

### إنشاء قاعدة البيانات من ملف المخطط

```powershell
C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\swaf\schema.sql
```

### إعدادات الاتصال `config/db.php`

```php
const DB_HOST     = 'localhost';
const DB_NAME     = 'alsawaf_db';
const DB_CHARSET  = 'utf8mb4';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';
```

### فحص صياغة كل ملفات PHP

```powershell
Get-ChildItem -Path "C:\xampp\htdocs\swaf" -Filter *.php -Recurse | ForEach-Object {
    & "C:\xampp\php\php.exe" -l $_.FullName
}
```

### تنفيذ استعلامات سريعة على القاعدة

```powershell
C:\xampp\mysql\bin\mysql.exe -u root alsawaf_db -e "SHOW TABLES;"
C:\xampp\mysql\bin\mysql.exe -u root alsawaf_db -e "SELECT id, username, email, role, is_active FROM users;"
C:\xampp\mysql\bin\mysql.exe -u root alsawaf_db -e "SELECT COUNT(*) FROM projects;"
```

---

## 3) قاعدة البيانات `alsawaf_db`

### الجداول والعروض

```text
users                    حسابات الدخول (مدير / مستخدم)
projects                 معرض الأعمال والمشاريع
services                 الخدمات والأسعار
messages                 رسائل نموذج التواصل
unread_messages_count    ← عرض (View) يعدّ الرسائل غير المقروءة
```

### المخطط الكامل `schema.sql`

```sql
-- Database schema for SecureTech (سكيور تيك) Portfolio & Security Hub
-- MySQL 8.0+

-- Create database
CREATE DATABASE IF NOT EXISTS alsawaf_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alsawaf_db;

-- --------------------------------------------------------
-- Table: users
-- Stores authentication data for admins and normal users
-- --------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: Admin123! hashed with bcrypt)
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@alsawaf.dev', '$2y$10$0eZ258NuviYTQ3GjMfXQ6.VZYLn9/dPj2aqMozaaGWymUF3f2669K', 'admin');

-- --------------------------------------------------------
-- Table: projects
-- Stores portfolio projects
-- --------------------------------------------------------
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category ENUM('Web Development', 'Network Architecture', 'Cybersecurity', 'Full-Stack') NOT NULL,
    description TEXT,
    tech_stack VARCHAR(255) NULL, -- comma-separated: PHP, MySQL, Linux, Docker, Python
    image_url VARCHAR(500) NULL,  -- name of image file inside uploads/projects/
    project_url VARCHAR(500) NULL, -- live demo or GitHub URL
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO projects (title, category, description, tech_stack, image_url, project_url) VALUES
('Secure Web Portal', 'Web Development', 'A full-featured web application with authentication and role-based access control.', 'PHP, MySQL, Linux, Docker, HTML5, CSS3', 'project1.jpg', 'https://github.com/alsawaf/secure-portal'),
('Network Infrastructure Monitor', 'Network Architecture', 'Real-time network monitoring and traffic analysis dashboard for enterprise networks.', 'PHP, MySQL, Python, Grafana, Docker', 'project2.jpg', 'https://github.com/alsawaf/network-monitor'),
('Vulnerability Assessment Tool', 'Cybersecurity', 'Automated security scanning tool for detecting common web vulnerabilities and misconfigurations.', 'Python, Flask, MySQL, OpenCV, Bootstrap', 'project3.jpg', 'https://github.com/alsawaf/vuln-assessment'),
('E-commerce Platform', 'Full-Stack', 'Complete online store with payment gateway integration, admin panel, and inventory management.', 'PHP, MySQL, Stripe API, React, Bootstrap', 'project4.jpg', 'https://github.com/alsawaf/ecommerce-platform');

-- --------------------------------------------------------
-- Table: services
-- --------------------------------------------------------
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('Available', 'Busy', 'Completed') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO services (service_name, description, price, status) VALUES
('Web Application Penetration Testing', 'Comprehensive security assessment and vulnerability identification for web applications', 50000.00, 'Available'),
('Network Infrastructure Setup', 'Design and deployment of secure network infrastructure for businesses in Yemen', 35000.00, 'Available'),
('Full-Stack Web Development', 'Custom web application development from concept to deployment with secure architecture', 45000.00, 'Available'),
('Security Audit & Compliance', 'PCI-DSS, ISO27001, and custom security audits for organizational systems', 60000.00, 'Busy');

-- --------------------------------------------------------
-- Table: messages
-- --------------------------------------------------------
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO messages (sender_name, sender_email, subject, message, is_read) VALUES
('Ahmed Hassan', 'ahmed@techco.com.ye', 'Inquiry about security audit', 'Hello, I would like to request a security audit.', 0),
('Fatima Saleh', 'fatima@startup.ye', 'Full-stack development project', 'Hi, I am interested in your full-stack development services.', 1);

-- --------------------------------------------------------
-- View: unread_messages_count
-- --------------------------------------------------------
CREATE VIEW unread_messages_count AS
SELECT COUNT(*) AS total_unread FROM messages WHERE is_read = 0;
```

### وصف الحقول

**`users`** — حسابات الدخول

| الحقل | النوع | ملاحظات |
|---|---|---|
| id | INT AI | المفتاح الأساسي |
| username | VARCHAR(50) | فريد — يُستخدم في الدخول |
| email | VARCHAR(100) | فريد |
| password_hash | VARCHAR(255) | bcrypt عبر `password_hash()` |
| role | ENUM('admin','user') | الافتراضي `user` |
| is_active | TINYINT(1) | تفعيل/إيقاف الحساب |
| last_login | DATETIME | آخر دخول ناجح |
| created_at / updated_at | TIMESTAMP | تلقائي |

**`projects`** — معرض الأعمال

| الحقل | النوع | ملاحظات |
|---|---|---|
| id | INT AI | |
| title | VARCHAR(200) | اسم المشروع |
| category | ENUM | تطوير ويب / هندسة شبكات / أمن سيبراني / Full-Stack |
| description | TEXT | الوصف |
| tech_stack | VARCHAR(255) | تقنيات مفصولة بفواصل |
| image_url | VARCHAR(500) | اسم ملف الصورة في `uploads/projects/` |
| project_url | VARCHAR(500) | رابط المشروع |
| created_at / updated_at | TIMESTAMP | تلقائي |

**`services`** — الخدمات

| الحقل | النوع | ملاحظات |
|---|---|---|
| id | INT AI | |
| service_name | VARCHAR(200) | اسم الخدمة |
| description | TEXT | الوصف |
| price | DECIMAL(10,2) | السعر (ريال يمني) |
| status | ENUM | Available / Busy / Completed |
| created_at / updated_at | TIMESTAMP | تلقائي |

**`messages`** — رسائل التواصل

| الحقل | النوع | ملاحظات |
|---|---|---|
| id | INT AI | |
| sender_name / sender_email | VARCHAR | بيانات المرسل |
| subject | VARCHAR(200) | الموضوع |
| message | TEXT | نص الرسالة |
| is_read | TINYINT(1) | مقروء / غير مقروء |
| created_at / updated_at | TIMESTAMP | تلقائي |

---

## 4) شجرة الملفات

```text
C:\xampp\htdocs\swaf\
│
├── index.php                  الصفحة الرئيسية (تتطلب دخول)
├── works.php                  معرض الأعمال (تتطلب دخول)
├── services.php               الخدمات والأسعار (تتطلب دخول)
├── contact.php                نموذج التواصل + واتساب (تتطلب دخول)
├── profile.php                الملف الشخصي للمستخدم العادي (تتطلب دخول)
├── login.php                  تسجيل الدخول (بلا شريط/تذييل)
├── register.php               إنشاء حساب (بلا شريط/تذييل)
├── logout.php                 تسجيل الخروج
├── schema.sql                 مخطط القاعدة + بيانات تجريبية
├── PROJECT_PLAN.md            هذا الملف (الدليل الكامل)
│
├── config\
│   └── db.php                 اتصال PDO + الدوال المساعدة العامة
│
├── includes\
│   ├── header.php             رأس الموقع + الشريط العلوي + الشعار
│   ├── footer.php             تذييل الموقع + سكربت Bootstrap
│   └── functions.php          التحقق + رفع الملفات + is_admin()
│
├── admin\
│   ├── index.php              لوحة التحكم (إحصائيات + مستخدمون + نشاط)
│   ├── user_edit.php          تعديل/حذف أي مستخدم (جديد)
│   ├── projects.php           إدارة المشاريع (بطاقات)
│   ├── project_add.php        إضافة مشروع
│   ├── project_edit.php       تعديل مشروع
│   ├── project_delete.php     تأكيد حذف مشروع
│   ├── services.php           إدارة الخدمات (بطاقات)
│   ├── messages.php           إدارة الرسائل
│   ├── profile.php            الملف الشخصي للمدير (بياناتي + كلمة المرور + إضافة مستخدم)
│   └── includes\
│       ├── header.php         شريط تنقل أفقي موحّد + <main>
│       └── footer.php         إغلاق <main> + Bootstrap JS
│
└── assets\
    ├── css\
    │   ├── bootstrap.rtl.min.css
    │   ├── all.min.css
    │   ├── tajawal.css
    │   ├── site-theme.css      ثيم الموقع العام (فاتح فخم) + أنماط الشعار والواتساب
    │   └── admin-theme.css     ثيم لوحة التحكم
    ├── js\
    │   └── bootstrap.bundle.min.js
    ├── fonts\                  خطوط Tajawal (woff2)
    ├── webfonts\               خطوط Font Awesome (woff2)
    ├── images\
    │   ├── securetech-logo.svg الشعار الرسمي (درع + عقد شبكة)
    │   └── profile-placeholder.svg
    └── uploads\
        └── projects\           صور المشاريع المرفوعة
```

---

## 5) شرح تفصيلي لكل ملف

### 5.1 الجذر — صفحات الموقع العامة

#### `index.php` — الصفحة الرئيسية
- **الغرض:** واجهة الموقع الرئيسية (Hero + نبذة + إحصائيات + بطاقات الحلول + مشاريع مميزة).
- **الحماية:** `if (!is_logged_in()) header('Location: login.php');`
- **يضبط:** `$page_title` و`$active_page = 'home'`.
- **يقسم إلى أقسام:**
  - قسم Hero: العنوان الرئيسي "نوفّر حلول أمن المعلومات وتقنية الشبكات بمعايير عالمية" + زرّا "استعراض الأعمال" و"طلب خدمة" + بطاقة البروفايل.
  - شريط إحصائيات: سنوات الخبرة (محسوبة `date('Y') - 2018`)، مشاريع منجزة، شهادات، عملاء.
  - "استكشف الحلول": ثلاث بطاقات (تطوير ويب / هندسة شبكات / أمن سيبراني).
  - "أعمالنا المميزة": يجلب حتى 6 مشاريع من جدول `projects` ويعرضها كبطاقات مع الصور والتقنيات.
- **قاعدة البيانات:** قراءة فقط من `projects` عبر Prepared Statement.
- **يعتمد على:** `includes/header.php` + `includes/footer.php`.

#### `works.php` — معرض الأعمال
- **الغرض:** عرض كل المشاريع من جدول `projects`.
- **الحماية:** تتطلب تسجيل دخول.
- **السلوك:** شبكة بطاقات مع صورة كل مشروع، الفئة، التقنيات، ووصف مختصر (`truncate`).
- **يضبط:** `$active_page = 'works'`.

#### `services.php` — الخدمات والأسعار
- **الغرض:** عرض الخدمات من جدول `services` بشكل بطاقات.
- **الحماية:** تتطلب تسجيل دخول.
- **السلوك:** لكل خدمة: أيقونة، اسم، وصف، السعر، وحالة ملوّنة:
  - `Available` → متاح (أخضر)، `Busy` → مشغول (سماوي)، `Completed` → مكتملة (رمادي).
- **يضبط:** `$active_page = 'services'`.

#### `contact.php` — صفحة التواصل  ⭐ محدّثة
- **الغرض:** نموذج تواصل + بيانات الاتصال + واتساب مباشر.
- **الحماية:** تتطلب تسجيل دخول + حماية CSRF للنموذج.
- **نموذج التواصل:** الحقول `sender_name`, `sender_email`, `subject`, `message`؛ تحقق كامل ثم `INSERT` في جدول `messages` بـ Prepared Statement.
- **بيانات الاتصال:** الموقع (صنعاء)، الهاتف `+967 771792570`، البريد `swaf8315@gmail.com`.
- **زر واتساب المباشر:** زر بارز في أعلى القسم:
  ```html
  <a href="https://wa.me/967771792570" target="_blank" rel="noopener" class="cta-button whatsapp-cta">
      <i class="fab fa-whatsapp"></i> راسلنا مباشرة على واتساب
  </a>
  ```
  وبطاقة واتساب كاملة قابلة للنقر في قسم البيانات.
- **يضبط:** `$active_page = 'contact'`.

#### `profile.php` — الملف الشخصي للمستخدم العادي  ⭐ جديد
- **الغرض:** يتيح للمستخدم العادي (role = user) إدارة حسابه.
- **الحماية:** `is_logged_in()` فقط.
- **الوظائف:**
  1. **تغيير اسم المستخدم:** حقل الاسم + كلمة المرور الحالية للتأكيد (`password_verify`)، مع منع التكرار.
  2. **تغيير كلمة المرور:** الحالية + الجديدة + التأكيد (6 أحرف على الأقل).
- **الرسائل:** يعرض تنبيهات نجاح/خطأ أعلى الصفحة.
- **ملاحظة:** متاح للأدمن أيضاً، لكن الأدمن يستخدم `admin/profile.php` الأكثر شمولاً.

#### `login.php` — تسجيل الدخول
- **الغرض:** مصادقة المستخدمين.
- **السلوك:** تحقق CSRF ← `SELECT` بالاسم أو البريد ← `password_verify` ← التحقق من `is_active` ← `session_regenerate_id(true)` ← تحديث `last_login` ← تخزين `user_id`/`username`/`role`.
- **التوجيه:** `admin` ⇒ `admin/index.php` ، `user` ⇒ `index.php`.
- **يخفي** الشريط والتذييل عبر `$hide_nav = true`.

#### `register.php` — إنشاء حساب
- **الغرض:** تسجيل مستخدم جديد (دور `user` دائماً).
- **السلوك:** تحقق + منع تكرار الاسم/البريد + `password_hash` + `INSERT` + تسجيل دخول تلقائي ثم التحويل للرئيسية.
- **يخفي** الشريط والتذييل عبر `$hide_nav = true`.

#### `logout.php` — تسجيل الخروج
- إتلاف الجلسة (`session_destroy`) وإعادة التوجيه إلى `login.php`.

#### `schema.sql` — مخطط القاعدة
- إنشاء قاعدة `alsawaf_db` والجداول `users`, `projects`, `services`, `messages` والعرض `unread_messages_count` مع بيانات تجريبية.

---

### 5.2 `config/` — الإعدادات والوظائف الأساسية

#### `config/db.php`
- **الغرض:** إنشاء اتصال PDO + تعريف الثوابت + الدوال المساعدة المستخدمة في كل الموقع.
- **الاتصال:** PDO مع `ATTR_ERRMODE = EXCEPTION` و`ATTR_EMULATE_PREPARES = false` و`charset=utf8mb4`.
- **الدوال العامة المُعرّفة:**
  | الدالة | الوظيفة |
  |---|---|
  | `h($text)` | تهريب المخرجات `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` |
  | `base_url()` | المسار الأساسي للمشروع |
  | `site_url($path)` | بناء رابط جذري `/swaf/...` |
  | `is_logged_in()` | هل الجلسة تحتوي `user_id` و`username` |
  | `get_user()` | جلب بيانات المستخدم الحالي من القاعدة |
  | `format_date($date, $format)` | تنسيق التواريخ |
  | `truncate($text, $len)` | قصّ النصوص الطويلة |
  | `generate_csrf_token()` | توليد رمز CSRF وتخزينه بالجلسة |
  | `validate_csrf_token($token)` | التحقق من رمز CSRF |
  | `sanitize_value($value)` | تنظيف القيم |
  | `is_admin()` | هل الدور `admin` |
- **يبدأ الجلسة** عبر `session_start()` إن لم تكن بدأت.

---

### 5.3 `includes/` — واجهة الموقع العامة

#### `includes/header.php`  ⭐ محدّث (شعار + اسم)
- **الغرض:** رأس صفحة الموقع: `<head>` + الشريط العلوي.
- **يحمّل** كل الأصول محلياً: Bootstrap RTL، Font Awesome، خط Tajawal، `site-theme.css`.
- **الشعار في الرأس:** أيقونة مصغّرة + الاسم بجانبها:
  ```html
  <a class="navbar-brand" href=".../index.php">
      <img src=".../assets/images/securetech-logo.svg" class="navbar-logo" alt="شعار سكيور تيك">
      <span class="brand-text">سكيور تيك (SecureTech)</span>
      <span class="brand-tag">| حلول أمن المعلومات وتقنية الشبكات</span>
  </a>
  ```
- **Favicon:** `<link rel="icon" type="image/svg+xml" href=".../securetech-logo.svg">`.
- **روابط التنقل:** الرئيسية / أعمالي / خدماتي / تواصل (مع تمييز الصفحة النشطة بـ `$active_page`).
- **القائمة المنسدلة (للمسجّلين):** الملف الشخصي، لوحة التحكم (للأدمن)، تسجيل الخروج.
- **الإخفاء:** كل الشريط داخل `if (!($hide_nav ?? false))`.
- **يفتح** `<main class="container my-5">`.

#### `includes/footer.php`
- **الغرض:** تذييل الموقع: الاسم والوصف، البريد `swaf8315@gmail.com`، الهاتف `+967 771792570`، روابط سريعة، حقوق النشر.
- **يحمّل** `assets/js/bootstrap.bundle.min.js`.
- **يُخفى** عند `$hide_nav = true`.

#### `includes/functions.php`
- **الغرض:** دوال التحقق والأمان ورفع الملفات (تُستخدم أساساً في لوحة التحكم).
- **الدوال:** `validate_required()`, `validate_email()`, `validate_text()`, `validate_numeric()`, `validate_category()`, `validate_tech_stack()`, `safe_file_upload()`, `delete_uploaded_file()`, `is_admin()`.

---

### 5.4 `admin/` — لوحة التحكم (دور admin فقط)

> كل صفحات هذا المجلد تتحقق أولاً من `is_logged_in()` ثم `is_admin()`، وإلا تحوّل إلى `../login.php` أو `../index.php`.

#### `admin/includes/header.php`  ⭐ شريط أفقي موحّد
- **الغرض:** رأس لوحة التحكم: `<head>` + `<main>` + شريط تنقل **أفقي** موحّد.
- **الروابط:** لوحة التحكم / إدارة المشاريع / الخدمات / الرسائل / الملف الشخصي.
- **تمييز النشط** تلقائياً عبر `basename($_SERVER['SCRIPT_NAME'])`.
- **يعرض** اسم المستخدم الحالي + زر تسجيل خروج.

#### `admin/includes/footer.php`
- إغلاق `<main>` + تحميل `bootstrap.bundle.min.js` + سكربتات مساعدة.

#### `admin/index.php` — لوحة التحكم  ⭐ محدّث (إدارة المستخدمين)
- **الغرض:** الصفحة الرئيسية للوحة: إحصائيات + جدول المستخدمين + نشاط أخير.
- **البطاقات الإحصائية:** إجمالي المستخدمين، المشاريع، الخدمات، الرسائل غير المقروءة.
- **جدول "مستخدمو الموقع":** اسم المستخدم، البريد، الدور، الحالة، آخر دخول، تاريخ التسجيل، و**الإجراءات**:
  - زر **تعديل** → `user_edit.php?id=X`.
  - زر **حذف** → POST إلى `user_edit.php` مع `action=delete` و`csrf_token` وتأكيد JS.
  - منع حذف الحساب الحالي (يظهر الرمز باهتاً).
- **زر "إضافة مستخدم"** → `profile.php#add-user`.
- **رسائل نجاح:** `?updated=1` و`?deleted=1`.

#### `admin/user_edit.php` — تعديل/حذف أي مستخدم  ⭐ جديد
- **الغرض:** تعديل بيانات أي مستخدم أو حذفه.
- **المُدخل:** `?id=X` أو `user_id` عبر POST.
- **إجراء `update`:** اسم المستخدم، البريد، الدور (`admin`/`user`)، الحالة (نشط/موقوف)، وكلمة مرور جديدة اختيارية (مع التأكيد).
- **إجراء `delete`:** حذف المستخدم بعد التحقق.
- **حراسات أمنية:**
  - لا يمكنك حذف حسابك الحالي.
  - لا يمكن حذف/إزالة صلاحية **آخر مدير نشط**.
  - منع تكرار اسم المستخدم/البريد.
  - الحد الأدنى لكلمة المرور 6 أحرف.
- **بعد النجاح:** تحويل إلى `index.php?updated=1` أو `?deleted=1`.

#### `admin/projects.php` — إدارة المشاريع
- نموذج إضافة سريع + شبكة بطاقات لكل المشاريع + حذف + عدّاد.
- فئات: تطوير ويب / هندسة شبكات / أمن سيبراني / Full-Stack.

#### `admin/project_add.php`
- نموذج إضافة مشروع كامل (عمودان) مع معاينة صورة مباشرة عبر JS.
- يستخدم `safe_file_upload()` لحفظ الصورة في `uploads/projects/`.

#### `admin/project_edit.php`
- نموذج تعديل مشروع موجود (بما فيه استبدال الصورة).

#### `admin/project_delete.php`
- صفحة تأكيد حذف المشروع مع POST محمي بـ CSRF.
- يحذف الصورة المرتبطة عبر `delete_uploaded_file()`.

#### `admin/services.php` — إدارة الخدمات
- إضافة/تعديل/حذف الخدمات.
- دعم وضع التعديل عبر `?edit_service=ID` (نموذج معبّأ).
- شبكة بطاقات + عدّاد بالحالات (متاح/مشغول/مكتملة).

#### `admin/messages.php` — إدارة الرسائل
- عرض الرسائل مع المرسل والموضوع والتاريخ.
- تعليم مقروء/غير مقروء + حذف.
- بطاقات إحصاء: غير المقروءة / المقروءة.

#### `admin/profile.php` — الملف الشخصي للمدير  ⭐ معاد كتابته
- **الغرض:** إدارة حساب المدير وإنشاء مستخدمين جدد.
- **ثلاثة أقسام:**
  1. **تعديل بياناتي:** اسم المستخدم + البريد (مع منع التكرار).
  2. **تغيير كلمة المرور:** الحالية + الجديدة + التأكيد (6 أحرف على الأقل، `password_verify`).
  3. **إضافة مستخدم جديد** (`id="add-user"`): اسم المستخدم، البريد، كلمة المرور + التأكيد، واختيار **الصلاحية** (مستخدم / مدير بصلاحيات كاملة).
- **بطاقة جانبية:** ملخص الحساب (البريد، تاريخ الإنشاء، آخر دخول).

---

### 5.5 `assets/` — الأصول الثابتة

| المسار | الوظيفة |
|---|---|
| `css/bootstrap.rtl.min.css` | Bootstrap 5 RTL محلي. |
| `css/all.min.css` + `webfonts/` | أيقونات Font Awesome محلية. |
| `css/tajawal.css` + `fonts/` | خط Tajawal محلي. |
| `css/site-theme.css` | ثيم الموقع العام (فاتح فخم) + أنماط الشعار (`.navbar-logo`, `.brand-text`, `.brand-tag`) + أنماط واتساب (`.whatsapp-cta`, `.contact-whatsapp`). |
| `css/admin-theme.css` | ثيم لوحة التحكم (كحلي/سماوي فخم). |
| `js/bootstrap.bundle.min.js` | سكربت Bootstrap محلي. |
| `images/securetech-logo.svg` | الشعار الرسمي: درع + عقد شبكة + قفل ذهبي مركزي. |
| `images/profile-placeholder.svg` | صورة افتراضية محلية. |
| `uploads/projects/` | صور المشاريع المرفوعة. |

#### تفصيل الشعار `securetech-logo.svg`
- **المفهوم:** درع (حماية) + شبكة عقد وخطوط (تقنية/شبكات) + قفل ذهبي في المركز (أمان).
- **الألوان:** تدرّج كحلي `#0F2440` → سماوي `#0E7C9B`، مع حدود وأقواس ذهبية `#F2D27B` → `#C9971C`.
- **العناصر:** هالة سماوية، مدار متقطع، ست عقد شبكية متصلة، مركز آمن ذهبي.
- **الاستخدام:** كأيقونة مصغّرة في رأس الصفحة (`.navbar-logo`) وكـ Favicon.
- **قابل للتوسّع:** SVG متجهي لا يفقد الجودة عند أي حجم.

---

## 6) التدفّقات الأساسية

### تسجيل الدخول

```text
login.php
  → التحقق من CSRF
  → SELECT user WHERE username/email = ?
  → password_verify()
  → التحقق من is_active
  → session_regenerate_id(true)
  → تخزين user_id / username / role
  → admin  ⇒ admin/index.php
  → user   ⇒ index.php
```

### حماية الصفحات

```php
// الصفحات العامة (index / works / services / contact / profile)
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// صفحات لوحة التحكم
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}
if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}
```

### إدارة المستخدمين (للمدير فقط)

```text
إضافة  : admin/profile.php  (action=add_user  + role=admin|user)
تعديل  : admin/user_edit.php (action=update)  ← username/email/role/is_active/password
حذف    : admin/user_edit.php (action=delete)  ← مع منع حذف النفس وآخر مدير
تعديل  : admin/index.php     ← جدول "مستخدمو الموقع" بأزرار مباشرة
```

### الملف الشخصي للمستخدم

```text
profile.php
  → تغيير اسم المستخدم (يتطلب كلمة المرور الحالية)
  → تغيير كلمة المرور (الحالية + الجديدة + التأكيد)
  → password_verify + password_hash
```

### عمليات CRUD

```text
إضافة / تعديل / حذف  ⇒  PDO Prepared Statements + تحقق CSRF
```

### رفع الصور

```php
safe_file_upload('project_image', ['image/jpeg','image/png','image/gif'], 2097152);
# يتحقق من MIME + الحجم (2MB) + يعيد تسمية الملف
# الحفظ في: uploads/projects/
# الحذف   : delete_uploaded_file($filename)
```

### التواصل المباشر عبر واتساب

```text
contact.php  →  https://wa.me/967771792570  (يفتح المحادثة مباشرة في تبويب جديد)
```

---

## 7) الهوية البصرية

| العنصر | القيمة |
|---|---|
| الاسم | سكيور تيك (SecureTech) |
| الوصف | حلول أمن المعلومات وتقنية الشبكات |
| اللون الكحلي | `#0F2440` |
| اللون السماوي | `#0E7C9B` |
| اللون الذهبي | `#F2D27B` → `#C9971C` |
| اللون الأخضر (واتساب) | `#25D366` → `#128C7E` |
| الخط | Tajawal |
| الشعار | درع + عقد شبكة + قفل ذهبي |

---

## 8) ملاحظات الأمان

- كل نماذج POST محمية برمز CSRF عبر `generate_csrf_token()` / `validate_csrf_token()`.
- كلمات المرور مخزّنة بـ bcrypt عبر `password_hash()` وتُتحقق بـ `password_verify()`.
- كل المخرجات تُهرّب عبر `h()` = `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- الاستعلامات كلها Prepared Statements مع `ATTR_EMULATE_PREPARES = false`.
- رفع الملفات يتحقق من نوع MIME الفعلي والحجم.
- إدارة المستخدمين تحمي من حذف الحساب الذاتي ومن إزالة آخر مدير.
- كل الأصول محلية — لا CDN خارجي ولا روابط خارجية.
- الحماية بالدخول على كل صفحات الموقع العامة.

---

## 9) تطويرات مستقبلية مقترحة

- استعادة كلمة المرور عبر البريد الإلكتروني.
- سجل تدقيق (Audit Log) لعمليات المستخدمين.
- رفع صور متعددة لكل مشروع.
- تصدير الرسائل / المستخدمين إلى CSV.
- دعم لغات متعددة (عربي / إنجليزي).
