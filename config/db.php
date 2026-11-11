<?php
/**
 * Database Configuration Module
 * Provides PDO database connection with prepared statement support
 * 
 * Security: Uses PDO with exception error mode for strict error handling.
 * Connection uses UTF-8 charset for proper Arabic/Unicode support.
 */

const DB_HOST = 'localhost';
const DB_NAME = 'alsawaf_db';
const DB_CHARSET = 'utf8mb4';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';

try {
    // Build PDO connection string with UTF-8 charset
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // PDO options for strict mode and error handling
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false, // Disable emulated prepares for real prepared statements
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    // Create PDO instance
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    
    // Ensure default timezone is set
    date_default_timezone_set('Asia/Aden');
    
} catch (PDOException $e) {
    // In production, log error instead of displaying
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

/**
 * Sanitize single value for use in PDO prepared statements.
 * Wrapper to ensure proper type handling.
 *
 * @param mixed $value The value to sanitize
 * @return mixed Sanitized value
 */
function sanitize_value($value) {
    return is_string($value) ? trim($value) : $value;
}

/**
 * Generate a CSRF token if not already set.
 * Should be called at the start of form processing.
 *
 * @param string $name Session token name
 * @return string The CSRF token
 */
function generate_csrf_token($name = 'csrf_token') {
    if (isset($_SESSION[$name])) {
        return $_SESSION[$name];
    }
    $_SESSION[$name] = bin2hex(random_bytes(32));
    return $_SESSION[$name];
}

/**
 * Validate CSRF token from POST data against session token.
 *
 * @param string $token The CSRF token from form data
 * @param string $name Session token name
 * @return bool Whether the token is valid
 */
function validate_csrf_token($token, $name = 'csrf_token') {
    if (!isset($_SESSION[$name])) {
        return false;
    }
    return hash_equals($_SESSION[$name], $token);
}

/**
 * Safe output escaping for HTML context.
 * Uses htmlspecialchars with ENT_QUOTES and UTF-8 encoding.
 *
 * @param string $data The data to escape
 * @return string Escaped data
 */
function h($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Compute the site's base URL path (root-relative).
 * Detects the app folder under the document root so links always
 * resolve correctly regardless of page depth or deployment path.
 *
 * @return string e.g. '' when site is at web root, or '/swaf' when in a subfolder
 */
function base_url() {
    static $base = null;
    if ($base === null) {
        $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        $appRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
        $base = str_replace($docRoot, '', $appRoot);
        $base = rtrim($base, '/');
    }
    return $base;
}

/**
 * Root-relative URL for a path relative to the app root.
 * Deterministic links that can never resolve to an external site.
 *
 * @param string $path e.g. 'works.php' or 'admin/index.php'
 * @return string e.g. '/swaf/works.php'
 */
function site_url($path = '') {
    return base_url() . '/' . ltrim($path, '/');
}

/**
 * Format Arabic/English date for display.
 *
 * @param string $date MySQL datetime string
 * @param string $format PHP date format
 * @return string Formatted date string
 */
function format_date($date, $format = 'M d, Y h:i A') {
    if (empty($date)) {
        return '-';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    return date($format, $timestamp);
}

/**
 * Check if user is logged in via session.
 *
 * @return bool Whether user is authenticated
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Get current logged-in user data.
 *
 * @param PDO $pdo Database connection
 * @return array|bool User data or false if not logged in
 */
function get_user(PDO $pdo) {
    if (!is_logged_in()) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Truncate text to specified length.
 *
 * @param string $text The text to truncate
 * @param int $length Maximum length
 * @param string $suffix Truncation suffix
 * @return string Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}