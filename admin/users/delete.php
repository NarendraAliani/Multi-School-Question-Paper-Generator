<?php
// admin/users/delete.php
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));
$db = getDB(); $user_id = (int)($_GET["id"] ?? 0); $token = $_GET["token"] ?? "";
if (!validate_csrf_token($token)) { set_flash_message(MSG_ERROR, "Invalid token."); redirect(base_url("admin/users/list.php")); }
if ($user_id == get_user_id()) { set_flash_message(MSG_ERROR, "Cannot delete yourself."); redirect(base_url("admin/users/list.php")); }
$deleted = $db->delete("DELETE FROM users WHERE user_id = ?", [$user_id]);
if ($deleted) set_flash_message(MSG_SUCCESS, "Deleted!");
else set_flash_message(MSG_ERROR, "Failed.");
redirect(base_url("admin/users/list.php"));