<?php
// c:\xampp\htdocs\project\admin\subjects\delete.php
// Delete Subject (Redirect Handler)

require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

$db = getDB();
$subject_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

// Validate CSRF token
if (!validate_csrf_token($token)) {
    set_flash_message(MSG_ERROR, 'Invalid security token.');
    redirect(base_url('admin/subjects/list.php'));
}

// Get subject info
$subject = $db->selectOne("SELECT subject_name FROM subjects WHERE subject_id = ?", [$subject_id]);

if (!$subject) {
    set_flash_message(MSG_ERROR, 'Subject not found.');
    redirect(base_url('admin/subjects/list.php'));
}

// Delete subject
$deleted = $db->delete("DELETE FROM subjects WHERE subject_id = ?", [$subject_id]);

if ($deleted) {
    log_activity(get_user_id(), 'delete', 'subject', $subject_id, "Deleted subject: {$subject['subject_name']}");
    set_flash_message(MSG_SUCCESS, 'Subject deleted successfully!');
} else {
    set_flash_message(MSG_ERROR, 'Failed to delete subject. It may have associated chapters.');
}

redirect(base_url('admin/subjects/list.php'));
