<?php
// c:\xampp\htdocs\project\admin\standards\edit.php
// Edit Standard/Class

$_page_title = "Edit Standard";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$errors = [];
$standard_id = (int)($_GET['id'] ?? 0);

// Get standard details
$standard = $db->selectOne("SELECT * FROM standards WHERE standard_id = ?", [$standard_id]);

if (!$standard) {
    set_flash_message(MSG_ERROR, 'Standard not found.');
    redirect(base_url('admin/standards/list.php'));
}

// Get all active boards
$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }
    
    // Get and sanitize input
    $board_id = (int)($_POST['board_id'] ?? 0);
    $standard_name = sanitize_input($_POST['standard_name'] ?? '');
    $standard_code = sanitize_input($_POST['standard_code'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'active');
    
    // Validate
    if (empty($board_id)) {
        $errors[] = 'Please select a board.';
    }
    
    if (empty($standard_name)) {
        $errors[] = 'Standard name is required.';
    }
    
    if (empty($standard_code)) {
        $errors[] = 'Standard code is required.';
    } elseif ($board_id != $standard['board_id'] || $standard_code !== $standard['standard_code']) {
        // Check if new combination already exists
        if ($db->exists('standards', 'board_id = ? AND standard_code = ? AND standard_id != ?', [$board_id, $standard_code, $standard_id])) {
            $errors[] = 'Standard code already exists for this board.';
        }
    }
    
    // If no errors, update
    if (empty($errors)) {
        $query = "UPDATE standards 
                  SET board_id = ?, standard_name = ?, standard_code = ?, description = ?, 
                      display_order = ?, status = ?
                  WHERE standard_id = ?";
        
        $updated = $db->update($query, [$board_id, $standard_name, $standard_code, $description, $display_order, $status, $standard_id]);
        
        if ($updated !== false) {
            log_activity(get_user_id(), 'update', 'standard', $standard_id, "Updated standard: $standard_name");
            set_flash_message(MSG_SUCCESS, 'Standard updated successfully!');
            redirect(base_url('admin/standards/list.php'));
        } else {
            $errors[] = 'Failed to update standard. Please try again.';
        }
    }
}

// Use POST data if available, otherwise use database data
$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $standard;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Edit Standard/Class</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/standards/list.php'); ?>">Standards</a></li>
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
                    <i class="fas fa-graduation-cap"></i> Standard Information
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

                        <div class="mb-3">
                            <label for="standard_name" class="form-label">Standard Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="standard_name" name="standard_name" 
                                   value="<?php echo e($form_data['standard_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="standard_code" class="form-label">Standard Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="standard_code" name="standard_code" 
                                   value="<?php echo e($form_data['standard_code']); ?>" required>
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
                                <i class="fas fa-save"></i> Update Standard
                            </button>
                            <a href="<?php echo base_url('admin/standards/list.php'); ?>" class="btn btn-secondary">
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
                    <i class="fas fa-info-circle"></i> Standard Details
                </div>
                <div class="card-body">
                    <p><strong>Standard ID:</strong> <?php echo $standard['standard_id']; ?></p>
                    <p><strong>Created:</strong> <?php echo format_datetime($standard['created_at']); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo format_datetime($standard['updated_at']); ?></p>
                    
                    <?php
                    $subject_count = $db->count('subjects', 'standard_id = ?', [$standard_id]);
                    ?>
                    <hr>
                    <p><strong>Associated Subjects:</strong> <?php echo $subject_count; ?></p>
                    
                    <?php if ($subject_count > 0): ?>
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle"></i> This standard has <?php echo $subject_count; ?> associated subject(s).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
