<?php
// c:\xampp\htdocs\project\teacher\papers\create.php
// Generate Paper from Blueprint

$_page_title = "Generate Paper";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();
$errors = [];

// Get all active blueprints for this school
$blueprints = $db->select("
    SELECT b.*, s.subject_name, std.standard_name 
    FROM paper_blueprints b 
    JOIN subjects s ON b.subject_id = s.subject_id 
    JOIN standards std ON b.standard_id = std.standard_id 
    WHERE b.school_id = ? AND b.status = 'active'
    ORDER BY b.blueprint_name ASC", 
    [$school_id]
);

$selected_blueprint_id = (int)($_GET['blueprint_id'] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) {
        $errors[] = "Invalid security token.";
    }
    
    $blueprint_id = (int)($_POST["blueprint_id"] ?? 0);
    $paper_title = sanitize_input($_POST["paper_title"] ?? "");
    $instructions = sanitize_input($_POST["instructions"] ?? "");
    $teacher_notes = sanitize_input($_POST["teacher_notes"] ?? "");
    $generate_answer_key = isset($_POST["generate_answer_key"]) ? 1 : 0;
    
    if (empty($blueprint_id)) {
        $errors[] = "Please select a blueprint.";
    }
    if (empty($paper_title)) {
        $errors[] = "Paper title is required.";
    }
    
    if (empty($errors)) {
        require_once MODULES_PATH . "/paper_generator/generate.php";
        $generator = new PaperGenerator($school_id, $user_id);
        
        $options = [
            'paper_title' => $paper_title,
            'instructions' => $instructions,
            'generate_answer_key' => $generate_answer_key,
            'teacher_notes' => $teacher_notes
        ];
        
        $result = $generator->generateFromBlueprint($blueprint_id, $options);
        
        if ($result["success"]) {
            set_flash_message(MSG_SUCCESS, "Paper generated successfully! Code: " . $result["paper_code"]);
            redirect(base_url("teacher/papers/preview.php?id=" . $result["paper_id"]));
        } else {
            $errors[] = $result["message"];
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-magic"></i> Generate Question Paper</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/papers/list.php'); ?>">Papers</a></li>
                        <li class="breadcrumb-item active">Generate</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Paper Generation Details</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($blueprints)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No blueprints found. 
                            You need to <a href="<?php echo base_url('teacher/blueprints/add.php'); ?>">create a blueprint</a> first before generating a paper.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <?php echo csrf_token_field(); ?>
                            
                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Select Blueprint <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" name="blueprint_id" id="blueprint_id" required>
                                    <option value="">-- Choose a Blueprint --</option>
                                    <?php foreach ($blueprints as $bp): ?>
                                        <option value="<?php echo $bp['blueprint_id']; ?>" 
                                            <?php echo ($selected_blueprint_id == $bp['blueprint_id']) ? 'selected' : ''; ?>
                                            data-name="<?php echo e($bp['blueprint_name']); ?>"
                                            data-instr="<?php echo e($bp['instructions']); ?>">
                                            <?php echo e($bp['blueprint_name']); ?> (<?php echo e($bp['standard_name']); ?> - <?php echo e($bp['subject_name']); ?>, <?php echo $bp['total_marks']; ?> Marks)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">The paper will be generated based on the sections and criteria defined in this blueprint.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Paper Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="paper_title" id="paper_title" 
                                       value="<?php echo e($_POST['paper_title'] ?? ''); ?>" 
                                       placeholder="e.g. Mid Term Exam - 2024" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Instructions for this Paper</label>
                                <textarea class="form-control" name="instructions" id="instructions" rows="3" 
                                          placeholder="Enter specific instructions for this instance of the paper..."><?php echo e($_POST['instructions'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="generate_answer_key" id="generate_answer_key" value="1" <?php echo (isset($_POST['generate_answer_key']) && $_POST['generate_answer_key']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="generate_answer_key">
                                        <strong>Generate Answer Key</strong>
                                        <small class="d-block text-muted">Create a separate answer key page with correct answers for MCQs</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Teacher Notes / Marking Scheme</label>
                                <textarea class="form-control" name="teacher_notes" id="teacher_notes" rows="4" 
                                          placeholder="Enter marking guidelines, evaluation criteria, or notes for the examiner..."><?php echo e($_POST['teacher_notes'] ?? ''); ?></textarea>
                                <small class="text-muted">These notes will be included in the marking scheme section (not visible to students)</small>
                            </div>

                            <div class="d-flex gap-2 border-top pt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-cog"></i> Generate Paper Now
                                </button>
                                <a href="<?php echo base_url('teacher/papers/list.php'); ?>" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4 bg-light">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">How it works</h6>
                </div>
                <div class="card-body small">
                    <ol class="pl-3">
                        <li class="mb-2"><strong>Pick a Template:</strong> Select a pre-defined blueprint. Each blueprint has sections, difficulty levels, and mark distributions.</li>
                        <li class="mb-2"><strong>Custom Title:</strong> Give this specific paper a unique name (e.g., "Set A", "2024 Final").</li>
                        <li class="mb-2"><strong>Auto-Selection:</strong> Our algorithm will pick unique questions from the bank that match your blueprint's criteria.</li>
                        <li class="mb-2"><strong>Shuffle:</strong> Questions are randomized to ensure no two generated papers are identical (unless criteria are very narrow).</li>
                    </ol>
                    <hr>
                    <p class="mb-0 text-primary font-weight-bold">Need a new pattern?</p>
                    <a href="<?php echo base_url('teacher/blueprints/add.php'); ?>" class="btn btn-sm btn-outline-primary mt-2">Create New Blueprint</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const blueprintSelect = document.getElementById('blueprint_id');
    const titleInput = document.getElementById('paper_title');
    const instrInput = document.getElementById('instructions');

    blueprintSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            // Auto-fill title if empty
            if (!titleInput.value) {
                titleInput.value = selected.getAttribute('data-name');
            }
            // Auto-fill instructions if empty
            if (!instrInput.value) {
                instrInput.value = selected.getAttribute('data-instr');
            }
        }
    });
});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
