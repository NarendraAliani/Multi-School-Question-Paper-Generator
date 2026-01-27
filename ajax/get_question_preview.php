<?php
// c:\xampp\htdocs\project\ajax\get_question_preview.php
// AJAX endpoint to fetch question preview data

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$question_id = (int)($_GET['id'] ?? 0);
$school_id = get_school_id();

if (!$question_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid question ID']);
    exit;
}

$db = getDB();

// Fetch question with all related details
$query = "
    SELECT 
        q.*,
        ch.chapter_name,
        sub.subject_name,
        std.standard_name,
        b.board_name
    FROM questions q
    INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
    INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    INNER JOIN boards b ON std.board_id = b.board_id
    WHERE q.question_id = ? AND q.school_id = ? AND q.status = 'active'
";

$question = $db->selectOne($query, [$question_id, $school_id]);

if (!$question) {
    echo json_encode(['success' => false, 'message' => 'Question not found']);
    exit;
}

// Return question data
echo json_encode([
    'success' => true,
    'question_id' => $question['question_id'],
    'board_name' => $question['board_name'],
    'standard_name' => $question['standard_name'],
    'subject_name' => $question['subject_name'],
    'chapter_name' => $question['chapter_name'],
    'question_text' => htmlspecialchars($question['question_text']),
    'question_image' => $question['question_image'],
    'question_type' => $question['question_type'],
    'difficulty_level' => $question['difficulty_level'],
    'marks' => $question['marks'],
    'option_a' => htmlspecialchars($question['option_a'] ?? ''),
    'option_b' => htmlspecialchars($question['option_b'] ?? ''),
    'option_c' => htmlspecialchars($question['option_c'] ?? ''),
    'option_d' => htmlspecialchars($question['option_d'] ?? ''),
    'correct_answer' => htmlspecialchars($question['correct_answer'] ?? ''),
    'solution' => htmlspecialchars($question['explanation'] ?? '')
]);
