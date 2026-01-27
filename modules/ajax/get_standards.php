<?php
// c:\xampp\htdocs\project\modules\ajax\get_standards.php
// AJAX Endpoint - Get Standards by Board ID

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(false, null, 'Unauthorized access');
}

if (!isset($_GET['board_id']) || empty($_GET['board_id'])) {
    json_response(false, null, 'Board ID is required');
}

$board_id = (int)$_GET['board_id'];
$standards = get_standards_by_board($board_id);

json_response(true, $standards, 'Standards fetched successfully');
