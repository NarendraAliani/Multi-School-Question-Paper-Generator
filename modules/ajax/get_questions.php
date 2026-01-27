<?php
// c:\xampp\htdocs\project\modules\ajax\get_questions.php
// AJAX Endpoint - Get Questions by Filters

require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    json_response(false, null, 'Unauthorized access');
}

// Get filters from request
$filters = [
    'chapter_id' => $_GET['chapter_id'] ?? null,
    'difficulty_level' => $_GET['difficulty_level'] ?? null,
    'marks' => $_GET['marks'] ?? null,
    'question_type' => $_GET['question_type'] ?? null,
    'school_id' => get_school_id()
];

// Remove empty filters
$filters = array_filter($filters, function($value) {
    return !empty($value);
});

$questions = get_questions_by_filters($filters);

json_response(true, $questions, 'Questions fetched successfully');
