<?php
// admin/users/list.php
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php"; require_once __DIR__ . "/../../includes/navbar.php";
$db = getDB(); $users = $db->select("SELECT u.*, s.school_name FROM users u LEFT JOIN schools s ON u.school_id = s.school_id ORDER BY u.created_at DESC");
?>
<div class="container-fluid mt-4">
    <div class="row"><div class="col-12">
        <h1><i class="fas fa-users"></i> Manage Users</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol></nav>
    </div></div>
    <div class="row mb-4"><div class="col-md-4"><div class="card"><div class="card-body"><h5>Total: <strong><?php echo count($users); ?></strong></h5></div></div></div></div>
    <div class="row"><div class="col-12">
        <div class="card"><div class="card-header"><i class="fas fa-list"></i> All Users</div>
        <div class="card-body"><table class="table table-hover">
            <thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th><th>School</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u["user_id"]; ?></td>
                    <td><strong><?php echo e($u["full_name"]); ?></strong></td>
                    <td><span class="badge bg-primary"><?php echo e($u["username"]); ?></span></td>
                    <td><span class="badge bg-<?php echo $u["role"]=="school_admin"?"info":"warning"; ?>"><?php echo ucfirst($u["role"]); ?></span></td>
                    <td><?php echo $u["school_name"] ? e($u["school_name"]) : "-"; ?></td>
                    <td><span class="badge bg-<?php echo $u["status"]=="active"?"success":"secondary"; ?>"><?php echo ucfirst($u["status"]); ?></span></td>
                    <td><a href="<?php echo base_url("admin/users/edit.php?id=".$u["user_id"]); ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        </div>
    </div></div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>