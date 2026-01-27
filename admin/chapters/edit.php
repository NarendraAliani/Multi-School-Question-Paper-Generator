<?php
// c:\xampp\htdocs\project\admin\chapters\edit.php
// Edit Chapter with AJAX Chain

$_page_title = "Edit Chapter";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));

$db = getDB();
$errors = [];
$chapter_id = (int)($_GET['id'] ?? 0);

$chapter = $db->selectOne("
    SELECT ch.*, std.board_id, b.board_name, std.standard_name, sub.subject_name
    FROM chapters ch
    INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    INNER JOIN boards b ON std.board_id = b.board_id
    WHERE ch.chapter_id = ?
", [$chapter_id]);

if (!$chapter) {
    set_flash_message(MSG_ERROR, 'Chapter not found.');
    redirect(base_url('admin/chapters/list.php'));
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");
$standards = $db->select("SELECT standard_id, standard_name FROM standards WHERE board_id = ? AND status = 'active' ORDER BY display_order", [$chapter['board_id']]);
$subjects = $db->select("SELECT subject_id, subject_name FROM subjects WHERE standard_id = ? AND status = 'active' ORDER BY display_order", [$chapter['standard_id']]);

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
    
    if (empty($board_id) || empty($standard_id) || empty($subject_id) || empty($chapter_name)) {
        $errors[] = 'Please fill all required fields.';
    }
    
    if (empty($errors)) {
        $query = "UPDATE chapters SET subject_id = ?, chapter_name = ?, chapter_number = ?, description = ?, display_order = ?, status = ? WHERE chapter_id = ?";
        $updated = $db->update($query, [$subject_id, $chapter_name, $chapter_number, $description, $display_order, $status, $chapter_id]);
        
        if ($updated !== false) {
            log_activity(get_user_id(), 'update', 'chapter', $chapter_id, "Updated chapter: $chapter_name");
            set_flash_message(MSG_SUCCESS, 'Chapter updated successfully!');
            redirect(base_url('admin/chapters/list.php'));
        } else {
            $errors[] = 'Failed to update chapter.';
        }
    }
}

$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $chapter;
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Edit Chapter</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('admin/chapters/list.php'); ?>">Chapters</a></li>
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
                                    <option value="<?php echo $b['board_id']; ?>" <?php echo ($form_data['board_id'] == $b['board_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($b['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="standard_id" class="form-label">Standard/Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="standard_id" name="standard_id" required>
                                <?php foreach ($standards as $s): ?>
                                    <option value="<?php echo $s['standard_id']; ?>" <?php echo ($form_data['standard_id'] == $s['standard_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($s['standard_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?php echo $s['subject_id']; ?>" <?php echo ($form_data['subject_id'] == $s['subject_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($s['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="chapter_name" class="form-label">Chapter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="chapter_name" name="chapter_name" 
                                   value="<?php echo e($form_data['chapter_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="chapter_number" class="form-label">Chapter Number</label>
                            <input type="text" class="form-control" id="chapter_number" name="chapter_number" 
                                   value="<?php echo e($form_data['chapter_number']); ?>">
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
                                <i class="fas fa-save"></i> Update Chapter
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
                    <i class="fas fa-info-circle"></i> Chapter Details
                </div>
                <div class="card-body">
                    <p><strong>Chapter ID:</strong> <?php echo $chapter['chapter_id']; ?></p>
                    <p><strong>Board:</strong> <?php echo e($chapter['board_name']); ?></p>
                    <p><strong>Standard:</strong> <?php echo e($chapter['standard_name']); ?></p>
                    <p><strong>Subject:</strong> <?php echo e($chapter['subject_name']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
