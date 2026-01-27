<?php
// c:\xampp\htdocs\project\admin\boards\edit.php
// Edit Board

$_page_title = "Edit Board";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$errors = [];
$board_id = (int)($_GET['id'] ?? 0);

// Get board details
$board = $db->selectOne("SELECT * FROM boards WHERE board_id = ?", [$board_id]);

if (!$board) {
    set_flash_message(MSG_ERROR, 'Board not found.');
    redirect(base_url('admin/boards/list.php'));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    }
    
    // Get and sanitize input
    $board_name = sanitize_input($_POST['board_name'] ?? '');
    $board_code = sanitize_input($_POST['board_code'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'active');
    
    // Validate
    if (empty($board_name)) {
        $errors[] = 'Board name is required.';
    }
    
    if (empty($board_code)) {
        $errors[] = 'Board code is required.';
    } elseif ($board_code !== $board['board_code']) {
        // Check if new code already exists
        if ($db->exists('boards', 'board_code = ? AND board_id != ?', [$board_code, $board_id])) {
            $errors[] = 'Board code already exists. Please use a different code.';
        }
    }
    
    // If no errors, update
    if (empty($errors)) {
        $query = "UPDATE boards 
                  SET board_name = ?, board_code = ?, description = ?, status = ?
                  WHERE board_id = ?";
        
        $updated = $db->update($query, [$board_name, $board_code, $description, $status, $board_id]);
        
        if ($updated !== false) {
            log_activity(get_user_id(), 'update', 'board', $board_id, "Updated board: $board_name");
            set_flash_message(MSG_SUCCESS, 'Board updated successfully!');
            redirect(base_url('admin/boards/list.php'));
        } else {
            $errors[] = 'Failed to update board. Please try again.';
        }
    }
}

// Use POST data if available, otherwise use database data
$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $board;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Edit Board</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/boards/list.php'); ?>">Boards</a></li>
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
                    <i class="fas fa-clipboard-list"></i> Board Information
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
                            <label for="board_name" class="form-label">Board Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="board_name" name="board_name" 
                                   value="<?php echo e($form_data['board_name']); ?>" 
                                   placeholder="e.g., CBSE, ICSE, State Board" required>
                        </div>

                        <div class="mb-3">
                            <label for="board_code" class="form-label">Board Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="board_code" name="board_code" 
                                   value="<?php echo e($form_data['board_code']); ?>" 
                                   placeholder="e.g., CBSE, ICSE" required>
                            <small class="text-muted">Unique code for the board</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Brief description about the board"><?php echo e($form_data['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo $form_data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $form_data['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Board
                            </button>
                            <a href="<?php echo base_url('admin/boards/list.php'); ?>" class="btn btn-secondary">
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
                    <i class="fas fa-info-circle"></i> Board Details
                </div>
                <div class="card-body">
                    <p><strong>Board ID:</strong> <?php echo $board['board_id']; ?></p>
                    <p><strong>Created:</strong> <?php echo format_datetime($board['created_at']); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo format_datetime($board['updated_at']); ?></p>
                    
                    <?php
                    $standard_count = $db->count('standards', 'board_id = ?', [$board_id]);
                    ?>
                    <hr>
                    <p><strong>Associated Standards:</strong> <?php echo $standard_count; ?></p>
                    
                    <?php if ($standard_count > 0): ?>
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle"></i> This board has <?php echo $standard_count; ?> associated standard(s). Deleting it will also delete all associated data.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
