<?php
// c:\xampp\htdocs\project\teacher\questions\edit.php
// Edit Question

$_page_title = "Edit Question";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));

$db = getDB();
$errors = [];
$question_id = (int)($_GET['id'] ?? 0);
$user_id = get_user_id();
$school_id = get_school_id();

$question = $db->selectOne("
    SELECT q.*, ch.subject_id, std.board_id, sub.standard_id
    FROM questions q
    INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
    INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    WHERE q.question_id = ? AND q.school_id = ?
", [$question_id, $school_id]);

if (!$question) {
    set_flash_message(MSG_ERROR, 'Question not found.');
    redirect(base_url('teacher/questions/list.php'));
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");
$standards = $db->select("SELECT standard_id, standard_name FROM standards WHERE board_id = ? AND status = 'active' ORDER BY display_order", [$question['board_id']]);
$subjects = $db->select("SELECT subject_id, subject_name FROM subjects WHERE standard_id = ? AND status = 'active' ORDER BY display_order", [$question['standard_id']]);
$chapters = $db->select("SELECT chapter_id, chapter_name FROM chapters WHERE subject_id = ? AND status = 'active' ORDER BY display_order", [$question['subject_id']]);

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
    
    if (empty($chapter_id) || empty($question_text)) {
        $errors[] = 'Chapter and question text are required.';
    }
    
    // Handle new image upload
    $question_image = $question['question_image'];
    if (!empty($_FILES['question_image']['name']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
        $upload = upload_image($_FILES['question_image'], QUESTIONS_UPLOAD_PATH, 'q_' . $user_id);
        if ($upload['success']) {
            // Delete old image
            if (!empty($question_image)) {
                $old_path = QUESTIONS_UPLOAD_PATH . '/' . $question_image;
                if (file_exists($old_path)) unlink($old_path);
            }
            $question_image = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }
    
    if (empty($errors)) {
        $query = "UPDATE questions SET chapter_id = ?, question_text = ?, question_image = ?, question_type = ?, 
                  difficulty_level = ?, marks = ?, time_minutes = ?, option_a = ?, option_b = ?, option_c = ?, 
                  option_d = ?, correct_answer = ?, explanation = ?, tags = ? 
                  WHERE question_id = ? AND school_id = ?";
        
        $updated = $db->update($query, [
            $chapter_id, $question_text, $question_image, $question_type, $difficulty, $marks, $time_minutes,
            $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $tags, $question_id, $school_id
        ]);
        
        if ($updated !== false) {
            log_activity($user_id, 'update', 'question', $question_id, 'Updated question');
            set_flash_message(MSG_SUCCESS, 'Question updated successfully!');
            redirect(base_url('teacher/questions/list.php'));
        } else {
            $errors[] = 'Failed to update question.';
        }
    }
}

$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $question;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Edit Question</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/questions/list.php'); ?>">Questions</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                
                <!-- Hierarchy -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-sitemap"></i> Select Hierarchy
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Board</label>
                                <select class="form-select" name="board_id">
                                    <?php foreach ($boards as $b): ?>
                                        <option value="<?php echo $b['board_id']; ?>" <?php echo ($form_data['board_id'] == $b['board_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($b['board_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Standard</label>
                                <select class="form-select" name="standard_id">
                                    <?php foreach ($standards as $s): ?>
                                        <option value="<?php echo $s['standard_id']; ?>" <?php echo ($form_data['standard_id'] == $s['standard_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($s['standard_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Subject</label>
                                <select class="form-select" name="subject_id">
                                    <?php foreach ($subjects as $s): ?>
                                        <option value="<?php echo $s['subject_id']; ?>" <?php echo ($form_data['subject_id'] == $s['subject_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($s['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Chapter <span class="text-danger">*</span></label>
                                <select class="form-select" name="chapter_id" required>
                                    <?php foreach ($chapters as $c): ?>
                                        <option value="<?php echo $c['chapter_id']; ?>" <?php echo ($form_data['chapter_id'] == $c['chapter_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($c['chapter_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
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
                                <label class="form-label">Question Type</label>
                                <select class="form-select" name="question_type" id="question_type">
                                    <option value="short_answer" <?php echo $form_data['question_type'] == 'short_answer' ? 'selected' : ''; ?>>Short Answer</option>
                                    <option value="long_answer" <?php echo $form_data['question_type'] == 'long_answer' ? 'selected' : ''; ?>>Long Answer</option>
                                    <option value="mcq" <?php echo $form_data['question_type'] == 'mcq' ? 'selected' : ''; ?>>MCQ</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Difficulty</label>
                                <select class="form-select" name="difficulty">
                                    <option value="easy" <?php echo $form_data['difficulty_level'] == 'easy' ? 'selected' : ''; ?>>Easy</option>
                                    <option value="medium" <?php echo $form_data['difficulty_level'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="hard" <?php echo $form_data['difficulty_level'] == 'hard' ? 'selected' : ''; ?>>Hard</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Marks</label>
                                <input type="number" class="form-control" name="marks" value="<?php echo $form_data['marks']; ?>" min="0.5" step="0.5" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Time (minutes)</label>
                                <input type="number" class="form-control" name="time_minutes" value="<?php echo $form_data['time_minutes']; ?>" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="question_text" rows="3" required><?php echo e($form_data['question_text']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Change Image</label>
                            <?php if (!empty($question['question_image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo QUESTIONS_UPLOAD_URL . '/' . $question['question_image']; ?>" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 5px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="question_image" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Question
                    </button>
                    <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('question_type').addEventListener('change', function() {
        var mcqOptions = document.getElementById('mcq-options');
        if (mcqOptions) {
            mcqOptions.style.display = (this.value === 'mcq') ? 'block' : 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
