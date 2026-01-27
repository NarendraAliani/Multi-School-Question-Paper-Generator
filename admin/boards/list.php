<?php
// c:\xampp\htdocs\project\admin\boards\list.php
// Boards Management - List View

$_page_title = "Manage Boards";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if (validate_csrf_token($_GET['token'] ?? '')) {
        $board_id = (int)$_GET['id'];
        $deleted = $db->delete("DELETE FROM boards WHERE board_id = ?", [$board_id]);
        
        if ($deleted) {
            log_activity(get_user_id(), 'delete', 'board', $board_id, 'Deleted board');
            set_flash_message(MSG_SUCCESS, 'Board deleted successfully!');
        } else {
            set_flash_message(MSG_ERROR, 'Failed to delete board. It may have associated data.');
        }
        redirect(base_url('admin/boards/list.php'));
    }
}

// Get all boards
$boards = $db->select("SELECT * FROM boards ORDER BY board_name ASC");

// Get statistics
$total_boards = count($boards);
$active_boards = $db->count('boards', "status = 'active'");
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-clipboard-list"></i> Manage Boards</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                                <li class="breadcrumb-item active">Boards</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url('admin/boards/add.php'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Board
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
                    <h5>Total Boards: <strong><?php echo $total_boards; ?></strong></h5>
                    <p class="mb-0">Active: <span class="badge bg-success"><?php echo $active_boards; ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Boards Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i> All Boards
                </div>
                <div class="card-body">
                    <?php if (!empty($boards)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Board Name</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($boards as $board): ?>
                                        <tr>
                                            <td><?php echo $board['board_id']; ?></td>
                                            <td><strong><?php echo e($board['board_name']); ?></strong></td>
                                            <td><span class="badge bg-primary"><?php echo e($board['board_code']); ?></span></td>
                                            <td><?php echo e(substr($board['description'] ?? '', 0, 50)); ?>...</td>
                                            <td>
                                                <span class="badge bg-<?php echo $board['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($board['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo format_date($board['created_at']); ?></td>
                                            <td>
                                                <a href="<?php echo base_url('admin/boards/edit.php?id=' . $board['board_id']); ?>" 
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo base_url('admin/boards/list.php?delete=1&id=' . $board['board_id'] . '&token=' . generate_csrf_token()); ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this board? All associated standards will also be deleted.');">
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
                            <i class="fas fa-info-circle"></i> No boards found. 
                            <a href="<?php echo base_url('admin/boards/add.php'); ?>">Add your first board</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
