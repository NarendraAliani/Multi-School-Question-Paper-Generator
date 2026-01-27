<?php
// c:\xampp\htdocs\project\admin\standards\add.php
// Add New Standard/Class

$_page_title = "Add Standard";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$errors = [];

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
    } elseif ($db->exists('standards', 'board_id = ? AND standard_code = ?', [$board_id, $standard_code])) {
        $errors[] = 'Standard code already exists for this board. Please use a different code.';
    }
    
    // If no errors, insert
    if (empty($errors)) {
        $query = "INSERT INTO standards (board_id, standard_name, standard_code, description, display_order, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $standard_id = $db->insert($query, [$board_id, $standard_name, $standard_code, $description, $display_order, $status]);
        
        if ($standard_id) {
            log_activity(get_user_id(), 'create', 'standard', $standard_id, "Created standard: $standard_name");
            set_flash_message(MSG_SUCCESS, 'Standard added successfully!');
            redirect(base_url('admin/standards/list.php'));
        } else {
            $errors[] = 'Failed to add standard. Please try again.';
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Standard/Class</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/standards/list.php'); ?>">Standards</a></li>
                        <li class="breadcrumb-item active">Add New</li>
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
                                            <?php echo (isset($_POST['board_id']) && $_POST['board_id'] == $board['board_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($board['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="standard_name" class="form-label">Standard Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="standard_name" name="standard_name" 
                                   value="<?php echo e($_POST['standard_name'] ?? ''); ?>" 
                                   placeholder="e.g., Class 10, Grade 5" required>
                            <small class="text-muted">Full name of the class/standard</small>
                        </div>

                        <div class="mb-3">
                            <label for="standard_code" class="form-label">Standard Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="standard_code" name="standard_code" 
                                   value="<?php echo e($_POST['standard_code'] ?? ''); ?>" 
                                   placeholder="e.g., CLASS_10, GRADE_5" required>
                            <small class="text-muted">Unique code (use letters, numbers, underscore)</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"
                                      placeholder="Brief description"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" 
                                       value="<?php echo e($_POST['display_order'] ?? 0); ?>" min="0">
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Standard
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
                    <i class="fas fa-info-circle"></i> Help
                </div>
                <div class="card-body">
                    <h6>What is a Standard?</h6>
                    <p class="small">A standard represents a class or grade level within a board (e.g., Class 10, Grade 5).</p>
                    
                    <h6>Examples:</h6>
                    <ul class="small">
                        <li><strong>Class 10</strong> - CODE: CLASS_10</li>
                        <li><strong>Class 5</strong> - CODE: CLASS_5</li>
                        <li><strong>Grade 12</strong> - CODE: GRADE_12</li>
                    </ul>
                    
                    <div class="alert alert-info small">
                        <i class="fas fa-lightbulb"></i> Each standard must be associated with a board.
                    </div>
                </div>
            </div>

            <?php if (empty($boards)): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>No boards available!</strong> 
                            <a href="<?php echo base_url('admin/boards/add.php'); ?>">Add a board first</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
