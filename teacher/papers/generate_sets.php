<?php
// c:\xampp\htdocs\project\teacher\papers\generate_sets.php
// Generate Multiple Paper Sets (Set A, Set B, etc.) from same Blueprint

$_page_title = "Generate Multiple Sets";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();
$errors = [];
$success_messages = [];

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) {
        $errors[] = "Invalid security token.";
    }
    
    $blueprint_id = (int)($_POST["blueprint_id"] ?? 0);
    $paper_title = sanitize_input($_POST["paper_title"] ?? "");
    $set_names_text = $_POST["set_names_text"] ?? "";
    $generate_answer_key = isset($_POST["generate_answer_key"]) ? 1 : 0;
    
    if (empty($blueprint_id)) {
        $errors[] = "Please select a blueprint.";
    }
    if (empty($paper_title)) {
        $errors[] = "Paper title is required.";
    }
    if (empty(trim($set_names_text))) {
        $errors[] = "Please enter at least one set name (e.g., Set A).";
    }
    
    if (empty($errors)) {
        require_once MODULES_PATH . "/paper_generator/generate.php";
        $generator = new PaperGenerator($school_id, $user_id);
        
        // Parse set names from textarea (handle both Unix \n and Windows \r\n line endings)
        $raw_text = $set_names_text;
        $raw_text = str_replace("\r\n", "\n", $raw_text); // Normalize Windows line endings
        $raw_text = str_replace("\r", "\n", $raw_text); // Normalize old Mac line endings
        
        $set_names = array_filter(
            array_map('trim', explode("\n", $raw_text)),
            function($v) { return !empty($v); }
        );
        
        if (empty($set_names)) {
            $errors[] = "Please enter at least one set name.";
        } else {
            foreach ($set_names as $set_name) {
                $options = [
                    'paper_title' => $paper_title . " - " . $set_name,
                    'instructions' => '',
                    'generate_answer_key' => $generate_answer_key
                ];
                
                $result = $generator->generateFromBlueprint($blueprint_id, $options);
                
                if ($result["success"]) {
                    $success_messages[] = "Generated {$set_name} successfully! (Code: " . $result["paper_code"] . ")";
                } else {
                    $errors[] = "Failed to generate {$set_name}: " . $result["message"];
                }
            }
        }
        
        if (empty($errors) && !empty($success_messages)) {
            set_flash_message(MSG_SUCCESS, "Successfully generated " . count($success_messages) . " paper(s)!");
            redirect(base_url("teacher/papers/list.php"));
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-layer-group"></i> Generate Multiple Paper Sets</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/papers/list.php'); ?>">Papers</a></li>
                        <li class="breadcrumb-item active">Generate Sets</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Generate Paper Sets</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($blueprints)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No blueprints found. 
                            You need to <a href="<?php echo base_url('teacher/blueprints/add.php'); ?>">create a blueprint</a> first.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <?php echo csrf_token_field(); ?>
                            
                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Select Blueprint <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" name="blueprint_id" id="blueprint_id" required>
                                    <option value="">-- Choose a Blueprint --</option>
                                    <?php foreach ($blueprints as $bp): ?>
                                        <option value="<?php echo $bp['blueprint_id']; ?>" <?php echo (isset($_POST['blueprint_id']) && $_POST['blueprint_id'] == $bp['blueprint_id']) ? 'selected' : ''; ?>>
                                            <?php echo e($bp['blueprint_name']); ?> (<?php echo e($bp['standard_name']); ?> - <?php echo e($bp['subject_name']); ?>, <?php echo $bp['total_marks']; ?> Marks)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">All sets will use the same blueprint structure with different questions.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Base Paper Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="paper_title" id="paper_title" 
                                       value="<?php echo e($_POST['paper_title'] ?? ''); ?>" 
                                       placeholder="e.g. Mid Term Exam - 2024" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Set Names (one per line) <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="set_names_text" id="set_names_text" rows="4" 
                                          placeholder="Set A
Set B
Set C"><?php echo e($_POST['set_names_text'] ?? "Set A\nSet B"); ?></textarea>
                                <small class="text-muted">Enter each set name on a new line (e.g., Set A, Set B, Set C)</small>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="generate_answer_key" id="generate_answer_key" value="1" <?php echo (isset($_POST['generate_answer_key']) && $_POST['generate_answer_key']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="generate_answer_key">
                                        <strong>Generate Answer Keys</strong>
                                        <small class="d-block text-muted">Create separate answer keys for all sets</small>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex gap-2 border-top pt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-layer-group"></i> Generate All Sets
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
                    <h6 class="m-0 font-weight-bold text-primary">How Multi-Set Generation Works</h6>
                </div>
                <div class="card-body small">
                    <ol class="pl-3">
                        <li class="mb-2"><strong>Select a Blueprint:</strong> Choose your pre-defined paper template.</li>
                        <li class="mb-2"><strong>Base Title:</strong> Enter the common paper title.</li>
                        <li class="mb-2"><strong>Define Sets:</strong> List set names (Set A, Set B, etc.) - one per line.</li>
                        <li class="mb-2"><strong>Generate:</strong> The system will randomly select different questions for each set from your question bank.</li>
                        <li class="mb-2"><strong>Unique Papers:</strong> Each generated set will have a unique paper code and completely different questions.</li>
                    </ol>
                    <hr>
                    <p class="mb-0 text-primary font-weight-bold">Use Cases:</p>
                    <ul>
                        <li>Multiple exam rooms with same difficulty</li>
                        <li>Main exam + backup papers</li>
                        <li>Different versions for different batches</li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Important Notes</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>Each set requires sufficient questions in your bank</li>
                        <li>Questions are selected randomly - no two sets will be identical</li>
                        <li>Generated sets are independent - can be viewed/printed separately</li>
                        <li>Answer keys are generated separately for each set</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
