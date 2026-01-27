<?php
// c:\xampp\htdocs\project\admin\subjects\list.php
// Subjects Management - List View

$_page_title = "Manage Subjects";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if (validate_csrf_token($_GET['token'] ?? '')) {
        $subject_id = (int)$_GET['id'];
        $deleted = $db->delete("DELETE FROM subjects WHERE subject_id = ?", [$subject_id]);
        
        if ($deleted) {
            log_activity(get_user_id(), 'delete', 'subject', $subject_id, 'Deleted subject');
            set_flash_message(MSG_SUCCESS, 'Subject deleted successfully!');
        } else {
            set_flash_message(MSG_ERROR, 'Failed to delete subject. It may have associated chapters.');
        }
        redirect(base_url('admin/subjects/list.php'));
    }
}

// Get all subjects with hierarchy
$subjects = $db->select("
    SELECT sub.*, std.standard_name, b.board_name
    FROM subjects sub
    INNER JOIN standards std ON sub.standard_id = std.standard_id
    INNER JOIN boards b ON std.board_id = b.board_id
    ORDER BY b.board_name, std.standard_name, sub.display_order, sub.subject_name ASC
");

// Get statistics
$total_subjects = count($subjects);
$active_subjects = $db->count('subjects', "status = 'active'");
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-book"></i> Manage Subjects</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Subjects</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url('admin/subjects/add.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Subject
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Total Subjects: <strong><?php echo $total_subjects; ?></strong></h5>
                    <p class="mb-0">Active: <span class="badge bg-success"><?php echo $active_subjects; ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Subjects
                </div>
                <div class="card-body">
                    <?php if (!empty($subjects)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Board</th>
                                        <th>Standard</th>
                                        <th>Subject Name</th>
                                        <th>Code</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                        <tr>
                                            <td><?php echo $subject['subject_id']; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo e($subject['board_name']); ?></span></td>
                                            <td><span class="badge bg-info"><?php echo e($subject['standard_name']); ?></span></td>
                                            <td><strong><?php echo e($subject['subject_name']); ?></strong></td>
                                            <td><span class="badge bg-primary"><?php echo e($subject['subject_code']); ?></span></td>
                                            <td><?php echo $subject['display_order']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $subject['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($subject['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo base_url('admin/subjects/edit.php?id=' . $subject['subject_id']); ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo base_url('admin/subjects/list.php?delete=1&id=' . $subject['subject_id'] . '&token=' . generate_csrf_token()); ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure? All associated chapters will also be deleted.');">
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
                            <i class="fas fa-info-circle"></i> No subjects found. 
                            <a href="<?php echo base_url('admin/subjects/add.php'); ?>">Add your first subject</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
