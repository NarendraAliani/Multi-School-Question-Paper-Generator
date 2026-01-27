<?php
// c:\xampp\htdocs\project\teacher\papers\preview.php
// Preview and Print Paper

$_page_title = "Paper Preview";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));

$db = getDB();
$paper_id = (int)($_GET["id"] ?? 0);
$user_id = get_user_id();
$school_id = get_school_id();

$paper = $db->selectOne("SELECT gp.*, b.board_name, std.standard_name, sub.subject_name, sch.school_name, sch.logo, sch.paper_header_text, sch.watermark_enabled, sch.watermark_text FROM generated_papers gp INNER JOIN boards b ON gp.board_id = b.board_id INNER JOIN standards std ON gp.standard_id = std.standard_id INNER JOIN subjects sub ON gp.subject_id = sub.subject_id INNER JOIN schools sch ON gp.school_id = sch.school_id WHERE gp.paper_id = ? AND gp.school_id = ?", [$paper_id, $school_id]);

if (!$paper) {
    set_flash_message(MSG_ERROR, "Paper not found.");
    redirect(base_url("teacher/papers/list.php"));
}

$questions = $db->select("SELECT gpq.*, q.question_text, q.question_image, q.question_type, q.option_a, q.option_b, q.option_c, q.option_d FROM generated_paper_questions gpq INNER JOIN questions q ON gpq.question_id = q.question_id WHERE gpq.paper_id = ? ORDER BY gpq.question_order ASC", [$paper_id]);

$sections = [];
foreach ($questions as $q) {
    $section = $q["section_name"] ?? "General";
    if (!isset($sections[$section])) $sections[$section] = [];
    $sections[$section][] = $q;
}

if (isset($_POST["update_status"])) {
    if (validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) {
        $new_status = sanitize_input($_POST["status"] ?? "draft");
        $db->update("UPDATE generated_papers SET status = ? WHERE paper_id = ?", [$new_status, $paper_id]);
        set_flash_message(MSG_SUCCESS, "Status updated!");
        redirect(base_url("teacher/papers/preview.php?id=" . $paper_id));
    }
}

$_additional_css = "<style>
@media print {
    .no-print, nav, .navbar, .card-header { display: none !important; }
    .card { border: none; box-shadow: none; }
    @page { size: A4; margin: 15mm; }
    .question { page-break-inside: avoid; margin-bottom: 20px; }
    .watermark { display: block !important; }
}
.watermark { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(0,0,0,0.05); white-space: nowrap; pointer-events: none; z-index: 0; }
.paper-content { position: relative; z-index: 1; }
.school-logo { max-height: 80px; max-width: 150px; object-fit: contain; margin-bottom: 10px; }
</style>";

require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$paper["sections"] = $sections;
?>

