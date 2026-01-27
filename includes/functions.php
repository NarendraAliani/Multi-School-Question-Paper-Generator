<?php
// c:\xampp\htdocs\project\includes\functions.php
// Core Utility Functions - The Brain of the System

// Load required files
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../config/session_config.php';

// ============================================
// INPUT SANITIZATION & VALIDATION
// ============================================

/**
 * Sanitize user input to prevent XSS and injection attacks
 * @param mixed $data Input data
 * @return mixed Sanitized data
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    if (is_string($data)) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Validate email address
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (10 digits)
 * @param string $phone
 * @return bool
 */
function validate_phone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

/**
 * Validate password strength
 * @param string $password
 * @return array ['valid' => bool, 'message' => string]
 */
function validate_password($password) {
    $result = ['valid' => true, 'message' => ''];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $result['valid'] = false;
        $result['message'] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
        return $result;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = "Password must contain at least one uppercase letter.";
        return $result;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = "Password must contain at least one lowercase letter.";
        return $result;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $result['valid'] = false;
        $result['message'] = "Password must contain at least one number.";
        return $result;
    }
    
    return $result;
}

/**
 * Hash password securely
 * @param string $password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// ============================================
// CSRF TOKEN PROTECTION
// ============================================

/**
 * Generate CSRF token
 * @return string
 */
function generate_csrf_token() {
    if (!has_session(CSRF_TOKEN_NAME)) {
        set_session(CSRF_TOKEN_NAME, bin2hex(random_bytes(32)));
    }
    return get_session(CSRF_TOKEN_NAME);
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validate_csrf_token($token) {
    return has_session(CSRF_TOKEN_NAME) && hash_equals(get_session(CSRF_TOKEN_NAME), $token);
}

/**
 * Get CSRF token input field HTML
 * @return string
 */
function csrf_token_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

// ============================================
// PERMISSION & AUTHORIZATION
// ============================================

/**
 * Check if user has required permission/role
 * @param string|array $required_role Single role or array of roles
 * @return bool
 */
function check_permission($required_role) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_role = get_user_role();
    
    if (is_array($required_role)) {
        return in_array($user_role, $required_role);
    }
    
    return $user_role === $required_role;
}

/**
 * Require specific permission or redirect
 * @param string|array $required_role
 * @param string $redirect_url
 */
function require_permission($required_role, $redirect_url = '/auth/login.php') {
    if (!check_permission($required_role)) {
        set_flash_message(MSG_ERROR, 'Access denied. Insufficient permissions.');
        redirect($redirect_url);
        exit;
    }
}

/**
 * Check if user is super admin
 * @return bool
 */
function is_super_admin() {
    return check_permission(ROLE_SUPER_ADMIN);
}

/**
 * Check if user is school admin
 * @return bool
 */
function is_school_admin() {
    return check_permission(ROLE_SCHOOL_ADMIN);
}

/**
 * Check if user is teacher
 * @return bool
 */
function is_teacher() {
    return check_permission(ROLE_TEACHER);
}

// ============================================
// DEPENDENT DATA FETCHING (For AJAX Dropdowns)
// ============================================

/**
 * Generic function to get dependent data from database
 * @param string $table Table name
 * @param string $parent_column Parent column name
 * @param int $parent_id Parent ID value
 * @param array $columns Columns to select
 * @param string $where_clause Additional WHERE conditions
 * @return array Result set
 */
function get_dependent_data($table, $parent_column, $parent_id, $columns = ['*'], $where_clause = '') {
    $db = getDB();
    
    $select_columns = implode(', ', $columns);
    $query = "SELECT {$select_columns} FROM {$table} WHERE {$parent_column} = ?";
    
    if (!empty($where_clause)) {
        $query .= " AND {$where_clause}";
    }
    
    $query .= " ORDER BY display_order ASC, " . $columns[0] . " ASC";
    
    return $db->select($query, [$parent_id]);
}

/**
 * Get standards by board ID
 * @param int $board_id
 * @return array
 */
function get_standards_by_board($board_id) {
    return get_dependent_data('standards', 'board_id', $board_id, 
        ['standard_id', 'standard_name', 'standard_code'], "status = 'active'");
}

/**
 * Get subjects by standard ID
 * @param int $standard_id
 * @return array
 */
function get_subjects_by_standard($standard_id) {
    return get_dependent_data('subjects', 'standard_id', $standard_id, 
        ['subject_id', 'subject_name', 'subject_code'], "status = 'active'");
}

/**
 * Get chapters by subject ID
 * @param int $subject_id
 * @return array
 */
function get_chapters_by_subject($subject_id) {
    return get_dependent_data('chapters', 'subject_id', $subject_id, 
        ['chapter_id', 'chapter_name', 'chapter_number'], "status = 'active'");
}

/**
 * Get questions by filters
 * @param array $filters ['chapter_id', 'difficulty', 'marks', 'type', etc.]
 * @return array
 */
