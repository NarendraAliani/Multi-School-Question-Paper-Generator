<?php
// admin/users/add.php
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php"; require_once __DIR__ . "/../../includes/navbar.php";
$db = getDB(); $errors = []; $schools = $db->select("SELECT school_id, school_name FROM schools WHERE status = \"active\"");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) $errors[] = "Invalid token.";
    $full_name = sanitize_input($_POST["full_name"] ?? ""); $username = sanitize_input($_POST["username"] ?? "");
    $password = $_POST["password"] ?? ""; $role = sanitize_input($_POST["role"] ?? "teacher"); $school_id = (int)($_POST["school_id"] ?? 0);
    if (empty($full_name) || empty($username) || empty($password)) $errors[] = "Required.";
    elseif ($db->exists("users", "username = ?", [$username])) $errors[] = "Username taken.";
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $user_id = $db->insert("INSERT INTO users (school_id, username, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)", [$school_id?:null, $username, $hash, $full_name, $role]);
        if ($user_id) { set_flash_message(MSG_SUCCESS, "User added!"); redirect(base_url("admin/users/list.php")); }
        else $errors[] = "Failed.";
    }
}
?>
<div class="container-fluid mt-4">
    <div class="row"><div class="col-12"><h1><i class="fas fa-user-plus"></i> Add User</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/users/list.php'); ?>">Users</a></li>
            <li class="breadcrumb-item active">Add</li>
        </ol></nav>
    </div></div>
    <div class="row"><div class="col-lg-8">
        <?php if ($errors): ?><div class="alert alert-danger"><?php echo implode("<br>", $errors); ?></div><?php endif; ?>
        <form method="POST" action=""><?php echo csrf_token_field(); ?>
            <div class="card"><div class="card-header">User Info</div><div class="card-body">
                <div class="row"><div class="col-md-6 mb-3"><label>Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="full_name" value="<?php echo e($_POST["full_name"] ?? ""); ?>" required></div>
                    <div class="col-md-6 mb-3"><label>Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="username" value="<?php echo e($_POST["username"] ?? ""); ?>" required></div></div>
                <div class="row"><div class="col-md-6 mb-3"><label>Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" required minlength="6"></div>
                    <div class="col-md-6 mb-3"><label>Role</label>
                    <select class="form-select" name="role"><option value="teacher">Teacher</option><option value="school_admin">School Admin</option></select></div></div>
                <div class="mb-3"><label>School</label>
                    <select class="form-select" name="school_id"><option value="">Select</option>
                    <?php foreach ($schools as $s): ?><option value="<?php echo $s["school_id"]; ?>"><?php echo e($s["school_name"]); ?></option><?php endforeach; ?>
                    </select></div>
            </div></div>
            <div class="mt-3"><button type="submit" class="btn btn-primary">Save</button>
                <a href="<?php echo base_url('admin/users/list.php'); ?>" class="btn btn-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>