<?php
// c:\xampp\htdocs\project\school_admin\settings.php
// School Branding & Settings Management

$_page_title = "School Branding Settings";
require_once __DIR__ . '/../../includes/auth_check.php';
require_permission([ROLE_SCHOOL_ADMIN], base_url('auth/login.php'));
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$db = getDB();
$user_id = get_user_id();
$school_id = get_school_id();
$errors = [];
$success_message = '';

// Get current school data
$school = $db->selectOne("SELECT * FROM schools WHERE school_id = ?", [$school_id]);

if (!$school) {
    set_flash_message(MSG_ERROR, "School not found.");
    redirect(base_url('school_admin/dashboard.php'));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = "Invalid security token.";
    }
    
    $paper_header_text = sanitize_input($_POST['paper_header_text'] ?? '');
    $paper_footer_text = sanitize_input($_POST['paper_footer_text'] ?? '');
    $watermark_enabled = isset($_POST['watermark_enabled']) ? 1 : 0;
    $watermark_text = sanitize_input($_POST['watermark_text'] ?? 'CONFIDENTIAL');
    
    $logo_filename = $school['logo'] ?? '';
    
    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = "File size must not exceed 2MB.";
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'school_' . $school_id . '_' . time() . '.' . $extension;
            $upload_path = SCHOOLS_UPLOAD_PATH . '/' . $new_filename;
            
            // Create directory if needed
            if (!is_dir(SCHOOLS_UPLOAD_PATH)) {
                mkdir(SCHOOLS_UPLOAD_PATH, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old logo if exists
                if (!empty($school['logo']) && file_exists(SCHOOLS_UPLOAD_PATH . '/' . $school['logo'])) {
                    unlink(SCHOOLS_UPLOAD_PATH . '/' . $school['logo']);
                }
                $logo_filename = $new_filename;
            } else {
                $errors[] = "Failed to upload logo. Please try again.";
            }
        }
    }
    
    // Handle logo deletion
    if (isset($_POST['delete_logo']) && !empty($school['logo'])) {
        if (file_exists(SCHOOLS_UPLOAD_PATH . '/' . $school['logo'])) {
            unlink(SCHOOLS_UPLOAD_PATH . '/' . $school['logo']);
        }
        $logo_filename = '';
    }
    
    if (empty($errors)) {
        $update_data = [
            'paper_header_text' => $paper_header_text,
            'paper_footer_text' => $paper_footer_text,
            'watermark_enabled' => $watermark_enabled,
            'watermark_text' => $watermark_text
        ];
        
        if ($logo_filename !== '') {
            $update_data['logo'] = $logo_filename;
        }
        
        $updated = $db->update("UPDATE schools SET 
            paper_header_text = ?, 
            paper_footer_text = ?, 
            watermark_enabled = ?, 
            watermark_text = ?" . 
            (isset($update_data['logo']) ? ", logo = ?" : "") . " 
            WHERE school_id = ?", 
            array_merge(array_values($update_data), [$school_id]));
        
        if ($updated !== false) {
            log_activity($user_id, 'update', 'school_settings', $school_id, 'Updated school branding settings');
            set_flash_message(MSG_SUCCESS, "Branding settings updated successfully!");
            redirect(base_url('school_admin/settings.php'));
        } else {
            $errors[] = "Failed to save settings. Please try again.";
        }
    }
}

