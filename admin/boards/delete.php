<?php
// c:\xampp\htdocs\project\admin\boards\delete.php
// Delete Board (Redirect Handler)

require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

$db = getDB();
$board_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

// Validate CSRF token
if (!validate_csrf_token($token)) {
    set_flash_message(MSG_ERROR, 'Invalid security token.');
    redirect(base_url('admin/boards/list.php'));
}

// Get board info
$board = $db->selectOne("SELECT board_name FROM boards WHERE board_id = ?", [$board_id]);

if (!$board) {
    set_flash_message(MSG_ERROR, 'Board not found.');
    redirect(base_url('admin/boards/list.php'));
}

// Delete board
$deleted = $db->delete("DELETE FROM boards WHERE board_id = ?", [$board_id]);

if ($deleted) {
    log_activity(get_user_id(), 'delete', 'board', $board_id, "Deleted board: {$board['board_name']}");
    set_flash_message(MSG_SUCCESS, 'Board deleted successfully!');
} else {
    set_flash_message(MSG_ERROR, 'Failed to delete board. It may have associated data.');
}

redirect(base_url('admin/boards/list.php'));
