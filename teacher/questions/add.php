<?php
// c:\xampp\htdocs\project\teacher\questions\add.php
// Add New Question with Image Upload

$_page_title = "Add Question";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$errors = [];
$user_id = get_user_id();
$school_id = get_school_id();

$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token.';
    }
    
    $board_id = (int)($_POST['board_id'] ?? 0);
    $standard_id = (int)($_POST['standard_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $chapter_id = (int)($_POST['chapter_id'] ?? 0);
    $question_text = sanitize_input($_POST['question_text'] ?? '');
    $question_type = sanitize_input($_POST['question_type'] ?? 'short_answer');
    $difficulty = sanitize_input($_POST['difficulty'] ?? 'medium');
    $marks = (float)($_POST['marks'] ?? 1);
    $time_minutes = (int)($_POST['time_minutes'] ?? 0);
    $option_a = sanitize_input($_POST['option_a'] ?? '');
    $option_b = sanitize_input($_POST['option_b'] ?? '');
    $option_c = sanitize_input($_POST['option_c'] ?? '');
    $option_d = sanitize_input($_POST['option_d'] ?? '');
    $correct_answer = sanitize_input($_POST['correct_answer'] ?? '');
    $explanation = sanitize_input($_POST['explanation'] ?? '');
    $tags = sanitize_input($_POST['tags'] ?? '');
    
    if (empty($board_id) || empty($standard_id) || empty($subject_id) || empty($chapter_id)) {
        $errors[] = 'Please select board, standard, subject, and chapter.';
    }
    if (empty($question_text)) {
        $errors[] = 'Question text is required.';
    }
    if ($marks <= 0) {
        $errors[] = 'Marks must be greater than 0.';
    }
    
    // Handle file upload
    $question_image = '';
    if (!empty($_FILES['question_image']['name']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_image($_FILES['question_image'], QUESTIONS_UPLOAD_PATH, 'q_' . $user_id);
        if (!$upload['success']) {
            $errors[] = $upload['message'];
        } else {
            $question_image = $upload['filename'];
        }
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO questions 
                  (school_id, chapter_id, created_by, question_text, question_image, question_type, 
                   difficulty_level, marks, time_minutes, option_a, option_b, option_c, option_d, 
                   correct_answer, explanation, tags, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $question_id = $db->insert($query, [
            $school_id, $chapter_id, $user_id, $question_text, $question_image, $question_type,
            $difficulty, $marks, $time_minutes, $option_a, $option_b, $option_c, $option_d,
            $correct_answer, $explanation, $tags
        ]);
        
        if ($question_id) {
            log_activity($user_id, 'create', 'question', $question_id, 'Created question');
            set_flash_message(MSG_SUCCESS, 'Question added successfully!');
            redirect(base_url('teacher/questions/list.php'));
        } else {
            $errors[] = 'Failed to add question.';
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Question</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/questions/list.php'); ?>">Questions</a></li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    <ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <?php echo csrf_token_field(); ?>
                
                <!-- Hierarchy Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-sitemap"></i> Select Hierarchy
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Board <span class="text-danger">*</span></label>
                                <select class="form-select" id="board_id" name="board_id" required>
                                    <option value="">Select Board</option>
                                    <?php foreach ($boards as $b): ?>
                                        <option value="<?php echo $b['board_id']; ?>" <?php echo (isset($_POST['board_id']) && $_POST['board_id'] == $b['board_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($b['board_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Standard <span class="text-danger">*</span></label>
                                <select class="form-select" id="standard_id" name="standard_id" required disabled>
                                    <option value="">Select Board First</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-select" id="subject_id" name="subject_id" required disabled>
                                    <option value="">Select Standard First</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Chapter <span class="text-danger">*</span></label>
                                <select class="form-select" id="chapter_id" name="chapter_id" required disabled>
                                    <option value="">Select Subject First</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-question-circle"></i> Question Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Question Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="question_type" name="question_type">
                                    <option value="short_answer" <?php echo (!isset($_POST['question_type']) || $_POST['question_type'] == 'short_answer') ? 'selected' : ''; ?>>Short Answer</option>
                                    <option value="long_answer" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'long_answer') ? 'selected' : ''; ?>>Long Answer</option>
                                    <option value="mcq" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'mcq') ? 'selected' : ''; ?>>MCQ</option>
                                    <option value="true_false" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'true_false') ? 'selected' : ''; ?>>True/False</option>
                                    <option value="fill_blank" <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'fill_blank') ? 'selected' : ''; ?>>Fill in Blank</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Difficulty <span class="text-danger">*</span></label>
                                <select class="form-select" name="difficulty">
                                    <option value="easy" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] == 'easy') ? 'selected' : ''; ?>>Easy</option>
                                    <option value="medium" <?php echo (!isset($_POST['difficulty']) || $_POST['difficulty'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="hard" <?php echo (isset($_POST['difficulty']) && $_POST['difficulty'] == 'hard') ? 'selected' : ''; ?>>Hard</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Marks <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="marks" value="<?php echo e($_POST['marks'] ?? 1); ?>" min="0.5" step="0.5" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Time (minutes)</label>
                                <input type="number" class="form-control" name="time_minutes" value="<?php echo e($_POST['time_minutes'] ?? ''); ?>" min="0">
                            </div>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-3">
                            <label class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="question_text" rows="3" required placeholder="Type your question here..."><?php echo e($_POST['question_text'] ?? ''); ?></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label class="form-label">Question Image (Optional)</label>
                            <input type="file" class="form-control" name="question_image" accept="image/*">
                            <small class="text-muted">Upload image if question contains diagrams or complex formulas (Max 5MB, JPG/PNG/GIF)</small>
                        </div>

                        <!-- MCQ Options (shown only for MCQ type) -->
                        <div id="mcq-options" style="display: <?php echo (isset($_POST['question_type']) && $_POST['question_type'] == 'mcq') ? 'block' : 'none'; ?>;">
                            <div class="card bg-light">
                                <div class="card-header">MCQ Options</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Option A</label>
                                            <input type="text" class="form-control" name="option_a" value="<?php echo e($_POST['option_a'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Option B</label>
                                            <input type="text" class="form-control" name="option_b" value="<?php echo e($_POST['option_b'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Option C</label>
                                            <input type="text" class="form-control" name="option_c" value="<?php echo e($_POST['option_c'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Option D</label>
                                            <input type="text" class="form-control" name="option_d" value="<?php echo e($_POST['option_d'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
                                        <select class="form-select" name="correct_answer">
                                            <option value="">Select Correct Answer</option>
                                            <option value="A" <?php echo (isset($_POST['correct_answer']) && $_POST['correct_answer'] == 'A') ? 'selected' : ''; ?>>A</option>
                                            <option value="B" <?php echo (isset($_POST['correct_answer']) && $_POST['correct_answer'] == 'B') ? 'selected' : ''; ?>>B</option>
                                            <option value="C" <?php echo (isset($_POST['correct_answer']) && $_POST['correct_answer'] == 'C') ? 'selected' : ''; ?>>C</option>
                                            <option value="D" <?php echo (isset($_POST['correct_answer']) && $_POST['correct_answer'] == 'D') ? 'selected' : ''; ?>>D</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Fields -->
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Explanation/Answer Key</label>
                                <textarea class="form-control" name="explanation" rows="2" placeholder="Explain the correct answer..."><?php echo e($_POST['explanation'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tags (comma separated)</label>
                                <input type="text" class="form-control" name="tags" value="<?php echo e($_POST['tags'] ?? ''); ?>" placeholder="algebra, equations, quadratic">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Question
                    </button>
                    <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for dynamic show/hide -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide MCQ options based on question type
    var questionType = document.getElementById('question_type');
    var mcqOptions = document.getElementById('mcq-options');
    
    questionType.addEventListener('change', function() {
        mcqOptions.style.display = (this.value === 'mcq') ? 'block' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