// Get logo URL if exists
$logo_url = !empty($school['logo']) ? SCHOOLS_UPLOAD_URL . '/' . $school['logo'] : '';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-palette"></i> School Branding Settings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url('school_admin/dashboard.php'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Branding Settings</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $e) echo "<li>" . e($e) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Logo & Branding</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrf_token_field(); ?>
                        
                        <!-- Logo Upload -->
                        <div class="mb-4">
                            <label class="form-label font-weight-bold">School Logo</label>
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <?php if (!empty($logo_url)): ?>
                                        <div class="logo-preview mb-2" style="width: 150px; height: 150px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                            <img src="<?php echo $logo_url; ?>" alt="School Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_logo" id="delete_logo" value="1">
                                            <label class="form-check-label text-danger" for="delete_logo">Delete current logo</label>
                                        </div>
                                    <?php else: ?>
                                        <div class="logo-placeholder mb-2" style="width: 150px; height: 150px; border: 2px dashed #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; color: #999;">
                                            <div class="text-center">
                                                <i class="fas fa-image fa-3x mb-2"></i>
                                                <p class="mb-0">No Logo</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small class="text-muted">Recommended: 200x200px, Max 2MB. Formats: JPG, PNG, GIF, WebP</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Paper Customization -->
                        <h5 class="mb-3"><i class="fas fa-file-alt"></i> Paper Customization</h5>

                        <div class="mb-4">
                            <label class="form-label font-weight-bold">Custom Header Text</label>
                            <input type="text" class="form-control" name="paper_header_text" id="paper_header_text" 
                                   value="<?php echo e($school['paper_header_text'] ?? ''); ?>" 
                                   placeholder="e.g., Annual Examination - 2024">
                            <small class="text-muted">This text appears below the school name on all generated papers</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-weight-bold">Custom Footer Text</label>
                            <input type="text" class="form-control" name="paper_footer_text" id="paper_footer_text" 
                                   value="<?php echo e($school['paper_footer_text'] ?? ''); ?>" 
                                   placeholder="e.g., Page | of">
                            <small class="text-muted">This text appears at the bottom of each page</small>
                        </div>

                        <hr>

                        <!-- Watermark -->
                        <h5 class="mb-3"><i class="fas fa-tint"></i> Watermark Settings</h5>

                        <div class="mb-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="watermark_enabled" id="watermark_enabled" 
                                       value="1" <?php echo (!empty($school['watermark_enabled'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="watermark_enabled">
                                    <strong>Enable Watermark</strong>
                                    <small class="d-block text-muted">Show a diagonal watermark on printed papers</small>
                                </label>
                            </div>

                            <div id="watermark_text_container" class="<?php echo empty($school['watermark_enabled']) ? 'd-none' : ''; ?>">
                                <label class="form-label">Watermark Text</label>
                                <input type="text" class="form-control" name="watermark_text" id="watermark_text" 
                                       value="<?php echo e($school['watermark_text'] ?? 'CONFIDENTIAL'); ?>" 
                                       placeholder="e.g., CONFIDENTIAL, DRAFT, SAMPLE">
                                <small class="text-muted">The text that appears diagonally across each page</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 border-top pt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="<?php echo base_url('school_admin/dashboard.php'); ?>" class="btn btn-secondary btn-lg">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Preview</h6>
                </div>
                <div class="card-body">
                    <div class="paper-preview p-3 border rounded" style="background: #fff; min-height: 400px; position: relative;">
                        <div class="text-center border-bottom pb-2 mb-2">
                            <?php if (!empty($logo_url)): ?>
                                <img src="<?php echo $logo_url; ?>" alt="Logo" style="max-height: 60px; max-width: 100px; margin-bottom: 10px;">
                                <br>
                            <?php endif; ?>
                            <h6 class="mb-1"><?php echo e($school['school_name']); ?></h6>
                            <?php if (!empty($school['paper_header_text'])): ?>
                                <small class="text-muted"><?php echo e($school['paper_header_text']); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="py-4 text-center text-muted">
                            <small>Paper content preview...</small>
                        </div>
                        <?php if (!empty($school['watermark_enabled'])): ?>
                            <div class="watermark-preview" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 24px; color: rgba(0,0,0,0.08); white-space: nowrap; pointer-events: none;">
                                <?php echo e($school['watermark_text'] ?? 'CONFIDENTIAL'); ?>
                            </div>
                        <?php endif; ?>
                        <div class="border-top pt-2 mt-2 text-center">
                            <small class="text-muted"><?php echo e($school['paper_footer_text'] ?? ''); ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Tips</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>Use a transparent PNG logo for best results</li>
                        <li>The watermark appears diagonally across the entire page</li>
                        <li>Custom header text is useful for exam names/dates</li>
                        <li>Footer text can include page numbers or disclaimers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const watermarkToggle = document.getElementById('watermark_enabled');
    const watermarkTextContainer = document.getElementById('watermark_text_container');
    
    watermarkToggle.addEventListener('change', function() {
        if (this.checked) {
            watermarkTextContainer.classList.remove('d-none');
        } else {
            watermarkTextContainer.classList.add('d-none');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
