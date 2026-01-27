<?php
// c:\xampp\htdocs\project\teacher\questions\delete.php
// Delete Question

require_once __DIR__ . '/../../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));

$db = getDB();
$question_id = (int)($_GET['id'] ?? 0);
$token = $_GET['token'] ?? '';
$user_id = get_user_id();
$school_id = get_school_id();

if (!validate_csrf_token($token)) {
    set_flash_message(MSG_ERROR, 'Invalid security token.');
    redirect(base_url('teacher/questions/list.php'));
}

$question = $db->selectOne("SELECT question_image FROM questions WHERE question_id = ? AND school_id = ?", [$question_id, $school_id]);

if (!$question) {
    set_flash_message(MSG_ERROR, 'Question not found.');
    redirect(base_url('teacher/questions/list.php'));
}

$deleted = $db->delete("DELETE FROM questions WHERE question_id = ? AND school_id = ?", [$question_id, $school_id]);

if ($deleted) {
    if (!empty($question['question_image'])) {
        $file_path = QUESTIONS_UPLOAD_PATH . '/' . $question['question_image'];
        if (file_exists($file_path)) unlink($file_path);
    }
    log_activity($user_id, 'delete', 'question', $question_id, 'Deleted question');
    set_flash_message(MSG_SUCCESS, 'Question deleted successfully!');
} else {
    set_flash_message(MSG_ERROR, 'Failed to delete question.');
}

redirect(base_url('teacher/questions/list.php'));