<div class="container-fluid mt-4 no-print">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><a href="<?php echo base_url("teacher/papers/list.php"); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a></div>
                <div>
                    <?php if ($paper['has_answer_key']): ?>
                        <a href="<?php echo base_url("teacher/papers/answer_key.php?id=" . $paper_id); ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-key"></i> View Answer Key
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($paper['teacher_notes'])): ?>
                        <a href="<?php echo base_url("teacher/papers/marking_scheme.php?id=" . $paper_id); ?>" class="btn btn-info" target="_blank">
                            <i class="fas fa-clipboard-check"></i> View Marking Scheme
                        </a>
                    <?php endif; ?>
                    <form method="POST" class="d-inline-flex gap-2"><?php echo csrf_token_field(); ?><select name="status" class="form-select"><option value="draft" <?php echo $paper["status"] == "draft" ? "selected" : ""; ?>>Draft</option><option value="finalized" <?php echo $paper["status"] == "finalized" ? "selected" : ""; ?>>Finalized</option></select><button type="submit" name="update_status" class="btn btn-success"><i class="fas fa-check"></i> Update Status</button></form>
                    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Paper</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><h5 class="mb-0"><?php echo e($paper["paper_title"]); ?></h5><small>Code: <?php echo e($paper["paper_code"]); ?></small></div>
                    <div><span class="badge bg-<?php echo $paper["status"] == "finalized" ? "success" : "warning"; ?>"><?php echo ucfirst($paper["status"]); ?></span></div>
                </div>
                <div class="card-body p-0">
                    <!-- Watermark -->
                    <?php if (!empty($paper['watermark_enabled']) && !empty($paper['watermark_text'])): ?>
                        <div class="watermark"><?php echo e($paper['watermark_text']); ?></div>
                    <?php endif; ?>
                    
                    <div class="paper-content">
                    <div class="text-center p-4 border-bottom">
                        <?php if (!empty($paper['logo'])): ?>
                            <img src="<?php echo SCHOOLS_UPLOAD_URL . '/' . $paper['logo']; ?>" alt="School Logo" class="school-logo">
                            <br>
                        <?php endif; ?>
                        <h2><?php echo e($paper["school_name"]); ?></h2>
                        <?php if (!empty($paper['paper_header_text'])): ?>
                            <h5 class="text-muted"><?php echo e($paper['paper_header_text']); ?></h5>
                        <?php endif; ?>
                        <h4><?php echo e($paper["paper_title"]); ?></h4>
                        <p class="mb-1"><strong>Board:</strong> <?php echo e($paper["board_name"]); ?> | <strong>Class:</strong> <?php echo e($paper["standard_name"]); ?> | <strong>Subject:</strong> <?php echo e($paper["subject_name"]); ?></p>
                        <p class="mb-0"><strong>Total Marks:</strong> <?php echo $paper["total_marks"]; ?> | <strong>Time:</strong> <?php echo $paper["duration_minutes"]; ?> minutes</p>
                    </div>
                    <div class="p-4">
                        <?php if (!empty($paper['instructions'])): ?>
                            <div class="alert alert-light border mb-4">
                                <strong>Instructions:</strong><br>
                                <?php echo nl2br(e($paper['instructions'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php $question_counter = 1; foreach ($paper["sections"] as $section_name => $section_questions): ?>
                            <div class="section-header mb-3 pb-2 border-bottom"><h5><?php echo e($section_name); ?></h5></div>
                            <?php foreach ($section_questions as $q): ?>
                                <div class="question mb-4">
                                    <div class="d-flex">
                                        <span class="question-number me-2 fw-bold"><?php echo $question_counter; ?>.</span>
                                        <div class="question-content flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <p class="question-text mb-0 flex-grow-1"><?php echo e($q["question_text"]); ?></p>
                                                <span class="text-muted ms-3" style="white-space: nowrap;">[<?php echo (int)$q["marks"]; ?>]</span>
                                            </div>
                                            
                                            <?php if (!empty($q["question_image"])): ?>
                                                <img src="<?php echo QUESTIONS_UPLOAD_URL . "/" . $q["question_image"]; ?>" class="img-fluid mb-2" style="max-width: 400px; max-height: 300px;">
                                            <?php endif; ?>
                                            
                                            <?php if ($q["question_type"] === 'mcq' && !empty($q["option_a"])): ?>
                                                <div class="options mt-2">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-1">
                                                            <p class="mb-0"><strong>(a)</strong> <?php echo e($q["option_a"]); ?></p>
                                                        </div>
                                                        <div class="col-md-6 mb-1">
                                                            <p class="mb-0"><strong>(b)</strong> <?php echo e($q["option_b"]); ?></p>
                                                        </div>
                                                        <div class="col-md-6 mb-1">
                                                            <p class="mb-0"><strong>(c)</strong> <?php echo e($q["option_c"]); ?></p>
                                                        </div>
                                                        <div class="col-md-6 mb-1">
                                                            <p class="mb-0"><strong>(d)</strong> <?php echo e($q["option_d"]); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php $question_counter++; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                    </div> <!-- End paper-content -->
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . "/../../includes/footer.php"; ?>