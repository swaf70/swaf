<?php
/**
 * Helper & Sanitization Functions Module
 * Provides server-side input validation, sanitization, and utility functions
 * for the Eng. Mohammed Al-Sawaf portfolio application.
 * 
 * All functions follow OWASP security best practices.
 */

/**
 * Server-side input validation for required fields.
 * Checks if value is empty after trimming.
 *
 * @param mixed $value The value to validate
 * @param string $field_name Name of the field for error messages
 * @return bool True if field is not empty
 */
function validate_required($value, $field_name = 'field') {
    $value = trim($value);
    if (empty($value)) {
        return false;
    }
    return true;
}

/**
 * Server-side validation for email format.
 * Uses PHP filter_var with FILTER_VALIDATE_EMAIL.
 *
 * @param string $email The email to validate
 * @return bool True if email is valid format
 */
function validate_email($email) {
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

/**
 * Server-side validation for Arabic/English text.
 * Allows Arabic characters, Latin letters, numbers, and common punctuation.
 *
 * @param string $text The text to validate
 * @param int $max_length Maximum allowed length
 * @return bool True if text is valid
 */
function validate_text($text, $max_length = 255) {
    $text = trim($text);
    // Allow Arabic, English, numbers, and basic punctuation
    if (!preg_match('/^[\p{L}\p{N}\s\-\'\".,;:!?()@#$%^&*\/]+$/', $text)) {
        return false;
    }
    if (strlen($text) > $max_length) {
        return false;
    }
    return true;
}

/**
 * Server-side validation for numeric values.
 * Ensures value is a positive number (int or float).
 *
 * @param mixed $value The value to validate
 * @param bool $allow_float Whether to allow floating point numbers
 * @return bool True if valid numeric value
 */
function validate_numeric($value, $allow_float = false) {
    $value = trim($value);
    if ($allow_float) {
        return is_numeric($value);
    }
    return ctype_digit($value);
}

/**
 * Server-side validation for portfolio category.
 * Must be one of the allowed enum values.
 *
 * @param string $category The category to validate
 * @return bool True if category is valid
 */
function validate_category($category) {
    $allowed = ['Web Development', 'Network Architecture', 'Cybersecurity', 'Full-Stack'];
    return in_array($category, $allowed);
}

/**
 * Server-side validation for tech stack tags.
 * Allows comma-separated technologies.
 *
 * @param string $tech_stack The tech stack to validate
 * @return bool True if valid
 */
function validate_tech_stack($tech_stack) {
    if (empty(trim($tech_stack))) {
        return true; // optional field
    }
    $allowed_technologies = [
        'PHP', 'MySQL', 'Linux', 'Docker', 'Python', 
        'HTML5', 'CSS3', 'JavaScript', 'Bootstrap', 'React',
        'Node.js', 'Apache', 'Nginx', 'Git', 'AWS'
    ];
    
    $technologies = array_map('trim', explode(',', $tech_stack));
    foreach ($technologies as $tech) {
        $tech = trim($tech);
        if (!in_array($tech, $allowed_technologies)) {
            return false;
        }
    }
    return true;
}

/**
 * Safe file upload handler for project thumbnails.
 * Validates MIME type, file size, and renames the file.
 *
 * @param string $field_name The $_FILES field name
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Maximum file size in bytes (default 2MB)
 * @param string $upload_dir Upload directory
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function safe_file_upload($field_name, $allowed_types = null, $max_size = 2097152, $upload_dir = null) {
    // Initialize return array
    $result = ['success' => false, 'filename' => '', 'error' => ''];
    
    // Resolve upload directory relative to project root (works from any page)
    if ($upload_dir === null) {
        $upload_dir = __DIR__ . '/../uploads/projects/';
    }
    
    // Check if file was uploaded without errors
    if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'No file uploaded or upload error.';
        return $result;
    }
    
    $file_name = $_FILES[$field_name]['name'];
    $file_size = $_FILES[$field_name]['size'];
    $file_tmp  = $_FILES[$field_name]['tmp_name'];
    $file_type = $_FILES[$field_name]['type'];
    
    // Validate MIME type
    if ($allowed_types && !in_array($file_type, $allowed_types)) {
        $result['error'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types);
        return $result;
    }
    
    // Validate file size
    if ($file_size > $max_size) {
        $result['error'] = 'File size exceeds limit of ' . ($max_size / 1024 / 1024) . 'MB';
        return $result;
    }
    
    // Validate that it's actually an image
    $info = getimagesize($file_tmp);
    if ($info === false) {
        $result['error'] = 'File is not a valid image.';
        return $result;
    }
    
    // Generate unique filename with timestamp and original extension
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_filename = 'proj_' . time() . '_' . uniqid() . '.' . $ext;
    $destination = $upload_dir . $new_filename;
    
    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $destination)) {
        $result['success'] = true;
        $result['filename'] = $new_filename;
    } else {
        $result['error'] = 'Failed to move uploaded file to destination.';
    }
    
    return $result;
}

/**
 * Delete uploaded file thumbnail.
 *
 * @param string $filename The filename to delete
 * @param string $upload_dir Upload directory
 * @return bool Whether deletion was successful
 */
function delete_uploaded_file($filename, $upload_dir = null) {
    if (empty($filename)) {
        return false;
    }
    if ($upload_dir === null) {
        $upload_dir = __DIR__ . '/../uploads/projects/';
    }
    $path = $upload_dir . $filename;
    if (file_exists($path)) {
        return unlink($path);
    }
    return false;
}

/**
 * Get MySQLi/PDO error message in Arabic/English.
 *
 * @param PDO|mysqli $conn Database connection
 * @return string Error message
 */
function db_error($conn) {
    if ($conn instanceof PDO) {
        return $conn->errorInfo[2];
    }
    return mysqli_error($conn);
}

/**
 * Check if user has admin role.
 *
 * @return bool Whether current user is admin
 */
function is_admin() {
    return is_logged_in() && ($_SESSION['role'] ?? '') === 'admin';
}