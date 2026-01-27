<?php
// c:\xampp\htdocs\project\teacher\questions\import.php
// Teacher Questions Management - CSV Bulk Import

$_page_title = "Import Questions";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();

$errors = [];
$success_count = 0;
$failed_rows = [];

// Handle CSV upload and parsing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message(MSG_ERROR, 'Invalid request. Please try again.');
        redirect(base_url('teacher/questions/import.php'));
    }

    $file = $_FILES['csv_file'];

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed. Please try again.';
    } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        $errors[] = 'Only CSV files are allowed.';
    } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        $errors[] = 'File size must not exceed 5MB.';
    } else {
        // Parse CSV
        $handle = fopen($file['tmp_name'], 'r');
        
        if ($handle === false) {
            $errors[] = 'Unable to read the CSV file.';
        } else {
            // Read header row
            $header = fgetcsv($handle);
            $expected_columns = ['board_name', 'standard_name', 'subject_name', 'chapter_name', 'question_text', 'question_type', 'difficulty_level', 'marks', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'solution'];
            
            if ($header !== $expected_columns) {
                $errors[] = 'CSV format is incorrect. Expected columns: ' . implode(', ', $expected_columns);
            } else {
                $row_number = 1;
                $db->beginTransaction();
                
                try {
                    while (($row = fgetcsv($handle)) !== false) {
                        $row_number++;
                        
                        // Skip empty rows
                        if (empty(array_filter($row))) continue;
                        
                        // Map row data
                        $data = array_combine($expected_columns, $row);
                        
                        // Validate and fetch IDs
                        $board = $db->selectOne("SELECT board_id FROM boards WHERE board_name = ? AND status = 'active'", [trim($data['board_name'])]);
                        if (!$board) {
                            $failed_rows[] = "Row $row_number: Board '{$data['board_name']}' not found.";
                            continue;
                        }
                        
                        $standard = $db->selectOne("SELECT standard_id FROM standards WHERE standard_name = ? AND board_id = ? AND status = 'active'", [trim($data['standard_name']), $board['board_id']]);
                        if (!$standard) {
                            $failed_rows[] = "Row $row_number: Standard '{$data['standard_name']}' not found for board '{$data['board_name']}'.";
                            continue;
                        }
                        
                        $subject = $db->selectOne("SELECT subject_id FROM subjects WHERE subject_name = ? AND standard_id = ? AND status = 'active'", [trim($data['subject_name']), $standard['standard_id']]);
                        if (!$subject) {
                            $failed_rows[] = "Row $row_number: Subject '{$data['subject_name']}' not found for standard '{$data['standard_name']}'.";
                            continue;
                        }
                        
                        $chapter = $db->selectOne("SELECT chapter_id FROM chapters WHERE chapter_name = ? AND subject_id = ? AND status = 'active'", [trim($data['chapter_name']), $subject['subject_id']]);
                        if (!$chapter) {
                            $failed_rows[] = "Row $row_number: Chapter '{$data['chapter_name']}' not found for subject '{$data['subject_name']}'.";
                            continue;
                        }
                        
                        // Validate question type
                        $valid_types = ['mcq', 'short_answer', 'long_answer'];
                        if (!in_array(trim($data['question_type']), $valid_types)) {
                            $failed_rows[] = "Row $row_number: Invalid question type '{$data['question_type']}'. Must be: " . implode(', ', $valid_types);
                            continue;
                        }
                        
                        // Validate difficulty
                        $valid_difficulties = ['easy', 'medium', 'hard'];
                        if (!in_array(trim($data['difficulty_level']), $valid_difficulties)) {
                            $failed_rows[] = "Row $row_number: Invalid difficulty '{$data['difficulty_level']}'. Must be: " . implode(', ', $valid_difficulties);
                            continue;
                        }
                        
                        // Validate marks
                        if (!is_numeric(trim($data['marks'])) || trim($data['marks']) <= 0) {
                            $failed_rows[] = "Row $row_number: Invalid marks value '{$data['marks']}'.";
                            continue;
                        }
                        
                        // Insert question
                        $insert_data = [
                            'school_id' => $school_id,
                            'chapter_id' => $chapter['chapter_id'],
                            'question_text' => trim($data['question_text']),
                            'question_type' => trim($data['question_type']),
                            'difficulty_level' => trim($data['difficulty_level']),
                            'marks' => (float)trim($data['marks']),
                            'option_a' => trim($data['question_type']) === 'mcq' ? trim($data['option_a']) : null,
                            'option_b' => trim($data['question_type']) === 'mcq' ? trim($data['option_b']) : null,
                            'option_c' => trim($data['question_type']) === 'mcq' ? trim($data['option_c']) : null,
                            'option_d' => trim($data['question_type']) === 'mcq' ? trim($data['option_d']) : null,
                            'correct_answer' => trim($data['question_type']) === 'mcq' ? trim($data['correct_answer']) : null,
                            'explanation' => !empty(trim($data['solution'])) ? trim($data['solution']) : null,
                            'created_by' => $user_id,
                            'status' => 'active'
                        ];
                        
                        $question_id = $db->insert('questions', $insert_data);
                        
                        if ($question_id) {
                            $success_count++;
                        } else {
                            $failed_rows[] = "Row $row_number: Database insertion failed.";
                        }
                    }
                    
                    $db->commit();
                    log_activity($user_id, 'import', 'questions', 0, "Imported $success_count questions via CSV");
                    
                    if ($success_count > 0) {
                        set_flash_message(MSG_SUCCESS, "$success_count questions imported successfully!");
                    }
                    
                } catch (Exception $e) {
                    $db->rollBack();
                    $errors[] = 'Import failed: ' . $e->getMessage();
                }
            }
            
            fclose($handle);
        }
    }
}

