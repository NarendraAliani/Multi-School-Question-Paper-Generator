<?php
// admin/schools/edit.php
$_page_title = "Edit School";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission(ROLE_SUPER_ADMIN, base_url("auth/login.php"));

$db = getDB(); $errors = []; $school_id = (int)($_GET["id"] ?? 0);
$school = $db->selectOne("SELECT * FROM schools WHERE school_id = ?", [$school_id]);
if (!$school) { set_flash_message(MSG_ERROR, "Not found."); redirect(base_url("admin/schools/list.php")); }

require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) $errors[] = "Invalid token.";
    $school_name = sanitize_input($_POST["school_name"] ?? "");
    $school_code = sanitize_input($_POST["school_code"] ?? "");
    $contact_email = sanitize_input($_POST["contact_email"] ?? "");
    $contact_phone = sanitize_input($_POST["contact_phone"] ?? "");
    $status = sanitize_input($_POST["status"] ?? "active");
    
    if (empty($school_name)) $errors[] = "Name required.";
    if (empty($errors)) {
        $updated = $db->update("UPDATE schools SET school_name=?, school_code=?, contact_email=?, contact_phone=?, status=? WHERE school_id=?", [$school_name, $school_code, $contact_email, $contact_phone, $status, $school_id]);
        if ($updated !== false) { log_activity(get_user_id(), "update", "school", $school_id, "Updated"); set_flash_message(MSG_SUCCESS, "Updated!"); redirect(base_url("admin/schools/list.php")); }
        else $errors[] = "Failed.";
    }
}
$form = $_SERVER["REQUEST_METHOD"] === "POST" ? $_POST : $school;
?>
<div class="container-fluid mt-4">
    <div class="row"><div class="col-12">
        <h1><i class="fas fa-edit"></i> Edit School</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/dashboard.php'); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo base_url('admin/schools/list.php'); ?>">Schools</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div></div>
    <div class="row">
        <div class="col-lg-8">
            <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div><?php endif; ?>
            <form method="POST" action=""><?php echo csrf_token_field(); ?>
                <div class="card"><div class="card-header"><i class="fas fa-school"></i> School Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="school_name" value="<?php echo e($form['school_name']); ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="school_code" value="<?php echo e($form['school_code']); ?>" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Email</label>
                            <input type="email" class="form-control" name="contact_email" value="<?php echo e($form['contact_email']); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="contact_phone" value="<?php echo e($form['contact_phone']); ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo $form['status']=='active'?'selected':''; ?>>Active</option>
                            <option value="inactive" <?php echo $form['status']=='inactive'?'selected':''; ?>>Inactive</option>
                        </select></div>
                </div></div>
                <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    <a href="<?php echo base_url('admin/schools/list.php'); ?>" class="btn btn-secondary">Cancel</a></div>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card"><div class="card-header"><i class="fas fa-info-circle"></i> Details</div>
            <div class="card-body"><p><strong>ID:</strong> <?php echo $school['school_id']; ?></p></div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>