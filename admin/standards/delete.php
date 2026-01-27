<?php
// c:\xampp\htdocs\project\admin\standards\delete.php
// Delete Standard (Redirect Handler)

require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

$db = getDB();
$standard_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

// Validate CSRF token
if (!validate_csrf_token($token)) {
    set_flash_message(MSG_ERROR, 'Invalid security token.');
    redirect(base_url('admin/standards/list.php'));
}

// Get standard info
$standard = $db->selectOne("SELECT standard_name FROM standards WHERE standard_id = ?", [$standard_id]);

if (!$standard) {
    set_flash_message(MSG_ERROR, 'Standard not found.');
    redirect(base_url('admin/standards/list.php'));
}

// Delete standard
$deleted = $db->delete("DELETE FROM standards WHERE standard_id = ?", [$standard_id]);

if ($deleted) {
    log_activity(get_user_id(), 'delete', 'standard', $standard_id, "Deleted standard: {$standard['standard_name']}");
    set_flash_message(MSG_SUCCESS, 'Standard deleted successfully!');
} else {
    set_flash_message(MSG_ERROR, 'Failed to delete standard. It may have associated subjects.');
}

redirect(base_url('admin/standards/list.php'));
