<?php
// c:\xampp\htdocs\project\school_admin\dashboard.php
// School Admin Dashboard - High-level overview for School Admins

$_page_title = "School Dashboard";
require_once __DIR__ . '/../includes/auth_check.php';
require_permission(ROLE_SCHOOL_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();
$school_id = get_school_id();

// Get School Info
$school = $db->selectOne("SELECT * FROM schools WHERE school_id = ?", [$school_id]);

// Get Stats
$total_teachers = $db->count('users', "school_id = ? AND role = 'teacher' AND status = 'active'", [$school_id]);
$total_questions = $db->count('questions', "school_id = ? AND status = 'active'", [$school_id]);
$total_papers = $db->count('generated_papers', "school_id = ?", [$school_id]);
$total_blueprints = $db->count('paper_blueprints', "school_id = ? AND status = 'active'", [$school_id]);

// Recent Activities in School
$recent_activities = $db->select(
    "SELECT al.*, u.full_name 
     FROM activity_logs al 
     INNER JOIN users u ON al.user_id = u.user_id 
     WHERE u.school_id = ? 
     ORDER BY al.created_at DESC 
     LIMIT 10",
    [$school_id]
);

// Recent Teachers added
$recent_teachers = $db->select(
    "SELECT * FROM users 
     WHERE school_id = ? AND role = 'teacher' 
     ORDER BY created_at DESC 
     LIMIT 5",
    [$school_id]
);

// Recent Papers generated in school
$recent_papers = $db->select(
    "SELECT gp.*, u.full_name AS generated_by_name, s.subject_name 
     FROM generated_papers gp 
     INNER JOIN users u ON gp.generated_by = u.user_id 
     INNER JOIN subjects s ON gp.subject_id = s.subject_id 
     WHERE gp.school_id = ? 
     ORDER BY gp.generated_at DESC 
     LIMIT 5",
    [$school_id]
);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-school"></i> <?php echo e($school['school_name']); ?></h1>
                <p class="lead text-muted">School Admin Dashboard - <?php echo e($school['school_code']); ?></p>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="background: #fff; border-left: 5px solid #4e73df; padding: 20px; border-radius: 10px; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Teachers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_teachers; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300" style="color: #dddfeb;"></i>
                    </div>
                </div>
                <a href="<?php echo base_url('admin/users/list.php?role=teacher'); ?>" class="btn btn-link btn-sm p-0 mt-2">Manage Teachers</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="background: #fff; border-left: 5px solid #1cc88a; padding: 20px; border-radius: 10px; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Question Bank</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_questions; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-question-circle fa-2x text-gray-300" style="color: #dddfeb;"></i>
                    </div>
                </div>
                <a href="<?php echo base_url('teacher/questions/list.php'); ?>" class="btn btn-link btn-sm p-0 mt-2">View Questions</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="background: #fff; border-left: 5px solid #36b9cc; padding: 20px; border-radius: 10px; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Papers Generated</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_papers; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300" style="color: #dddfeb;"></i>
                    </div>
                </div>
                <a href="<?php echo base_url('teacher/papers/list.php'); ?>" class="btn btn-link btn-sm p-0 mt-2">View Papers</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="background: #fff; border-left: 5px solid #f6c23e; padding: 20px; border-radius: 10px; box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Blueprints</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_blueprints; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-copy fa-2x text-gray-300" style="color: #dddfeb;"></i>
                    </div>
                </div>
                <a href="<?php echo base_url('teacher/blueprints/list.php'); ?>" class="btn btn-link btn-sm p-0 mt-2">Manage Templates</a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> School Activities</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="mb-3 pb-2 border-bottom">
                                <div class="small text-muted"><?php echo format_datetime($activity['created_at']); ?></div>
                                <span class="font-weight-bold"><?php echo e($activity['full_name']); ?></span> 
                                <?php echo e($activity['action']); ?> <?php echo e($activity['entity_type']); ?>

                                <p class="mb-0 text-muted small"><?php echo e($activity['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">No activities recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Papers -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-invoice"></i> Recently Generated Papers</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_papers)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Subject</th>
                                        <th>By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_papers as $paper): ?>
                                        <tr>
                                            <td><a href="<?php echo base_url('teacher/papers/preview.php?id='.$paper['paper_id']); ?>"><?php echo e($paper['paper_code']); ?></a></td>
                                            <td><?php echo e($paper['subject_name']); ?></td>
                                            <td><?php echo e($paper['generated_by_name']); ?></td>
                                            <td><?php echo date('d M', strtotime($paper['generated_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No papers generated yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users"></i> Recent Teachers</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_teachers)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_teachers as $teacher): ?>
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="font-weight-bold"><?php echo e($teacher['full_name']); ?></div>
                                        <div class="small text-muted"><?php echo e($teacher['username']); ?></div>
                                    </div>
                                    <span class="badge bg-<?php echo $teacher['status'] == 'active' ? 'success' : 'danger'; ?> rounded-pill"><?php echo $teacher['status']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No teachers added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <a href="<?php echo base_url('admin/users/add.php?role=teacher'); ?>" class="btn btn-outline-primary btn-block mb-2">
                                <i class="fas fa-user-plus d-block fa-2x mb-2"></i> Add Teacher
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo base_url('teacher/questions/add.php'); ?>" class="btn btn-outline-success btn-block mb-2">
                                <i class="fas fa-plus-circle d-block fa-2x mb-2"></i> Add Question
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo base_url('teacher/blueprints/add.php'); ?>" class="btn btn-outline-info btn-block mb-2">
                                <i class="fas fa-copy d-block fa-2x mb-2"></i> Create Blueprint
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?php echo base_url('teacher/papers/create.php'); ?>" class="btn btn-outline-warning btn-block mb-2">
                                <i class="fas fa-magic d-block fa-2x mb-2"></i> Generate Paper
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
