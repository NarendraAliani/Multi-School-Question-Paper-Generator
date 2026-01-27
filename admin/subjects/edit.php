<?php
// c:\xampp\htdocs\project\admin\subjects\edit.php
// Edit Subject with AJAX Dependent Dropdowns

$_page_title = "Edit Subject";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

$db = getDB();
$errors = [];
$subject_id = (int)($_GET['id'] ?? 0);

// Get subject details with hierarchy
$subject = $db->selectOne("
    SELECT sub.*, std.board_id, b.board_name, std.standard_name
    FROM subjects sub
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    INNER JOIN boards b ON std.board_id = b.board_id
    WHERE sub.subject_id = ?
", [$subject_id]);

if (!$subject) {
    set_flash_message(MSG_ERROR, 'Subject not found.');
    redirect(base_url('admin/subjects/list.php'));
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

// Get all active boards
$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");

// Get standards for selected board (for edit mode)
$standards = $db->select("SELECT standard_id, standard_name FROM standards WHERE board_id = ? AND status = 'active' ORDER BY display_order", 
                         [$subject['board_id']]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }
    
    // Get and sanitize input
    $board_id = (int)($_POST['board_id'] ?? 0);
    $standard_id = (int)($_POST['standard_id'] ?? 0);
    $subject_name = sanitize_input($_POST['subject_name'] ?? '');
    $subject_code = sanitize_input($_POST['subject_code'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'active');
    
    // Validate
    if (empty($board_id)) {
        $errors[] = 'Please select a board.';
    }
    
    if (empty($standard_id)) {
        $errors[] = 'Please select a standard.';
    }
    
    if (empty($subject_name)) {
        $errors[] = 'Subject name is required.';
    }
    
    if (empty($subject_code)) {
        $errors[] = 'Subject code is required.';
    } elseif ($standard_id != $subject['standard_id'] || $subject_code !== $subject['subject_code']) {
        if ($db->exists('subjects', 'standard_id = ? AND subject_code = ? AND subject_id != ?', 
                       [$standard_id, $subject_code, $subject_id])) {
            $errors[] = 'Subject code already exists for this standard.';
        }
    }
    
    // If no errors, update
    if (empty($errors)) {
        $query = "UPDATE subjects 
                  SET standard_id = ?, subject_name = ?, subject_code = ?, description = ?, 
                      display_order = ?, status = ?
                  WHERE subject_id = ?";
        
        $updated = $db->update($query, [$standard_id, $subject_name, $subject_code, $description, 
                                        $display_order, $status, $subject_id]);
        
        if ($updated !== false) {
            log_activity(get_user_id(), 'update', 'subject', $subject_id, "Updated subject: $subject_name");
            set_flash_message(MSG_SUCCESS, 'Subject updated successfully!');
            redirect(base_url('admin/subjects/list.php'));
        } else {
            $errors[] = 'Failed to update subject. Please try again.';
        }
    }
}

// Use POST data if available, otherwise use database data
$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $subject;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Edit Subject</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/subjects/list.php'); ?>">Subjects</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-book"></i> Subject Information
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php echo csrf_token_field(); ?>
                        
                        <!-- Board Selection -->
                        <div class="mb-3">
                            <label for="board_id" class="form-label">Board <span class="text-danger">*</span></label>
                            <select class="form-select" id="board_id" name="board_id" required>
                                <option value="">Select Board</option>
                                <?php foreach ($boards as $board): ?>
                                    <option value="<?php echo $board['board_id']; ?>" 
                                            <?php echo ($form_data['board_id'] == $board['board_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($board['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Standard Selection (AJAX Dependent) -->
                        <div class="mb-3">
                            <label for="standard_id" class="form-label">Standard/Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="standard_id" name="standard_id" required>
                                <option value="">Select Standard</option>
                                <?php foreach ($standards as $standard): ?>
                                    <option value="<?php echo $standard['standard_id']; ?>"
                                            <?php echo ($form_data['standard_id'] == $standard['standard_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($standard['standard_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" 
                                   value="<?php echo e($form_data['subject_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject_code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" 
                                   value="<?php echo e($form_data['subject_code']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo e($form_data['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" 
                                       value="<?php echo e($form_data['display_order']); ?>" min="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo $form_data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $form_data['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Subject
                            </button>
                            <a href="<?php echo base_url('admin/subjects/list.php'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Subject Details
                </div>
                <div class="card-body">
                    <p><strong>Subject ID:</strong> <?php echo $subject['subject_id']; ?></p>
                    <p><strong>Board:</strong> <?php echo e($subject['board_name']); ?></p>
                    <p><strong>Standard:</strong> <?php echo e($subject['standard_name']); ?></p>
                    <p><strong>Created:</strong> <?php echo format_datetime($subject['created_at']); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo format_datetime($subject['updated_at']); ?></p>
                    
                    <?php
                    $chapter_count = $db->count('chapters', 'subject_id = ?', [$subject_id]);
                    ?>
                    <hr>
                    <p><strong>Associated Chapters:</strong> <?php echo $chapter_count; ?></p>
                    
                    <?php if ($chapter_count > 0): ?>
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle"></i> This subject has <?php echo $chapter_count; ?> associated chapter(s).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
