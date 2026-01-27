<?php
// c:\xampp\htdocs\project\teacher\blueprints\add.php
// Create New Paper Blueprint with Sections

$_page_title = "Create Blueprint";
require_once __DIR__ . "/../../includes/auth_check.php";
require_permission([ROLE_TEACHER, ROLE_SCHOOL_ADMIN], base_url("auth/login.php"));
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar.php";

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();
$errors = [];

$boards = $db->select("SELECT * FROM boards WHERE status = 'active' ORDER BY board_name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? "")) {
        $errors[] = "Invalid security token.";
    }

    $blueprint_name = sanitize_input($_POST["blueprint_name"] ?? "");
    $board_id = (int)($_POST["board_id"] ?? 0);
    $standard_id = (int)($_POST["standard_id"] ?? 0);
    $subject_id = (int)($_POST["subject_id"] ?? 0);
    $duration_minutes = (int)($_POST["duration_minutes"] ?? 180);
    $instructions = sanitize_input($_POST["instructions"] ?? "");
    
    $sections = $_POST["sections"] ?? [];

    if (empty($blueprint_name) || empty($board_id) || empty($standard_id) || empty($subject_id)) {
        $errors[] = "Please fill all required header fields.";
    }

    if (empty($sections)) {
        $errors[] = "Please add at least one section.";
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Insert Blueprint Header
            $blueprint_id = $db->insert("INSERT INTO paper_blueprints 
                (school_id, created_by, blueprint_name, board_id, standard_id, subject_id, total_marks, duration_minutes, instructions, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')", 
                [$school_id, $user_id, $blueprint_name, $board_id, $standard_id, $subject_id, 0, $duration_minutes, $instructions]);

            if ($blueprint_id) {
                $total_marks = 0;
                $order = 1;
                foreach ($sections as $s) {
                    $section_name = sanitize_input($s["name"] ?? "Section " . $order);
                    $q_type = sanitize_input($s["type"] ?? "mcq");
                    $difficulty = sanitize_input($s["difficulty"] ?? "medium");
                    $marks_per_q = (float)($s["marks"] ?? 1);
                    $num_q = (int)($s["num"] ?? 1);
                    $chapters = implode(",", array_filter(array_map('intval', $s["chapters"] ?? [])));

                    if (empty($chapters)) {
                        throw new Exception("Please select chapters for section: " . $section_name);
                    }

                    $db->insert("INSERT INTO blueprint_sections 
                        (blueprint_id, section_name, section_order, question_type, difficulty_level, marks_per_question, number_of_questions, chapter_ids) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$blueprint_id, $section_name, $order, $q_type, $difficulty, $marks_per_q, $num_q, $chapters]);
                    
                    $total_marks += ($marks_per_q * $num_q);
                    $order++;
                }

                // Update total marks in header
                $db->update("UPDATE paper_blueprints SET total_marks = ? WHERE blueprint_id = ?", [$total_marks, $blueprint_id]);

                $db->commit();
                log_activity($user_id, "create", "blueprint", $blueprint_id, "Created blueprint: $blueprint_name");
                set_flash_message(MSG_SUCCESS, "Blueprint created successfully!");
                redirect(base_url("teacher/blueprints/list.php"));
            } else {
                throw new Exception("Failed to create blueprint header.");
            }
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <h1><i class="fas fa-plus-circle"></i> Create Paper Blueprint</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/dashboard.php'); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo base_url('teacher/blueprints/list.php'); ?>">Blueprints</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
    </div>

    <form method="POST" action="" id="blueprintForm">
        <?php echo csrf_token_field(); ?>
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-info-circle"></i> Basic Details</div>
                    <div class="card-body">
                        <?php if ($errors): ?><div class="alert alert-danger"><?php echo implode("<br>", $errors); ?></div><?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Blueprint Name <span class="text-danger">*</span></label>
                            <input type="text" name="blueprint_name" class="form-control" value="<?php echo e($_POST['blueprint_name'] ?? ''); ?>" required placeholder="e.g. Annual Exam - Math">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Board <span class="text-danger">*</span></label>
                            <select name="board_id" id="board_id" class="form-select" required>
                                <option value="">Select Board</option>
                                <?php foreach ($boards as $b): ?>
                                    <option value="<?php echo $b['board_id']; ?>" <?php echo (isset($_POST['board_id']) && $_POST['board_id'] == $b['board_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($b['board_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Standard <span class="text-danger">*</span></label>
                            <select name="standard_id" id="standard_id" class="form-select" required disabled>
                                <option value="">Select Board First</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" id="subject_id" class="form-select" required disabled>
                                <option value="">Select Standard First</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Duration (Minutes)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="<?php echo e($_POST['duration_minutes'] ?? 180); ?>" min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">General Instructions</label>
                            <textarea name="instructions" class="form-control" rows="3"><?php echo e($_POST['instructions'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-layer-group"></i> Paper Sections</span>
                        <button type="button" class="btn btn-sm btn-success" id="addSection"><i class="fas fa-plus"></i> Add Section</button>
                    </div>
                    <div class="card-body">
                        <div id="sections-container">
                            <!-- Dynamic Sections -->
                        </div>
                        <div class="mt-3 text-end">
                            <h5>Total Marks: <span id="displayTotalMarks">0</span></h5>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Blueprint</button>
                    <a href="list.php" class="btn btn-secondary btn-lg">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Template for dynamic section -->
<template id="sectionTemplate">
    <div class="card bg-light mb-3 section-item">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h6 class="section-title">New Section</h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-section"><i class="fas fa-times"></i></button>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="small">Section Name</label>
                    <input type="text" name="sections[INDEX][name]" class="form-control form-control-sm" required placeholder="e.g. Objective Type">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Type</label>
                    <select name="sections[INDEX][type]" class="form-select form-select-sm">
                        <option value="mcq">MCQ</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="long_answer">Long Answer</option>
                        <option value="true_false">True/False</option>
                        <option value="fill_blank">Fill in Blank</option>
                        <option value="numerical">Numerical</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Level</label>
                    <select name="sections[INDEX][difficulty]" class="form-select form-select-sm">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Marks/Q</label>
                    <input type="number" name="sections[INDEX][marks]" class="form-control form-control-sm mark-input" step="0.5" min="0.5" value="1" required>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small">Num Qs</label>
                    <input type="number" name="sections[INDEX][num]" class="form-control form-control-sm count-input" min="1" value="5" required>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <label class="small">Chapters <span class="text-danger">*</span></label>
                    <div class="mb-2">
                        <button type="button" class="btn btn-xs btn-outline-primary select-all-chapters">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary deselect-all-chapters">Deselect All</button>
                    </div>
                    <div class="chapter-selection border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                        <p class="text-muted small mb-0">Select subject first to load chapters.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const boardSelect = document.getElementById('board_id');
    const standardSelect = document.getElementById('standard_id');
    const subjectSelect = document.getElementById('subject_id');
    const sectionsContainer = document.getElementById('sections-container');
    const addSectionBtn = document.getElementById('addSection');
    const template = document.getElementById('sectionTemplate');
    let sectionIndex = 0;
    let chaptersData = [];

    // Cascading Dropdowns
    boardSelect.onchange = function() {
        standardSelect.disabled = true;
        standardSelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`../../modules/ajax/get_standards.php?board_id=${this.value}`)
            .then(r => r.json())
            .then(d => {
                standardSelect.innerHTML = '<option value="">Select Standard</option>';
                d.data.forEach(s => standardSelect.innerHTML += `<option value="${s.standard_id}">${s.standard_name}</option>`);
                standardSelect.disabled = false;
            });
    };

    standardSelect.onchange = function() {
        subjectSelect.disabled = true;
        subjectSelect.innerHTML = '<option value="">Loading...</option>';
        fetch(`../../modules/ajax/get_subjects.php?standard_id=${this.value}`)
            .then(r => r.json())
            .then(d => {
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                d.data.forEach(s => subjectSelect.innerHTML += `<option value="${s.subject_id}">${s.subject_name}</option>`);
                subjectSelect.disabled = false;
            });
    };

    subjectSelect.onchange = function() {
        fetch(`../../modules/ajax/get_chapters.php?subject_id=${this.value}`)
            .then(r => r.json())
            .then(d => {
                chaptersData = d.data;
                // Update existing chapters containers if any
                document.querySelectorAll('.chapter-selection').forEach(container => {
                    renderChapters(container);
                });
            });
    };

    function renderChapters(container) {
        if (!chaptersData.length) {
            container.innerHTML = '<p class="text-muted small mb-0">No chapters found for this subject.</p>';
            return;
        }
        const index = container.closest('.section-item').querySelector('.remove-section').dataset.index;
        let html = '';
        chaptersData.forEach(c => {
            html += `<div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="sections[${index}][chapters][]" value="${c.chapter_id}" id="ch_${index}_${c.chapter_id}">
                <label class="form-check-label small" for="ch_${index}_${c.chapter_id}">${c.chapter_name}</label>
            </div>`;
        });
        container.innerHTML = html;
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.section-item').forEach(item => {
            const marks = parseFloat(item.querySelector('.mark-input').value) || 0;
            const count = parseInt(item.querySelector('.count-input').value) || 0;
            total += (marks * count);
        });
        document.getElementById('displayTotalMarks').innerText = total;
    }

    addSectionBtn.onclick = function() {
        sectionIndex++;
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.section-item');
        item.innerHTML = item.innerHTML.replace(/INDEX/g, sectionIndex);
        
        const removeBtn = item.querySelector('.remove-section');
        removeBtn.dataset.index = sectionIndex;
        removeBtn.onclick = function() {
            item.remove();
            calculateTotal();
        };

        item.querySelectorAll('input').forEach(input => {
            input.onchange = calculateTotal;
        });
        
        // Select All / Deselect All functionality
        const selectAllBtn = item.querySelector('.select-all-chapters');
        const deselectAllBtn = item.querySelector('.deselect-all-chapters');
        const chapterContainer = item.querySelector('.chapter-selection');
        
        selectAllBtn.onclick = function() {
            chapterContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
        };
        
        deselectAllBtn.onclick = function() {
            chapterContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        };

        renderChapters(chapterContainer);
        
        sectionsContainer.appendChild(clone);
        calculateTotal();
    };

    // Add first section by default
    addSectionBtn.click();
});
</script>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
