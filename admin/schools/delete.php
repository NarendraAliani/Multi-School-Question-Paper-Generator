<?php
// admin/schools/delete.php
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));

$db = getDB(); $school_id = (int)($_GET["id"] ?? 0); $token = $_GET["token"] ?? "";
if (!validate_csrf_token($token)) { set_flash_message(MSG_ERROR, "Invalid token."); redirect(base_url("admin/schools/list.php")); }
$school = $db->selectOne("SELECT school_name FROM schools WHERE school_id = ?", [$school_id]);
if (!$school) { set_flash_message(MSG_ERROR, "Not found."); redirect(base_url("admin/schools/list.php")); }
$user_count = $db->count("users", "school_id = ?", [$school_id]);
if ($user_count > 0) { set_flash_message(MSG_ERROR, "Cannot delete - has users."); redirect(base_url("admin/schools/list.php")); }
$deleted = $db->delete("DELETE FROM schools WHERE school_id = ?", [$school_id]);
if ($deleted) { log_activity(get_user_id(), "delete", "school", $school_id, "Deleted"); set_flash_message(MSG_SUCCESS, "Deleted!"); }
else set_flash_message(MSG_ERROR, "Failed.");
redirect(base_url("admin/schools/list.php"));