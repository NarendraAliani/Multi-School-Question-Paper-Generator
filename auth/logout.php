<?php
// c:\xampp\htdocs\project\auth\logout.php
// User Logout Handler

require_once __DIR__ . '/../includes/functions.php';

// Log activity before destroying session
if (is_logged_in()) {
    $user_id = get_user_id();
    log_activity($user_id, 'logout', 'user', $user_id, 'User logged out');
}

// Destroy session
destroy_session();

// Set flash message
set_flash_message(MSG_SUCCESS, 'You have been logged out successfully.');

// Redirect to login page
redirect(base_url('auth/login.php'));
