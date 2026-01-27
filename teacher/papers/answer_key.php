<?php
// c:\xampp\htdocs\project\teacher\papers\answer_key.php
// Answer Key Page with Correct Answers

$_page_title = "Answer Key";
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

if (!$paper['has_answer_key']) {
    set_flash_message(MSG_WARNING, "This paper does not have an answer key enabled.");
    redirect(base_url("teacher/papers/preview.php?id=" . $paper_id));
}

$questions = $db->select("SELECT gpq.*, q.question_text, q.question_type, q.correct_answer, q.explanation, q.option_a, q.option_b, q.option_c, q.option_d 
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
    .answer-item { page-break-inside: avoid; margin-bottom: 10px; }
}
.answer-key-header { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
.answer-item { border-left: 3px solid #28a745; padding-left: 15px; margin-bottom: 15px; }
.correct-answer { color: #28a745; font-weight: bold; }
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
                    <button onclick="window.print()" class="btn btn-success">
                        <i class="fas fa-print"></i> Print Answer Key
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
                        <h4><?php echo e($paper["paper_title"]); ?> - ANSWER KEY</h4>
                        <p class="mb-1"><strong>Code:</strong> <?php echo e($paper["paper_code"]); ?></p>
                        <p class="mb-1"><strong>Board:</strong> <?php echo e($paper["board_name"]); ?> | <strong>Class:</strong> <?php echo e($paper["standard_name"]); ?> | <strong>Subject:</strong> <?php echo e($paper["subject_name"]); ?></p>
                        <p class="text-danger mb-0"><strong>CONFIDENTIAL - FOR TEACHER USE ONLY</strong></p>
                    </div>
                    
                    <div class="p-4">
                        <div class="answer-key-header">
                            <h5 class="mb-0"><i class="fas fa-key"></i> Answer Key & Marking Scheme</h5>
                        </div>
                        
                        <?php 
                        $question_counter = 1; 
                        foreach ($sections as $section_name => $section_questions): 
                        ?>
                            <div class="section-header mb-3 pb-2 border-bottom">
                                <h5><?php echo e($section_name); ?></h5>
                            </div>
                            
                            <?php foreach ($section_questions as $q): ?>
                                <div class="answer-item">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <p class="mb-1">
                                                <strong>Q<?php echo $question_counter; ?>.</strong> 
                                                <?php echo e(substr($q["question_text"], 0, 100)); ?><?php echo strlen($q["question_text"]) > 100 ? '...' : ''; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-2">
                                            <?php if ($q["question_type"] === 'mcq' && !empty($q["correct_answer"])): ?>
                                                <p class="mb-0">
                                                    <strong>Answer:</strong> 
                                                    <span class="correct-answer"><?php echo e($q["correct_answer"]); ?></span>
                                                </p>
                                            <?php elseif ($q["question_type"] !== 'mcq'): ?>
                                                <p class="mb-0 text-muted"><em>Subjective</em></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <span class="badge bg-primary"><?php echo (int)$q["marks"]; ?> Mark<?php echo $q["marks"] > 1 ? 's' : ''; ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($q["question_type"] === 'mcq' && !empty($q["correct_answer"])): ?>
                                        <div class="mt-2 ms-3">
                                            <small class="text-muted">
                                                <?php
                                                $correct_option = strtolower($q["correct_answer"]);
                                                if ($correct_option === 'a') echo '<strong>(a)</strong> ' . e($q["option_a"]);
                                                elseif ($correct_option === 'b') echo '<strong>(b)</strong> ' . e($q["option_b"]);
                                                elseif ($correct_option === 'c') echo '<strong>(c)</strong> ' . e($q["option_c"]);
                                                elseif ($correct_option === 'd') echo '<strong>(d)</strong> ' . e($q["option_d"]);
                                                ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($q["explanation"])): ?>
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small><strong>Explanation:</strong> <?php echo e($q["explanation"]); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php $question_counter++; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
