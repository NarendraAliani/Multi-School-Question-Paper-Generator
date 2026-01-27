<?php
// c:\xampp\htdocs\project\admin\index.php
// Admin Panel Entry Point

require_once __DIR__ . '/../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

// Redirect to dashboard
redirect(base_url('admin/dashboard.php'));
