<?php
// c:\xampp\htdocs\project\modules\ajax\get_chapters.php
// AJAX Endpoint - Get Chapters by Subject ID

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(false, null, 'Unauthorized access');
}

if (!isset($_GET['subject_id']) || empty($_GET['subject_id'])) {
    json_response(false, null, 'Subject ID is required');
}

$subject_id = (int)$_GET['subject_id'];
$chapters = get_chapters_by_subject($subject_id);

json_response(true, $chapters, 'Chapters fetched successfully');
