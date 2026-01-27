<?php
// c:\xampp\htdocs\project\includes\auth_check.php
// Authentication Check - Include this in protected pages

require_once __DIR__ . '/../config/session_config.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    set_flash_message(MSG_ERROR, 'Please login to access this page.');
    redirect(base_url('auth/login.php'));
    exit;
}

// Update last activity
set_session('last_activity', time());
