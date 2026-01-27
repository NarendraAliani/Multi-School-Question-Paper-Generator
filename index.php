<?php
// c:\xampp\htdocs\project\index.php
// Landing Page - Redirect to appropriate dashboard or login

require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
if (is_logged_in()) {
    $role = get_user_role();
    
    // Redirect based on role
    if ($role === ROLE_SUPER_ADMIN) {
        redirect(base_url('admin/dashboard.php'));
    } elseif ($role === ROLE_SCHOOL_ADMIN) {
        redirect(base_url('school_admin/dashboard.php'));
    } elseif ($role === ROLE_TEACHER) {
        redirect(base_url('teacher/dashboard.php'));
    } else {
        // Unknown role, logout
        redirect(base_url('auth/logout.php'));
    }
} else {
    // Not logged in, redirect to login page
    redirect(base_url('auth/login.php'));
}