// Get stats for help section
$boards = $db->select("SELECT board_name FROM boards WHERE status = 'active' ORDER BY board_name LIMIT 5");
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-file-import"></i> Import Questions</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/questions/list.php'); ?>">Questions</a></li>
                                <li class="breadcrumb-item active">Import</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle"></i> Errors</h5>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($failed_rows)): ?>
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-circle"></i> Failed Rows (<?php echo count($failed_rows); ?>)</h5>
            <ul style="max-height: 200px; overflow-y: auto;">
                <?php foreach ($failed_rows as $failure): ?>
                    <li><?php echo e($failure); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success_count > 0): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Successfully imported <strong><?php echo $success_count; ?></strong> questions!
            <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-sm btn-success ms-3">View Questions</a>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-upload"></i> Upload CSV File
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Select CSV File</label>
                            <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                            <small class="form-text text-muted">Maximum file size: 5MB</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload and Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> CSV Format Guide
                </div>
                <div class="card-body">
                    <p><strong>Required Columns (in exact order):</strong></p>
                    <ol style="font-size: 0.9em;">
                        <li>board_name</li>
                        <li>standard_name</li>
                        <li>subject_name</li>
                        <li>chapter_name</li>
                        <li>question_text</li>
                        <li>question_type <em>(mcq, short_answer, long_answer)</em></li>
                        <li>difficulty_level <em>(easy, medium, hard)</em></li>
                        <li>marks</li>
                        <li>option_a</li>
                        <li>option_b</li>
                        <li>option_c</li>
                        <li>option_d</li>
                        <li>correct_answer <em>(A, B, C, or D)</em></li>
                        <li>solution <em>(optional)</em></li>
                    </ol>
                    
                    <hr>
                    
                    <p><strong>Tips:</strong></p>
                    <ul style="font-size: 0.9em;">
                        <li>Use exact names from your database</li>
                        <li>For non-MCQ questions, leave option columns empty</li>
                        <li>Ensure all chapters exist before importing</li>
                        <li>Test with a small file first</li>
                    </ul>

                    <a href="<?php echo base_url('assets/sample_questions.csv'); ?>" class="btn btn-sm btn-info" download>
                        <i class="fas fa-download"></i> Download Sample CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
