<?php
// c:\xampp\htdocs\project\teacher\blueprints\edit.php
// Edit Paper Blueprint (basic details only)

$_page_title = "Edit Blueprint";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();
$errors = [];

$blueprint_id = (int)($_GET['id'] ?? 0);

$blueprint = $db->selectOne(
    "SELECT * FROM paper_blueprints WHERE blueprint_id = ? AND school_id = ?",
    [$blueprint_id, $school_id]
);

if (!$blueprint) {
    set_flash_message(MSG_ERROR, 'Blueprint not found.');
    redirect(base_url('teacher/blueprints/list.php'));
}

$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");
$standards = $db->select("SELECT standard_id, standard_name FROM standards WHERE board_id = ? AND status = 'active' ORDER BY display_order, standard_name", [$blueprint['board_id']]);
$subjects = $db->select("SELECT subject_id, subject_name FROM subjects WHERE standard_id = ? AND status = 'active' ORDER BY display_order, subject_name", [$blueprint['standard_id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = "Invalid security token.";
    }

    $blueprint_name = sanitize_input($_POST['blueprint_name'] ?? '');
    $board_id = (int)($_POST['board_id'] ?? 0);
    $standard_id = (int)($_POST['standard_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $duration_minutes = (int)($_POST['duration_minutes'] ?? 180);
    $instructions = sanitize_input($_POST['instructions'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'active');

    if (empty($blueprint_name)) {
        $errors[] = "Blueprint name is required.";
    }
    if (empty($board_id) || empty($standard_id) || empty($subject_id)) {
        $errors[] = "Please select board, standard, and subject.";
    }

    if (empty($errors)) {
        $updated = $db->update(
            "UPDATE paper_blueprints 
             SET blueprint_name = ?, board_id = ?, standard_id = ?, subject_id = ?, duration_minutes = ?, instructions = ?, status = ?
             WHERE blueprint_id = ? AND school_id = ?",
            [$blueprint_name, $board_id, $standard_id, $subject_id, $duration_minutes, $instructions, $status, $blueprint_id, $school_id]
        );

        if ($updated !== false) {
            log_activity($user_id, 'update', 'blueprint', $blueprint_id, 'Updated blueprint: ' . $blueprint_name);
            set_flash_message(MSG_SUCCESS, 'Blueprint updated successfully.');
            redirect(base_url('teacher/blueprints/list.php'));
        } else {
            $errors[] = 'Failed to update blueprint.';
        }
    }
}

$form = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $blueprint;

?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-copy"></i> Edit Paper Blueprint</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/blueprints/list.php'); ?>">Blueprints</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrf_token_field(); ?>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Blueprint Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Blueprint Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="blueprint_name" 
                                       value="<?php echo e($form['blueprint_name']); ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" name="duration_minutes" 
                                       value="<?php echo e($form['duration_minutes']); ?>" min="30">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo $form['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $form['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Board <span class="text-danger">*</span></label>
                                <select class="form-select" id="board_id" name="board_id" required>
                                    <option value="">Select Board</option>
                                    <?php foreach ($boards as $board): ?>
                                        <option value="<?php echo $board['board_id']; ?>" 
                                            <?php echo ($form['board_id'] == $board['board_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($board['board_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Standard <span class="text-danger">*</span></label>
                                <select class="form-select" id="standard_id" name="standard_id" required>
                                    <option value="">Select Standard</option>
                                    <?php foreach ($standards as $std): ?>
                                        <option value="<?php echo $std['standard_id']; ?>" 
                                            <?php echo ($form['standard_id'] == $std['standard_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($std['standard_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subj): ?>
                                        <option value="<?php echo $subj['subject_id']; ?>" 
                                            <?php echo ($form['subject_id'] == $subj['subject_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($subj['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Instructions</label>
                                <textarea class="form-control" name="instructions" rows="3"><?php echo e($form['instructions']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Update Blueprint
                    </button>
                    <a href="<?php echo base_url('teacher/blueprints/list.php'); ?>" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Basic dependent dropdowns for edit page
// (Optional enhancement: load via AJAX similar to add.php)
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
