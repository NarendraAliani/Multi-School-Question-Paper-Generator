<?php
// c:\xampp\htdocs\project\teacher\blueprints\delete.php
// Delete Paper Blueprint

require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();

$blueprint_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

if (!$blueprint_id) {
    set_flash_message(MSG_ERROR, 'Invalid blueprint.');
    redirect(base_url('teacher/blueprints/list.php'));
}

if (!validate_csrf_token($token)) {
    set_flash_message(MSG_ERROR, 'Invalid security token.');
    redirect(base_url('teacher/blueprints/list.php'));
}

// Ensure blueprint belongs to this school
$blueprint = $db->selectOne("SELECT * FROM paper_blueprints WHERE blueprint_id = ? AND school_id = ?", [$blueprint_id, $school_id]);

if (!$blueprint) {
    set_flash_message(MSG_ERROR, 'Blueprint not found.');
    redirect(base_url('teacher/blueprints/list.php'));
}

$deleted = $db->delete("DELETE FROM paper_blueprints WHERE blueprint_id = ?", [$blueprint_id]);

if ($deleted) {
    log_activity($user_id, 'delete', 'blueprint', $blueprint_id, 'Deleted blueprint: ' . $blueprint['blueprint_name']);
    set_flash_message(MSG_SUCCESS, 'Blueprint deleted successfully.');
} else {
    set_flash_message(MSG_ERROR, 'Failed to delete blueprint.');
}

redirect(base_url('teacher/blueprints/list.php'));
