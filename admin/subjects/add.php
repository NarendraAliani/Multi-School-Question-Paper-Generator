<?php
// c:\xampp\htdocs\project\admin\subjects\add.php
// Add New Subject with AJAX Dependent Dropdowns

$_page_title = "Add Subject";
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
    } elseif ($db->exists('subjects', 'standard_id = ? AND subject_code = ?', [$standard_id, $subject_code])) {
        $errors[] = 'Subject code already exists for this standard.';
    }
    
    // If no errors, insert
    if (empty($errors)) {
        $query = "INSERT INTO subjects (standard_id, subject_name, subject_code, description, display_order, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $subject_id = $db->insert($query, [$standard_id, $subject_name, $subject_code, $description, $display_order, $status]);
        
        if ($subject_id) {
            log_activity(get_user_id(), 'create', 'subject', $subject_id, "Created subject: $subject_name");
            set_flash_message(MSG_SUCCESS, 'Subject added successfully!');
            redirect(base_url('admin/subjects/list.php'));
        } else {
            $errors[] = 'Failed to add subject. Please try again.';
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Subject</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/subjects/list.php'); ?>">Subjects</a></li>
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
                                            <?php echo (isset($_POST['board_id']) && $_POST['board_id'] == $board['board_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($board['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Standard Selection (AJAX Dependent) -->
                        <div class="mb-3">
                            <label for="standard_id" class="form-label">Standard/Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="standard_id" name="standard_id" required disabled>
                                <option value="">Select Board First</option>
                            </select>
                            <small class="text-muted">This will populate after selecting a board</small>
                        </div>

                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" 
                                   value="<?php echo e($_POST['subject_name'] ?? ''); ?>" 
                                   placeholder="e.g., Mathematics, Science, English" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject_code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" 
                                   value="<?php echo e($_POST['subject_code'] ?? ''); ?>" 
                                   placeholder="e.g., MATH, SCI, ENG" required>
                            <small class="text-muted">Unique code for this subject</small>
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
                                <i class="fas fa-save"></i> Save Subject
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
                    <i class="fas fa-info-circle"></i> Help
                </div>
                <div class="card-body">
                    <h6>Dependent Dropdowns</h6>
                    <p class="small">The Standard dropdown will populate automatically after selecting a Board. This uses AJAX for seamless experience.</p>
                    
                    <h6>Examples:</h6>
                    <ul class="small">
                        <li><strong>Mathematics</strong> - CODE: MATH</li>
                        <li><strong>Science</strong> - CODE: SCI</li>
                        <li><strong>English</strong> - CODE: ENG</li>
                    </ul>
                    
                    <div class="alert alert-info small">
                        <i class="fas fa-lightbulb"></i> Select Board → Standard in order
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
