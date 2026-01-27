<?php
// c:\xampp\htdocs\project\teacher\papers\marking_scheme.php
// Marking Scheme & Teacher Notes Page

$_page_title = "Marking Scheme";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));

$db = getDB();
$paper_id = (int)($_GET["id"] ?? 0);
$user_id = get_user_id();
$school_id = get_school_id();

$paper = $db->selectOne("SELECT gp.*, b.board_name, std.standard_name, sub.subject_name, sch.school_name 
    FROM generated_papers gp 
    INNER JOIN boards b ON gp.board_id = b.board_id 
    INNER JOIN standards std ON gp.standard_id = std.standard_id 
    INNER JOIN subjects sub ON gp.subject_id = sub.subject_id 
    INNER JOIN schools sch ON gp.school_id = sch.school_id 
    WHERE gp.paper_id = ? AND gp.school_id = ?", [$paper_id, $school_id]);

if (!$paper) {
    set_flash_message(MSG_ERROR, "Paper not found.");
    redirect(base_url("teacher/papers/list.php"));
}

if (empty($paper['teacher_notes'])) {
    set_flash_message(MSG_WARNING, "This paper does not have any teacher notes or marking scheme.");
    redirect(base_url("teacher/papers/preview.php?id=" . $paper_id));
}

$questions = $db->select("SELECT gpq.*, q.question_text, q.question_type, q.explanation, q.marks 
    FROM generated_paper_questions gpq 
    INNER JOIN questions q ON gpq.question_id = q.question_id 
    WHERE gpq.paper_id = ? 
    ORDER BY gpq.question_order ASC", [$paper_id]);

$sections = [];
foreach ($questions as $q) {
    $section = $q["section_name"] ?? "General";
    if (!isset($sections[$section])) $sections[$section] = [];
    $sections[$section][] = $q;
}

$_additional_css = "<style>
@media print {
    .no-print, nav, .navbar, .card-header { display: none !important; }
    .card { border: none; box-shadow: none; }
    @page { size: A4; margin: 15mm; }
    .marking-item { page-break-inside: avoid; }
}
.marking-header { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #1976d2; }
.marking-item { border-left: 3px solid #1976d2; padding-left: 15px; margin-bottom: 15px; background: #f8f9fa; }
.notes-section { background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
</style>";

require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";
?>

<div class="container-fluid mt-4 no-print">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="<?php echo base_url("teacher/papers/preview.php?id=" . $paper_id); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Paper
                    </a>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Print Marking Scheme
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="text-center p-4 border-bottom">
                        <h2><?php echo e($paper["school_name"]); ?></h2>
                        <h4><?php echo e($paper["paper_title"]); ?> - Marking Scheme</h4>
                        <p class="mb-1"><strong>Code:</strong> <?php echo e($paper["paper_code"]); ?></p>
                        <p class="mb-1"><strong>Board:</strong> <?php echo e($paper["board_name"]); ?> | <strong>Class:</strong> <?php echo e($paper["standard_name"]); ?> | <strong>Subject:</strong> <?php echo e($paper["subject_name"]); ?></p>
                        <p class="mb-1"><strong>Total Marks:</strong> <?php echo $paper["total_marks"]; ?> | <strong>Time:</strong> <?php echo $paper["duration_minutes"]; ?> minutes</p>
                        <p class="text-danger mb-0"><strong>CONFIDENTIAL - FOR TEACHER/EXAMINER USE ONLY</strong></p>
                    </div>
                    
                    <div class="p-4">
                        <!-- Teacher Notes Section -->
                        <?php if (!empty($paper['teacher_notes'])): ?>
                            <div class="notes-section mb-4">
                                <h5 class="mb-3"><i class="fas fa-clipboard-list"></i> General Marking Guidelines</h5>
                                <div style="white-space: pre-wrap;"><?php echo e($paper['teacher_notes']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Question-wise Marking Scheme -->
                        <div class="marking-header">
                            <h5 class="mb-0"><i class="fas fa-list-ol"></i> Question-wise Marking Scheme</h5>
                        </div>
                        
                        <?php 
                        $question_counter = 1; 
                        $total_section_marks = 0;
                        foreach ($sections as $section_name => $section_questions): 
                        ?>
                            <div class="section-header mb-3 pb-2 border-bottom">
                                <h5><?php echo e($section_name); ?></h5>
                            </div>
                            
                            <?php foreach ($section_questions as $q): ?>
                                <div class="marking-item">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <p class="mb-1">
                                                <strong>Q<?php echo $question_counter; ?>.</strong> 
                                                <?php echo e(substr($q["question_text"], 0, 100)); ?><?php echo strlen($q["question_text"]) > 100 ? '...' : ''; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <span class="badge bg-primary fs-6"><?php echo (int)$q["marks"]; ?> Mark<?php echo $q["marks"] > 1 ? 's' : ''; ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($q["explanation"])): ?>
                                        <div class="mt-2 p-2 bg-white rounded border">
                                            <small><strong>Answer Guide/Solution:</strong> <?php echo e($q["explanation"]); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <div style="border-bottom: 1px dashed #ccc; margin: 10px 0;"></div>
                                        <small class="text-muted">Allocate marks based on steps/working shown. Full marks for complete correct answer.</small>
                                    </div>
                                </div>
                                <?php $question_counter++; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        
                        <!-- Summary -->
                        <div class="marking-header mt-4">
                            <h5 class="mb-0"><i class="fas fa-calculator"></i> Mark Distribution Summary</h5>
                        </div>
                        
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Section</th>
                                    <th>Number of Questions</th>
                                    <th>Total Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sections as $section_name => $section_questions): ?>
                                    <?php $section_marks = array_sum(array_column($section_questions, 'marks')); ?>
                                    <tr>
                                        <td><?php echo e($section_name); ?></td>
                                        <td><?php echo count($section_questions); ?></td>
                                        <td><?php echo (int)$section_marks; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td><strong><?php echo count($questions); ?></strong></td>
                                    <td><strong><?php echo $paper["total_marks"]; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Important Instructions -->
                        <div class="alert alert-warning mt-4">
                            <h6><i class="fas fa-exclamation-triangle"></i> Important Instructions for Examiner</h6>
                            <ul class="mb-0">
                                <li>Verify student identity before distributing the answer script</li>
                                <li>Mark each question independently - do not be influenced by other answers</li>
                                <li>Show working/step marks for partial credit</li>
                                <li>Use red ink for marking only</li>
                                <li>Sign and date after complete evaluation</li>
                                <li>Any discrepancy should be brought to the subject coordinator's notice</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
