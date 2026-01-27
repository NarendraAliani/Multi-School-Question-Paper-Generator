<?php
// c:\xampp\htdocs\project\teacher\questions\list.php
// Teacher Questions Management - List View

$_page_title = "My Questions";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if (validate_csrf_token($_GET['token'] ?? '')) {
        $question_id = (int)$_GET['id'];
        
        // Get question image to delete
        $question = $db->selectOne("SELECT question_image FROM questions WHERE question_id = ? AND school_id = ?", [$question_id, $school_id]);
        
        $deleted = $db->delete("DELETE FROM questions WHERE question_id = ? AND school_id = ?", [$question_id, $school_id]);
        
        if ($deleted) {
            // Delete image file
            if (!empty($question['question_image'])) {
                $file_path = QUESTIONS_UPLOAD_PATH . '/' . $question['question_image'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            log_activity($user_id, 'delete', 'question', $question_id, 'Deleted question');
            set_flash_message(MSG_SUCCESS, 'Question deleted successfully!');
        } else {
            set_flash_message(MSG_ERROR, 'Failed to delete question.');
        }
        redirect(base_url('teacher/questions/list.php'));
    }
}

// Get questions with filters
$board_id = (int)($_GET['board_id'] ?? 0);
$standard_id = (int)($_GET['standard_id'] ?? 0);
$subject_id = (int)($_GET['subject_id'] ?? 0);
$difficulty = sanitize_input($_GET['difficulty'] ?? '');
$type = sanitize_input($_GET['type'] ?? '');

// Build query with filters
$query = "
    SELECT q.*, ch.chapter_name, sub.subject_name, std.standard_name, b.board_name
    FROM questions q
    INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
    INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    INNER JOIN boards b ON std.board_id = b.board_id
    WHERE q.school_id = ? AND q.status = 'active'
";
$params = [$school_id];

if ($board_id) {
    $query .= " AND b.board_id = ?";
    $params[] = $board_id;
}
if ($standard_id) {
    $query .= " AND std.standard_id = ?";
    $params[] = $standard_id;
}
if ($subject_id) {
    $query .= " AND sub.subject_id = ?";
    $params[] = $subject_id;
}
if (!empty($difficulty)) {
    $query .= " AND q.difficulty_level = ?";
    $params[] = $difficulty;
}
if (!empty($type)) {
    $query .= " AND q.question_type = ?";
    $params[] = $type;
}

$query .= " ORDER BY q.created_at DESC";

$questions = $db->select($query, $params);

// Get filters
$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");
$standards = $board_id ? $db->select("SELECT standard_id, standard_name FROM standards WHERE board_id = ? AND status = 'active' ORDER BY display_order", [$board_id]) : [];
$subjects = $standard_id ? $db->select("SELECT subject_id, subject_name FROM subjects WHERE standard_id = ? AND status = 'active' ORDER BY display_order", [$standard_id]) : [];

$total_questions = count($questions);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-question-circle"></i> My Questions</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Questions</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url('teacher/questions/import.php'); ?>" class="btn btn-success me-2">
                            <i class="fas fa-file-import"></i> Import CSV
                        </a>
                        <a href="<?php echo base_url('teacher/questions/add.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Question
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filters
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Board</label>
                            <select class="form-select" name="board_id" onchange="this.form.submit()">
                                <option value="">All Boards</option>
                                <?php foreach ($boards as $b): ?>
                                    <option value="<?php echo $b['board_id']; ?>" <?php echo $board_id == $b['board_id'] ? 'selected' : ''; ?>>
                                        <?php echo e($b['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Standard</label>
                            <select class="form-select" name="standard_id" onchange="this.form.submit()">
                                <option value="">All Standards</option>
                                <?php foreach ($standards as $s): ?>
                                    <option value="<?php echo $s['standard_id']; ?>" <?php echo $standard_id == $s['standard_id'] ? 'selected' : ''; ?>>
                                        <?php echo e($s['standard_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Difficulty</label>
                            <select class="form-select" name="difficulty" onchange="this.form.submit()">
                                <option value="">All Levels</option>
                                <option value="easy" <?php echo $difficulty == 'easy' ? 'selected' : ''; ?>>Easy</option>
                                <option value="medium" <?php echo $difficulty == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="hard" <?php echo $difficulty == 'hard' ? 'selected' : ''; ?>>Hard</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="mcq" <?php echo $type == 'mcq' ? 'selected' : ''; ?>>MCQ</option>
                                <option value="short_answer" <?php echo $type == 'short_answer' ? 'selected' : ''; ?>>Short Answer</option>
                                <option value="long_answer" <?php echo $type == 'long_answer' ? 'selected' : ''; ?>>Long Answer</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Total Questions: <strong><?php echo $total_questions; ?></strong></h5>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Difficulty Distribution
                </div>
                <div class="card-body">
                    <?php
                    $easy_count = 0;
                    $medium_count = 0;
                    $hard_count = 0;
                    foreach ($questions as $q) {
                        if ($q['difficulty_level'] == 'easy') $easy_count++;
                        if ($q['difficulty_level'] == 'medium') $medium_count++;
                        if ($q['difficulty_level'] == 'hard') $hard_count++;
                    }
                    ?>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h4><span class="badge bg-success"><?php echo $easy_count; ?></span></h4>
                            <p>Easy Questions</p>
                        </div>
                        <div class="col-md-4">
                            <h4><span class="badge bg-warning"><?php echo $medium_count; ?></span></h4>
                            <p>Medium Questions</p>
                        </div>
                        <div class="col-md-4">
                            <h4><span class="badge bg-danger"><?php echo $hard_count; ?></span></h4>
                            <p>Hard Questions</p>
                        </div>
                    </div>
                    <?php
                    // Balance check
                    if ($total_questions > 0) {
                        $easy_percent = ($easy_count / $total_questions) * 100;
                        $hard_percent = ($hard_count / $total_questions) * 100;
                        
                        if ($easy_percent < 20 || $hard_percent < 10) {
                            echo '<div class="alert alert-warning mt-3 mb-0"><i class="fas fa-exclamation-triangle"></i> <strong>Tip:</strong> Maintain a balanced difficulty distribution (40% easy, 40% medium, 20% hard) for better paper generation.</div>';
                        } elseif ($easy_percent > 70) {
                            echo '<div class="alert alert-info mt-3 mb-0"><i class="fas fa-info-circle"></i> <strong>Recommendation:</strong> Add more challenging questions to balance your question bank.</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> Question Bank
                </div>
                <div class="card-body">
                    <?php if (!empty($questions)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Board/Std/Sub</th>
                                        <th>Chapter</th>
                                        <th>Question</th>
                                        <th>Type</th>
                                        <th>Difficulty</th>
                                        <th>Marks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($questions as $q): ?>
                                        <tr>
                                            <td><?php echo $q['question_id']; ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo e($q['board_name']); ?></span><br>
                                                <span class="badge bg-info"><?php echo e($q['standard_name']); ?></span><br>
                                                <span class="badge bg-warning"><?php echo e(substr($q['subject_name'], 0, 8)); ?></span>
                                            </td>
                                            <td><?php echo e($q['chapter_name']); ?></td>
                                            <td>
                                                <?php if (!empty($q['question_image'])): ?>
                                                    <i class="fas fa-image text-success" title="Has image"></i>
                                                <?php endif; ?>
                                                <a href="#" class="preview-question" data-qid="<?php echo $q['question_id']; ?>" data-bs-toggle="modal" data-bs-target="#previewModal">
                                                    <?php echo e(substr($q['question_text'], 0, 80)); ?>...
                                                </a>
                                            </td>
                                            <td><span class="badge bg-primary"><?php echo ucfirst(str_replace('_', ' ', $q['question_type'])); ?></span></td>
                                            <td>
                                                <span class="badge bg-<?php echo $q['difficulty_level'] == 'easy' ? 'success' : ($q['difficulty_level'] == 'medium' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($q['difficulty_level']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $q['marks']; ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary preview-question" data-qid="<?php echo $q['question_id']; ?>" data-bs-toggle="modal" data-bs-target="#previewModal" title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="<?php echo base_url('teacher/questions/edit.php?id=' . $q['question_id']); ?>" class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo base_url('teacher/questions/list.php?delete=1&id=' . $q['question_id'] . '&token=' . generate_csrf_token()); ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No questions found. 
                            <a href="<?php echo base_url('teacher/questions/add.php'); ?>">Add your first question</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Question Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel"><i class="fas fa-eye"></i> Question Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Question Preview via AJAX
document.addEventListener('DOMContentLoaded', function() {
    const previewButtons = document.querySelectorAll('.preview-question');
    const previewContent = document.getElementById('previewContent');
    
    previewButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const questionId = this.getAttribute('data-qid');
            
            // Show loading
            previewContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            // Fetch question details via AJAX
            fetch('<?php echo base_url('ajax/get_question_preview.php'); ?>?id=' + questionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="question-preview">';
                        html += '<p><strong>Board:</strong> ' + data.board_name + ' | <strong>Standard:</strong> ' + data.standard_name + ' | <strong>Subject:</strong> ' + data.subject_name + '</p>';
                        html += '<p><strong>Chapter:</strong> ' + data.chapter_name + '</p>';
                        html += '<hr>';
                        html += '<h6>Question:</h6>';
                        html += '<p>' + data.question_text + '</p>';
                        
                        if (data.question_image) {
                            html += '<img src="<?php echo QUESTIONS_UPLOAD_URL; ?>/' + data.question_image + '" class="img-fluid" style="max-height: 300px;">';
                        }
                        
                        if (data.question_type === 'mcq') {
                            html += '<hr><h6>Options:</h6>';
                            html += '<ul class="list-unstyled">';
                            html += '<li><strong>A.</strong> ' + data.option_a + '</li>';
                            html += '<li><strong>B.</strong> ' + data.option_b + '</li>';
                            html += '<li><strong>C.</strong> ' + data.option_c + '</li>';
                            html += '<li><strong>D.</strong> ' + data.option_d + '</li>';
                            html += '</ul>';
                            html += '<p><strong>Correct Answer:</strong> <span class="badge bg-success">' + data.correct_answer + '</span></p>';
                        }
                        
                        if (data.solution) {
                            html += '<hr><h6>Solution:</h6><p>' + data.solution + '</p>';
                        }
                        
                        html += '<hr><div class="row"><div class="col-md-4"><strong>Type:</strong> <span class="badge bg-primary">' + data.question_type.replace('_', ' ').toUpperCase() + '</span></div>';
                        html += '<div class="col-md-4"><strong>Difficulty:</strong> <span class="badge bg-' + (data.difficulty_level === 'easy' ? 'success' : (data.difficulty_level === 'medium' ? 'warning' : 'danger')) + '">' + data.difficulty_level.toUpperCase() + '</span></div>';
                        html += '<div class="col-md-4"><strong>Marks:</strong> ' + data.marks + '</div></div>';
                        html += '</div>';
                        
                        previewContent.innerHTML = html;
                    } else {
                        previewContent.innerHTML = '<div class="alert alert-danger">Failed to load question preview.</div>';
                    }
                })
                .catch(error => {
                    previewContent.innerHTML = '<div class="alert alert-danger">Error loading preview.</div>';
                });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
