<?php
// c:\xampp\htdocs\project\config\session_config.php
// Session Management Configuration

// Load constants
require_once __DIR__ . '/constants.php';

/**
 * Initialize secure session
 */
function init_session() {
    // Check if session already started
    if (session_status() === PHP_SESSION_NONE) {
        
        // Session configuration for security
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        
        // Set session name
        session_name(SESSION_NAME);
        
        // Set session timeout
        ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
        
        // Start session
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
    }
    
    return true;
}

/**
 * Destroy session and clear all data
 */
function destroy_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}

/**
 * Set session variable
 * @param string $key
 * @param mixed $value
 */
function set_session($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get session variable
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_session($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Check if session variable exists
 * @param string $key
 * @return bool
 */
function has_session($key) {
    return isset($_SESSION[$key]);
}

/**
 * Remove session variable
 * @param string $key
 */
function unset_session($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return has_session('user_id') && has_session('user_role');
}

/**
 * Get logged in user ID
 * @return int|null
 */
function get_user_id() {
    return get_session('user_id');
}

/**
 * Get logged in user role
 * @return string|null
 */
function get_user_role() {
    return get_session('user_role');
}

/**
 * Get logged in user school ID
 * @return int|null
 */
function get_school_id() {
    return get_session('school_id');
}

/**
 * Set flash message
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if flash message exists
 * @return bool
 */
function has_flash_message() {
    return isset($_SESSION['flash_message']);
}

// Initialize session automatically when this file is included
init_session();
