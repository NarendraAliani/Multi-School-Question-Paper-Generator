<?php
// c:\xampp\htdocs\project\admin\chapters\list.php
// Chapters Management - List View

$_page_title = "Manage Chapters";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if (validate_csrf_token($_GET['token'] ?? '')) {
        $chapter_id = (int)$_GET['id'];
        $deleted = $db->delete("DELETE FROM chapters WHERE chapter_id = ?", [$chapter_id]);
        
        if ($deleted) {
            log_activity(get_user_id(), 'delete', 'chapter', $chapter_id, 'Deleted chapter');
            set_flash_message(MSG_SUCCESS, 'Chapter deleted successfully!');
        } else {
            set_flash_message(MSG_ERROR, 'Failed to delete chapter.');
        }
        redirect(base_url('admin/chapters/list.php'));
    }
}

// Get all chapters with hierarchy
$chapters = $db->select("
    SELECT ch.*, sub.subject_name, std.standard_name, b.board_name
    FROM chapters ch
    INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    INNER JOIN boards b ON std.board_id = b.board_id
    ORDER BY b.board_name, std.standard_name, sub.subject_name, ch.display_order, ch.chapter_name ASC
");

$total_chapters = count($chapters);
$active_chapters = $db->count('chapters', "status = 'active'");
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-list-ol"></i> Manage Chapters</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Chapters</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url('admin/chapters/add.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Chapter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Total Chapters: <strong><?php echo $total_chapters; ?></strong></h5>
                    <p class="mb-0">Active: <span class="badge bg-success"><?php echo $active_chapters; ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Chapters
                </div>
                <div class="card-body">
                    <?php if (!empty($chapters)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Board</th>
                                        <th>Standard</th>
                                        <th>Subject</th>
                                        <th>Chapter</th>
                                        <th>Number</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($chapters as $chapter): ?>
                                        <tr>
                                            <td><?php echo $chapter['chapter_id']; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo e($chapter['board_name']); ?></span></td>
                                            <td><span class="badge bg-info"><?php echo e($chapter['standard_name']); ?></span></td>
                                            <td><span class="badge bg-warning"><?php echo e($chapter['subject_name']); ?></span></td>
                                            <td><strong><?php echo e($chapter['chapter_name']); ?></strong></td>
                                            <td><?php echo e($chapter['chapter_number']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $chapter['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($chapter['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo base_url('admin/chapters/edit.php?id=' . $chapter['chapter_id']); ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo base_url('admin/chapters/list.php?delete=1&id=' . $chapter['chapter_id'] . '&token=' . generate_csrf_token()); ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No chapters found. 
                            <a href="<?php echo base_url('admin/chapters/add.php'); ?>">Add your first chapter</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
