<?php
// c:\xampp\htdocs\project\modules\ajax\get_subjects.php
// AJAX Endpoint - Get Subjects by Standard ID

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(false, null, 'Unauthorized access');
}

if (!isset($_GET['standard_id']) || empty($_GET['standard_id'])) {
    json_response(false, null, 'Standard ID is required');
}

$standard_id = (int)$_GET['standard_id'];
$subjects = get_subjects_by_standard($standard_id);

json_response(true, $subjects, 'Subjects fetched successfully');
