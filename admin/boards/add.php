<?php
// c:\xampp\htdocs\project\admin\boards\add.php
// Add New Board

$_page_title = "Add Board";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$errors = [];
$success = '';

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
    } elseif ($db->exists('boards', 'board_code = ?', [$board_code])) {
        $errors[] = 'Board code already exists. Please use a different code.';
    }
    
    // If no errors, insert
    if (empty($errors)) {
        $query = "INSERT INTO boards (board_name, board_code, description, status) 
                  VALUES (?, ?, ?, ?)";
        
        $board_id = $db->insert($query, [$board_name, $board_code, $description, $status]);
        
        if ($board_id) {
            log_activity(get_user_id(), 'create', 'board', $board_id, "Created board: $board_name");
            set_flash_message(MSG_SUCCESS, 'Board added successfully!');
            redirect(base_url('admin/boards/list.php'));
        } else {
            $errors[] = 'Failed to add board. Please try again.';
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Board</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/boards/list.php'); ?>">Boards</a></li>
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
                                   value="<?php echo e($_POST['board_name'] ?? ''); ?>" 
                                   placeholder="e.g., CBSE, ICSE, State Board" required>
                            <small class="text-muted">Full name of the educational board</small>
                        </div>

                        <div class="mb-3">
                            <label for="board_code" class="form-label">Board Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="board_code" name="board_code" 
                                   value="<?php echo e($_POST['board_code'] ?? ''); ?>" 
                                   placeholder="e.g., CBSE, ICSE" required>
                            <small class="text-muted">Unique code for the board (letters and numbers, no spaces)</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Brief description about the board"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Board
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
                    <i class="fas fa-info-circle"></i> Help
                </div>
                <div class="card-body">
                    <h6>What is a Board?</h6>
                    <p class="small">A board represents an educational board or curriculum authority (e.g., CBSE, ICSE, State Boards).</p>
                    
                    <h6>Examples:</h6>
                    <ul class="small">
                        <li><strong>CBSE</strong> - Central Board of Secondary Education</li>
                        <li><strong>ICSE</strong> - Indian Certificate of Secondary Education</li>
                        <li><strong>MSBSHSE</strong> - Maharashtra State Board</li>
                    </ul>
                    
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i> Board code must be unique and cannot be changed later.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
