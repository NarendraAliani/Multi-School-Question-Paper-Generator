<?php
// school_admin/index.php
require_once __DIR__ . "/../includes/auth_check.php";
require_permission(ROLE_SCHOOL_ADMIN, base_url("auth/login.php"));
redirect(base_url("school_admin/dashboard.php"));