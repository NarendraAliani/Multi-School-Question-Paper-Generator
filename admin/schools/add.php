<?php
// admin/schools/add.php
$_page_title = "Add School";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB(); $errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) $errors[] = "Invalid token.";
    $school_name = sanitize_input($_POST["school_name"] ?? "");
    $school_code = sanitize_input($_POST["school_code"] ?? "");
    $contact_email = sanitize_input($_POST["contact_email"] ?? "");
    $contact_phone = sanitize_input($_POST["contact_phone"] ?? "");
    $status = sanitize_input($_POST["status"] ?? "active");
    
    if (empty($school_name)) $errors[] = "School name required.";
    if (empty($school_code)) $errors[] = "School code required.";
    elseif ($db->exists("schools", "school_code = ?", [$school_code])) $errors[] = "Code already exists.";
    
    if (empty($errors)) {
        $school_id = $db->insert("INSERT INTO schools (school_name, school_code, contact_email, contact_phone, status) VALUES (?, ?, ?, ?, ?)", [$school_name, $school_code, $contact_email, $contact_phone, $status]);
        if ($school_id) { log_activity(get_user_id(), "create", "school", $school_id, "Created: $school_name"); set_flash_message(MSG_SUCCESS, "School added!"); redirect(base_url("admin/schools/list.php")); }
        else $errors[] = "Failed.";
    }
}
?>
<div class="container-fluid mt-4">
    <div class="row"><div class="col-12">
        <h1><i class="fas fa-plus-circle"></i> Add New School</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/schools/list.php'); ?>">Schools</a></li>
            <li class="breadcrumb-item active">Add</li>
        </ol></nav>
    </div></div>
    <div class="row">
        <div class="col-lg-8">
            <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div><?php endif; ?>
            <form method="POST" action="">
                <?php echo csrf_token_field(); ?>
                <div class="card"><div class="card-header"><i class="fas fa-school"></i> School Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="school_name" value="<?php echo e($_POST['school_name'] ?? ''); ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="school_code" value="<?php echo e($_POST['school_code'] ?? ''); ?>" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Email</label>
                            <input type="email" class="form-control" name="contact_email" value="<?php echo e($_POST['contact_email'] ?? ''); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="contact_phone" value="<?php echo e($_POST['contact_phone'] ?? ''); ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                </div></div>
                <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    <a href="<?php echo base_url('admin/schools/list.php'); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>