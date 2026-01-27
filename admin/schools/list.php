<?php
// admin/schools/list.php
$_page_title = "Manage Schools";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$schools = $db->select("SELECT * FROM schools ORDER BY created_at DESC");
$total_schools = count($schools);
$active_schools = $db->count("schools", "status = 'active'");
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-school"></i> Manage Schools</h1>
            <nav aria-label="breadcrumb"><ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Schools</li>
            </ol></nav>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <h5>Total: <strong><?php echo $total_schools; ?></strong></h5>
                <p class="mb-0">Active: <span class="badge bg-success"><?php echo $active_schools; ?></span></p>
            </div></div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fas fa-list"></i> All Schools</div>
                <div class="card-body">
                    <?php if (!empty($schools)): ?>
                        <table class="table table-hover">
                            <thead><tr><th>ID</th><th>Name</th><th>Code</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($schools as $s): ?>
                                <tr>
                                    <td><?php echo $s['school_id']; ?></td>
                                    <td><strong><?php echo e($s['school_name']); ?></strong></td>
                                    <td><span class="badge bg-primary"><?php echo e($s['school_code']); ?></span></td>
                                    <td><?php echo e($s['contact_email']); ?></td>
                                    <td><span class="badge bg-<?php echo $s['status']=='active'?'success':'secondary'; ?>"><?php echo ucfirst($s['status']); ?></span></td>
                                    <td>
                                        <a href="<?php echo base_url('admin/schools/edit.php?id='.$s['school_id']); ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No schools. <a href="<?php echo base_url('admin/schools/add.php'); ?>">Add first school</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>