function get_questions_by_filters($filters) {
    $db = getDB();
    $query = "SELECT * FROM questions WHERE status = 'active'";
    $params = [];
    
    if (!empty($filters['chapter_id'])) {
        $query .= " AND chapter_id = ?";
        $params[] = $filters['chapter_id'];
    }
    
    if (!empty($filters['difficulty_level'])) {
        $query .= " AND difficulty_level = ?";
        $params[] = $filters['difficulty_level'];
    }
    
    if (!empty($filters['marks'])) {
        $query .= " AND marks = ?";
        $params[] = $filters['marks'];
    }
    
    if (!empty($filters['question_type'])) {
        $query .= " AND question_type = ?";
        $params[] = $filters['question_type'];
    }
    
    if (!empty($filters['school_id'])) {
        $query .= " AND school_id = ?";
        $params[] = $filters['school_id'];
    }
    
    return $db->select($query, $params);
}

// ============================================
// FILE UPLOAD HANDLING
// ============================================

/**
 * Handle image upload securely
 * @param array $file $_FILES array element
 * @param string $upload_dir Upload directory path
 * @param string $prefix Filename prefix
 * @return array ['success' => bool, 'filename' => string, 'message' => string]
 */
function upload_image($file, $upload_dir, $prefix = 'img') {
    $result = ['success' => false, 'filename' => '', 'message' => ''];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $result['message'] = 'No file uploaded.';
        return $result;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'File upload error.';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $result['message'] = 'File size exceeds maximum limit (' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB).';
        return $result;
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_IMAGE_EXTENSIONS)) {
        $result['message'] = 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_IMAGE_EXTENSIONS);
        return $result;
    }
    
    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_MIME_TYPES)) {
        $result['message'] = 'Invalid file format.';
        return $result;
    }
    
    // Create upload directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $destination = $upload_dir . '/' . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $result['success'] = true;
        $result['filename'] = $new_filename;
        $result['message'] = 'File uploaded successfully.';
    } else {
        $result['message'] = 'Failed to move uploaded file.';
    }
    
    return $result;
}

/**
 * Delete uploaded file
 * @param string $filepath
 * @return bool
 */
function delete_uploaded_file($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit;
    } else {
        echo "<script>window.location.href='{$url}';</script>";
        exit;
    }
}

/**
 * Get base URL
 * @return string
 */
function base_url($path = '') {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get assets URL
 * @param string $path
 * @return string
 */
function assets_url($path = '') {
    return ASSETS_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Format date
 * @param string $date
 * @param string $format
 * @return string
 */
function format_date($date, $format = DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 * @param string $datetime
 * @param string $format
 * @return string
 */
function format_datetime($datetime, $format = DATETIME_FORMAT) {
    return date($format, strtotime($datetime));
}

/**
 * Generate random string
 * @param int $length
 * @return string
 */
function generate_random_string($length = 10) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

/**
 * Generate unique code
 * @param string $prefix
 * @return string
 */
function generate_unique_code($prefix = 'CODE') {
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Check if request is AJAX
 * @return bool
 */
function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Send JSON response
 * @param bool $success
 * @param mixed $data
 * @param string $message
 */
function json_response($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Log activity
 * @param int $user_id
 * @param string $action
 * @param string $entity_type
 * @param int $entity_id
 * @param string $description
 */
function log_activity($user_id, $action, $entity_type, $entity_id = null, $description = '') {
    if (!ENABLE_ACTIVITY_LOG) {
        return;
    }
    
    $db = getDB();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $query = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $db->insert($query, [$user_id, $action, $entity_type, $entity_id, $description, $ip_address, $user_agent]);
}

/**
 * Get pagination data
 * @param int $total_records
 * @param int $current_page
 * @param int $records_per_page
 * @return array
 */
function get_pagination($total_records, $current_page = 1, $records_per_page = RECORDS_PER_PAGE) {
    $total_pages = ceil($total_records / $records_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_records' => $total_records,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'records_per_page' => $records_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Escape output for HTML
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Debug print (only in debug mode)
 * @param mixed $data
 */
function dd($data) {
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
}

/**
 * Get user full details by ID
 * @param int $user_id
 * @return array|false
 */
function get_user_by_id($user_id) {
    $db = getDB();
    $query = "SELECT * FROM users WHERE user_id = ?";
    return $db->selectOne($query, [$user_id]);
}

/**
 * Get school details by ID
 * @param int $school_id
 * @return array|false
 */
function get_school_by_id($school_id) {
    $db = getDB();
    $query = "SELECT * FROM schools WHERE school_id = ?";
    return $db->selectOne($query, [$school_id]);
}

/**
 * Check if username exists
 * @param string $username
 * @param int $exclude_user_id
 * @return bool
 */
function username_exists($username, $exclude_user_id = null) {
    $db = getDB();
    $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
    $params = [$username];
    
    if ($exclude_user_id) {
        $query .= " AND user_id != ?";
        $params[] = $exclude_user_id;
    }
    
    $result = $db->selectOne($query, $params);
    return $result && $result['count'] > 0;
}

/**
 * Check if email exists
 * @param string $email
 * @param int $exclude_user_id
 * @return bool
 */
function email_exists($email, $exclude_user_id = null) {
    $db = getDB();
    $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $params = [$email];
    
    if ($exclude_user_id) {
        $query .= " AND user_id != ?";
        $params[] = $exclude_user_id;
    }
    
    $result = $db->selectOne($query, $params);
    return $result && $result['count'] > 0;
}
