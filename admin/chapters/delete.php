<?php
// c:\xampp\htdocs\project\admin\chapters\delete.php
// Delete Chapter

require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

$db = getDB();
$chapter_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';

if (!validate_csrf_token($token)) {
    set_flash_message(MSG_ERROR, 'Invalid security token.');
    redirect(base_url('admin/chapters/list.php'));
}

$chapter = $db->selectOne("SELECT chapter_name FROM chapters WHERE chapter_id = ?", [$chapter_id]);

if (!$chapter) {
    set_flash_message(MSG_ERROR, 'Chapter not found.');
    redirect(base_url('admin/chapters/list.php'));
}

$deleted = $db->delete("DELETE FROM chapters WHERE chapter_id = ?", [$chapter_id]);

if ($deleted) {
    log_activity(get_user_id(), 'delete', 'chapter', $chapter_id, "Deleted chapter: {$chapter['chapter_name']}");
    set_flash_message(MSG_SUCCESS, 'Chapter deleted successfully!');
} else {
    set_flash_message(MSG_ERROR, 'Failed to delete chapter.');
}

redirect(base_url('admin/chapters/list.php'));
