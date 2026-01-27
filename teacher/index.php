<?php
// c:\xampp\htdocs\project\teacher\index.php
// Teacher Panel Entry Point

require_once __DIR__ . '/../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));

// Redirect to dashboard
redirect(base_url('teacher/dashboard.php'));
