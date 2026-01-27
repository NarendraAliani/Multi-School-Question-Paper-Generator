<?php
// admin/users/edit.php
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));
$db = getDB(); $user_id = (int)($_GET["id"] ?? 0);
$user = $db->selectOne("SELECT * FROM users WHERE user_id = ?", [$user_id]);
if (!$user) { set_flash_message(MSG_ERROR, "Not found."); redirect(base_url("admin/users/list.php")); }
require_once __DIR__ . "/../../includes/header.php"; require_once __DIR__ . "/../../includes/navbar.php";
$schools = $db->select("SELECT school_id, school_name FROM schools WHERE status = \"active\"");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = sanitize_input($_POST["full_name"] ?? ""); $username = sanitize_input($_POST["username"] ?? "");
    $role = sanitize_input($_POST["role"] ?? "teacher"); $school_id = (int)($_POST["school_id"] ?? 0);
    $password = $_POST["password"] ?? "";
    $params = [$full_name, $username, $role, $school_id ?: null, $user_id];
    $query = "UPDATE users SET full_name=?, username=?, role=?, school_id=? WHERE user_id=?";
    if ($password) { $hash = password_hash($password, PASSWORD_BCRYPT); $query = "UPDATE users SET full_name=?, username=?, password_hash=?, role=?, school_id=? WHERE user_id=?"; $params = [$full_name, $username, $hash, $role, $school_id ?: null, $user_id]; }
    $updated = $db->update($query, $params);
    if ($updated !== false) { set_flash_message(MSG_SUCCESS, "Updated!"); redirect(base_url("admin/users/list.php")); }
}
$form = $_SERVER["REQUEST_METHOD"] === "POST" ? $_POST : $user;
?>
<div class="container-fluid mt-4">
    <div class="row"><div class="col-12"><h1><i class="fas fa-user-edit"></i> Edit User</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/users/list.php'); ?>">Users</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div></div>
    <div class="row"><div class="col-lg-8">
        <form method="POST" action=""><?php echo csrf_token_field(); ?>
            <div class="card"><div class="card-header">User Info</div><div class="card-body">
                <div class="row"><div class="col-md-6 mb-3"><label>Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="full_name" value="<?php echo e($form["full_name"]); ?>" required></div>
                    <div class="col-md-6 mb-3"><label>Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="username" value="<?php echo e($form["username"]); ?>" required></div></div>
                <div class="row"><div class="col-md-6 mb-3"><label>New Password</label>
                    <input type="password" class="form-control" name="password" minlength="6"></div>
                    <div class="col-md-6 mb-3"><label>Role</label>
                    <select class="form-select" name="role"><option value="teacher" <?php echo $form["role"]=="teacher"?"selected":""; ?>>Teacher</option><option value="school_admin" <?php echo $form["role"]=="school_admin"?"selected":""; ?>>School Admin</option></select></div></div>
                <div class="mb-3"><label>School</label>
                    <select class="form-select" name="school_id"><option value="">None</option>
                    <?php foreach ($schools as $s): ?><option value="<?php echo $s["school_id"]; ?>" <?php echo $form["school_id"]==$s["school_id"]?"selected":""; ?>><?php echo e($s["school_name"]); ?></option><?php endforeach; ?>
                    </select></div>
            </div></div>
            <div class="mt-3"><button type="submit" class="btn btn-primary">Update</button>
                <a href="<?php echo base_url('admin/users/list.php'); ?>" class="btn btn-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>