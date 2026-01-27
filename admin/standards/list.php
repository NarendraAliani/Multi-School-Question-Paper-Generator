<?php
// c:\xampp\htdocs\project\admin\standards\list.php
// Standards/Classes Management - List View

$_page_title = "Manage Standards";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if (validate_csrf_token($_GET['token'] ?? '')) {
        $standard_id = (int)$_GET['id'];
        $deleted = $db->delete("DELETE FROM standards WHERE standard_id = ?", [$standard_id]);
        
        if ($deleted) {
            log_activity(get_user_id(), 'delete', 'standard', $standard_id, 'Deleted standard');
            set_flash_message(MSG_SUCCESS, 'Standard deleted successfully!');
        } else {
            set_flash_message(MSG_ERROR, 'Failed to delete standard. It may have associated subjects.');
        }
        redirect(base_url('admin/standards/list.php'));
    }
}

// Get all standards with board info
$standards = $db->select("
    SELECT s.*, b.board_name, b.board_code 
    FROM standards s 
    INNER JOIN boards b ON s.board_id = b.board_id 
    ORDER BY b.board_name, s.display_order, s.standard_name ASC
");

// Get statistics
$total_standards = count($standards);
$active_standards = $db->count('standards', "status = 'active'");
$boards = $db->select("SELECT board_id, board_name FROM boards WHERE status = 'active' ORDER BY board_name");
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-graduation-cap"></i> Manage Standards/Classes</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Standards</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url('admin/standards/add.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Standard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Total Standards: <strong><?php echo $total_standards; ?></strong></h5>
                    <p class="mb-0">Active: <span class="badge bg-success"><?php echo $active_standards; ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Total Boards: <strong><?php echo count($boards); ?></strong></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Standards Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Standards/Classes
                </div>
                <div class="card-body">
                    <?php if (!empty($standards)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Board</th>
                                        <th>Standard Name</th>
                                        <th>Code</th>
                                        <th>Display Order</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($standards as $standard): ?>
                                        <tr>
                                            <td><?php echo $standard['standard_id']; ?></td>
                                            <td><span class="badge bg-info"><?php echo e($standard['board_name']); ?></span></td>
                                            <td><strong><?php echo e($standard['standard_name']); ?></strong></td>
                                            <td><span class="badge bg-primary"><?php echo e($standard['standard_code']); ?></span></td>
                                            <td><?php echo $standard['display_order']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $standard['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($standard['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo format_date($standard['created_at']); ?></td>
                                            <td>
                                                <a href="<?php echo base_url('admin/standards/edit.php?id=' . $standard['standard_id']); ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo base_url('admin/standards/list.php?delete=1&id=' . $standard['standard_id'] . '&token=' . generate_csrf_token()); ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure? All associated subjects will also be deleted.');">
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
                            <i class="fas fa-info-circle"></i> No standards found. 
                            <a href="<?php echo base_url('admin/standards/add.php'); ?>">Add your first standard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
