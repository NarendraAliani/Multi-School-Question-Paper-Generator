<?php
// c:\xampp\htdocs\project\admin\chapters\add.php
// Add New Chapter with Full AJAX Chain

$_page_title = "Add Chapter";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$errors = [];

$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token.';
    }
    
    $board_id = (int)($_POST['board_id'] ?? 0);
    $standard_id = (int)($_POST['standard_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $chapter_name = sanitize_input($_POST['chapter_name'] ?? '');
    $chapter_number = sanitize_input($_POST['chapter_number'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'active');
    
    if (empty($board_id)) $errors[] = 'Please select a board.';
    if (empty($standard_id)) $errors[] = 'Please select a standard.';
    if (empty($subject_id)) $errors[] = 'Please select a subject.';
    if (empty($chapter_name)) $errors[] = 'Chapter name is required.';
    
    if (empty($errors)) {
        $query = "INSERT INTO chapters (subject_id, chapter_name, chapter_number, description, display_order, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $chapter_id = $db->insert($query, [$subject_id, $chapter_name, $chapter_number, $description, $display_order, $status]);
        
        if ($chapter_id) {
            log_activity(get_user_id(), 'create', 'chapter', $chapter_id, "Created chapter: $chapter_name");
            set_flash_message(MSG_SUCCESS, 'Chapter added successfully!');
            redirect(base_url('admin/chapters/list.php'));
        } else {
            $errors[] = 'Failed to add chapter.';
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Chapter</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/chapters/list.php'); ?>">Chapters</a></li>
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
                    <i class="fas fa-list-ol"></i> Chapter Information
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong>
                            <ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php echo csrf_token_field(); ?>
                        
                        <div class="mb-3">
                            <label for="board_id" class="form-label">Board <span class="text-danger">*</span></label>
                            <select class="form-select" id="board_id" name="board_id" required>
                                <option value="">Select Board</option>
                                <?php foreach ($boards as $b): ?>
                                    <option value="<?php echo $b['board_id']; ?>" <?php echo (isset($_POST['board_id']) && $_POST['board_id'] == $b['board_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($b['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="standard_id" class="form-label">Standard/Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="standard_id" name="standard_id" required disabled>
                                <option value="">Select Board First</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_id" name="subject_id" required disabled>
                                <option value="">Select Standard First</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="chapter_name" class="form-label">Chapter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="chapter_name" name="chapter_name" 
                                   value="<?php echo e($_POST['chapter_name'] ?? ''); ?>" placeholder="e.g., Real Numbers" required>
                        </div>

                        <div class="mb-3">
                            <label for="chapter_number" class="form-label">Chapter Number</label>
                            <input type="text" class="form-control" id="chapter_number" name="chapter_number" 
                                   value="<?php echo e($_POST['chapter_number'] ?? ''); ?>" placeholder="e.g., 1, 2, 3">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo e($_POST['description'] ?? ''); ?></textarea>
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
                                <i class="fas fa-save"></i> Save Chapter
                            </button>
                            <a href="<?php echo base_url('admin/chapters/list.php'); ?>" class="btn btn-secondary">
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
                    <i class="fas fa-info-circle"></i> Full Dependency Chain
                </div>
                <div class="card-body">
                    <p class="small">This module uses <strong>AJAX dependent dropdowns</strong> for the complete hierarchy:</p>
                    <ol class="small">
                        <li>Select <strong>Board</strong></li>
                        <li>Standards load automatically</li>
                        <li>Select <strong>Standard</strong></li>
                        <li>Subjects load automatically</li>
                        <li>Select <strong>Subject</strong></li>
                        <li>Add <strong>Chapter</strong></li>
                    </ol>
                    <div class="alert alert-info small">
                        <i class="fas fa-lightbulb"></i> Each step populates the next via AJAX
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
