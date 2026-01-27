<?php
// c:\xampp\htdocs\project\teacher\dashboard.php
// Teacher Dashboard

$_page_title = "Teacher Dashboard";
require_once __DIR__ . '/../includes/auth_check.php';
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();

// Get statistics
$my_questions = $db->count('questions', "created_by = ? AND status = 'active'", [$user_id]);
$my_papers = $db->count('generated_papers', "generated_by = ?", [$user_id]);
$school_questions = $db->count('questions', "school_id = ? AND status = 'active'", [$school_id]);
$school_papers = $db->count('generated_papers', "school_id = ?", [$school_id]);

// Get recent papers
$recent_papers = $db->select(
    "SELECT gp.*, b.board_name, s.standard_name, sub.subject_name 
     FROM generated_papers gp 
     INNER JOIN boards b ON gp.board_id = b.board_id
     INNER JOIN standards s ON gp.standard_id = s.standard_id
     INNER JOIN subjects sub ON gp.subject_id = sub.subject_id
     WHERE gp.generated_by = ?
     ORDER BY gp.generated_at DESC 
     LIMIT 5",
    [$user_id]
);

// Get recent questions
$recent_questions = $db->select(
    "SELECT q.*, ch.chapter_name, sub.subject_name 
     FROM questions q
     INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
     INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
     WHERE q.created_by = ? AND q.status = 'active'
     ORDER BY q.created_at DESC 
     LIMIT 5",
    [$user_id]
);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt"></i> Teacher Dashboard</h1>
                <p class="lead">Welcome, <?php echo e(get_session('full_name')); ?>!</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-question-circle icon"></i>
                <div class="number"><?php echo $my_questions; ?></div>
                <div class="label">My Questions</div>
                <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-sm btn-primary mt-2">View All</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-file-alt icon"></i>
                <div class="number"><?php echo $my_papers; ?></div>
                <div class="label">My Papers</div>
                <a href="<?php echo base_url('teacher/papers/list.php'); ?>" class="btn btn-sm btn-primary mt-2">View All</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-database icon"></i>
                <div class="number"><?php echo $school_questions; ?></div>
                <div class="label">School Question Bank</div>
                <span class="badge bg-info mt-2">Total</span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-chart-line icon"></i>
                <div class="number"><?php echo $school_papers; ?></div>
                <div class="label">School Papers</div>
                <span class="badge bg-success mt-2">Total</span>
            </div>
        </div>
    </div>

    <!-- Recent Papers & Questions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-alt"></i> Recent Papers
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_papers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Paper Title</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_papers as $paper): ?>
                                        <tr>
                                            <td><?php echo e($paper['paper_title']); ?></td>
                                            <td><?php echo e($paper['subject_name']); ?></td>
                                            <td><span class="badge bg-<?php echo $paper['status'] == 'finalized' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($paper['status']); ?>
                                            </span></td>
                                            <td>
                                                <a href="<?php echo base_url('teacher/papers/preview.php?id=' . $paper['paper_id']); ?>" 
                                                   class="btn btn-sm btn-info" title="Preview">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No papers generated yet.</p>
                        <a href="<?php echo base_url('teacher/papers/create.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create First Paper
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-question-circle"></i> Recent Questions
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($recent_questions)): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($recent_questions as $question): ?>
                                <li class="mb-3 question-card">
                                    <div class="question-marks"><?php echo $question['marks']; ?> marks</div>
                                    <div>
                                        <strong><?php echo e($question['subject_name']); ?></strong> - 
                                        <?php echo e($question['chapter_name']); ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo e(substr($question['question_text'], 0, 100)); ?>...
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-<?php echo $question['difficulty_level'] == 'easy' ? 'success' : ($question['difficulty_level'] == 'medium' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($question['difficulty_level']); ?>
                                        </span>
                                        <span class="badge bg-secondary"><?php echo ucfirst(str_replace('_', ' ', $question['question_type'])); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No questions added yet.</p>
                        <a href="<?php echo base_url('teacher/questions/add.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Question
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('teacher/blueprints/list.php'); ?>" class="btn btn-warning w-100 btn-lg text-white">
                                <i class="fas fa-copy"></i> Manage Blueprints
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('teacher/papers/create.php'); ?>" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-file-alt"></i> Generate Paper
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('teacher/questions/add.php'); ?>" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-plus-circle"></i> Add Question
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-info w-100 btn-lg text-white">
                                <i class="fas fa-database"></i> Browse Bank
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
