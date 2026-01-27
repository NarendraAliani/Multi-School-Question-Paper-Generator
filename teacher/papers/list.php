<?php
// c:\xampp\htdocs\project\teacher\papers\list.php
// Generated Papers List

$_page_title = "My Papers";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();

if (isset($_GET["delete"]) && isset($_GET["id"])) {
    if (validate_csrf_token($_GET["token"] ?? "")) {
        $paper_id = (int)$_GET["id"];
        $deleted = $db->delete("DELETE FROM generated_papers WHERE paper_id = ? AND school_id = ?", [$paper_id, $school_id]);
        if ($deleted) {
            log_activity($user_id, "delete", "paper", $paper_id, "Deleted paper");
            set_flash_message(MSG_SUCCESS, "Paper deleted successfully!");
        }
        redirect(base_url("teacher/papers/list.php"));
    }
}

$papers = $db->select("SELECT gp.*, b.board_name, std.standard_name, sub.subject_name FROM generated_papers gp INNER JOIN boards b ON gp.board_id = b.board_id INNER JOIN standards std ON gp.standard_id = std.standard_id INNER JOIN subjects sub ON gp.subject_id = sub.subject_id WHERE gp.school_id = ? ORDER BY gp.generated_at DESC", [$school_id]);
$total_papers = count($papers);
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-file-alt"></i> My Papers</h1>
                        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?php echo base_url("teacher/dashboard.php"); ?>">Dashboard</a></li><li class="breadcrumb-item active">Papers</li></ol></nav>
                    </div>
                    <div>
                        <a href="<?php echo base_url("teacher/papers/generate_sets.php"); ?>" class="btn btn-success me-2">
                            <i class="fas fa-layer-group"></i> Generate Multiple Sets
                        </a>
                        <a href="<?php echo base_url("teacher/papers/create.php"); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Generate New Paper
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4"><div class="col-md-3"><div class="card"><div class="card-body"><h5>Total Papers: <strong><?php echo $total_papers; ?></strong></h5></div></div></div></div>
    <div class="row"><div class="col-12"><div class="card"><div class="card-header"><i class="fas fa-list"></i> Generated Papers</div><div class="card-body"><?php if (!empty($papers)): ?><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Code</th><th>Title</th><th>Board/Std/Sub</th><th>Marks</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($papers as $paper): ?><tr><td><span class="badge bg-primary"><?php echo e($paper["paper_code"]); ?></span></td><td><strong><?php echo e($paper["paper_title"]); ?></strong></td><td><?php echo e($paper["board_name"]); ?> - <?php echo e($paper["standard_name"]); ?></td><td><?php echo $paper["total_marks"]; ?></td><td><span class="badge bg-<?php echo $paper["status"] == "finalized" ? "success" : "warning"; ?>"><?php echo ucfirst($paper["status"]); ?></span></td><td><a href="<?php echo base_url("teacher/papers/preview.php?id=" . $paper["paper_id"]); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="alert alert-info">No papers generated yet.</div><?php endif; ?></div></div></div></div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>