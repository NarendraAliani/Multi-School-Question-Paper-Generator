<?php
// c:\xampp\htdocs\project\ajax\get_chapter_difficulty_stats.php
// AJAX endpoint to get difficulty distribution stats for a chapter

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$chapter_id = (int)($_GET['chapter_id'] ?? 0);
$school_id = get_school_id();

if (!$chapter_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid chapter ID']);
    exit;
}

$db = getDB();

// Get difficulty distribution
$stats = [
    'easy' => $db->count('questions', "chapter_id = ? AND school_id = ? AND difficulty_level = 'easy' AND status = 'active'", [$chapter_id, $school_id]),
    'medium' => $db->count('questions', "chapter_id = ? AND school_id = ? AND difficulty_level = 'medium' AND status = 'active'", [$chapter_id, $school_id]),
    'hard' => $db->count('questions', "chapter_id = ? AND school_id = ? AND difficulty_level = 'hard' AND status = 'active'", [$chapter_id, $school_id])
];

$total = $stats['easy'] + $stats['medium'] + $stats['hard'];

// Calculate balance status
$balance_status = 'good';
$recommendations = [];

if ($total > 0) {
    $easy_percent = ($stats['easy'] / $total) * 100;
    $medium_percent = ($stats['medium'] / $total) * 100;
    $hard_percent = ($stats['hard'] / $total) * 100;
    
    // Ideal distribution: 40% easy, 40% medium, 20% hard
    if ($easy_percent < 20) {
        $balance_status = 'warning';
        $recommendations[] = "Add more Easy questions (Current: {$stats['easy']}, Target: " . ceil($total * 0.4) . ")";
    }
    
    if ($medium_percent < 20) {
        $balance_status = 'warning';
        $recommendations[] = "Add more Medium questions (Current: {$stats['medium']}, Target: " . ceil($total * 0.4) . ")";
    }
    
    if ($hard_percent < 10) {
        $balance_status = 'warning';
        $recommendations[] = "Add more Hard questions (Current: {$stats['hard']}, Target: " . ceil($total * 0.2) . ")";
    }
    
    if ($hard_percent > 50) {
        $balance_status = 'danger';
        $recommendations[] = "Too many Hard questions. Balance with easier questions.";
    }
    
    if ($easy_percent > 70) {
        $balance_status = 'danger';
        $recommendations[] = "Too many Easy questions. Add more challenging questions.";
    }
} else {
    $balance_status = 'danger';
    $recommendations[] = "No questions found for this chapter. Please add questions.";
}

echo json_encode([
    'success' => true,
    'total' => $total,
    'easy' => $stats['easy'],
    'medium' => $stats['medium'],
    'hard' => $stats['hard'],
    'balance_status' => $balance_status,
    'recommendations' => $recommendations
]);
