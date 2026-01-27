<?php
// c:\xampp\htdocs\project\teacher\papers\index.php
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
redirect(base_url("teacher/papers/list.php"));