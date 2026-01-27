<?php
// c:\xampp\htdocs\project\teacher\blueprints\list.php
// List Paper Blueprints - Teacher/School Admin

$_page_title = "Paper Blueprints";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$school_id = get_school_id();

// Fetch blueprints with subject and board names
$blueprints = $db->select("
    SELECT b.*, s.subject_name, std.standard_name, brd.board_name 
    FROM paper_blueprints b 
    JOIN subjects s ON b.subject_id = s.subject_id 
    JOIN standards std ON b.standard_id = std.standard_id 
    JOIN boards brd ON b.board_id = brd.board_id 
    WHERE b.school_id = ? 
    ORDER BY b.created_at DESC", 
    [$school_id]
);

$csrf_token = generate_csrf_token();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-copy"></i> Paper Blueprints</h1>
                <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Blueprint</a>
            </div>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Blueprints</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Blueprints</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($blueprints)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-copy fa-4x text-gray-300 mb-3" style="color: #dddfeb;"></i>
                            <p class="text-muted">No blueprints found. Create one to start generating papers quickly.</p>
                            <a href="add.php" class="btn btn-primary">Create Your First Blueprint</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Board / Std / Subject</th>
                                        <th>Total Marks</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blueprints as $b): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-primary"><?php echo e($b['blueprint_name']); ?></div>
                                                <small class="text-muted">Created: <?php echo date('d M Y', strtotime($b['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo e($b['board_name']); ?></span>
                                                <span class="badge bg-info text-dark"><?php echo e($b['standard_name']); ?></span>
                                                <div class="mt-1 small font-weight-bold"><?php echo e($b['subject_name']); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="h5"><?php echo $b['total_marks']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php echo $b['duration_minutes']; ?> min
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $b['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($b['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo base_url('teacher/papers/create.php?blueprint_id='.$b['blueprint_id']); ?>" class="btn btn-sm btn-success" title="Generate Paper">
                                                        <i class="fas fa-magic"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $b['blueprint_id']; ?>" class="btn btn-sm btn-info text-white" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?php echo $b['blueprint_id']; ?>&token=<?php echo $csrf_token; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this blueprint?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
