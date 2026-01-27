<?php
// c:\xampp\htdocs\project\admin\dashboard.php
// Super Admin Dashboard

$_page_title = "Admin Dashboard";
require_once __DIR__ . '/../includes/auth_check.php';
require_permission(ROLE_SUPER_ADMIN, base_url('auth/login.php'));
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$db = getDB();

// Get statistics
$total_schools = $db->count('schools', "status = 'active'");
$total_users = $db->count('users', "status = 'active'");
$total_teachers = $db->count('users', "role = 'teacher' AND status = 'active'");
$total_questions = $db->count('questions', "status = 'active'");
$total_papers = $db->count('generated_papers');
$total_boards = $db->count('boards', "status = 'active'");
$total_subjects = $db->count('subjects', "status = 'active'");

// Recent activities
$recent_activities = $db->select(
    "SELECT al.*, u.full_name 
     FROM activity_logs al 
     INNER JOIN users u ON al.user_id = u.user_id 
     ORDER BY al.created_at DESC 
     LIMIT 10"
);

// Recent schools
$recent_schools = $db->select(
    "SELECT * FROM schools 
     ORDER BY created_at DESC 
     LIMIT 5"
);
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo base_url(); ?>">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-school icon"></i>
                <div class="number"><?php echo $total_schools; ?></div>
                <div class="label">Active Schools</div>
                <a href="<?php echo base_url('admin/schools/list.php'); ?>" class="btn btn-sm btn-primary mt-2">View All</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-users icon"></i>
                <div class="number"><?php echo $total_users; ?></div>
                <div class="label">Total Users</div>
                <a href="<?php echo base_url('admin/users/list.php'); ?>" class="btn btn-sm btn-primary mt-2">Manage Users</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-chalkboard-teacher icon"></i>
                <div class="number"><?php echo $total_teachers; ?></div>
                <div class="label">Teachers</div>
                <a href="<?php echo base_url('admin/users/list.php?role=teacher'); ?>" class="btn btn-sm btn-primary mt-2">View Teachers</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-question-circle icon"></i>
                <div class="number"><?php echo $total_questions; ?></div>
                <div class="label">Questions in Bank</div>
                <span class="badge bg-success mt-2">Active</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-file-alt icon"></i>
                <div class="number"><?php echo $total_papers; ?></div>
                <div class="label">Papers Generated</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-clipboard-list icon"></i>
                <div class="number"><?php echo $total_boards; ?></div>
                <div class="label">Boards</div>
                <a href="<?php echo base_url('admin/boards/list.php'); ?>" class="btn btn-sm btn-primary mt-2">Manage</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-book icon"></i>
                <div class="number"><?php echo $total_subjects; ?></div>
                <div class="label">Subjects</div>
                <a href="<?php echo base_url('admin/subjects/list.php'); ?>" class="btn btn-sm btn-primary mt-2">Manage</a>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <div class="dashboard-card" style="position: relative;">
                <i class="fas fa-chart-line icon"></i>
                <div class="number"><?php echo date('Y'); ?></div>
                <div class="label">Current Year</div>
                <span class="badge bg-info mt-2">v<?php echo APP_VERSION; ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Schools & Activities -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-school"></i> Recent Schools
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_schools)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>School Name</th>
                                        <th>City</th>
                                        <th>Plan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_schools as $school): ?>
                                        <tr>
                                            <td><?php echo e($school['school_name']); ?></td>
                                            <td><?php echo e($school['city']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo ucfirst($school['subscription_plan']); ?></span></td>
                                            <td><span class="badge bg-success"><?php echo ucfirst($school['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No schools registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Recent Activities
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($recent_activities)): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($recent_activities as $activity): ?>
                                <li class="mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-circle text-primary" style="font-size: 8px; margin-top: 6px; margin-right: 10px;"></i>
                                        <div>
                                            <strong><?php echo e($activity['full_name']); ?></strong>
                                            <?php echo e($activity['action']); ?>
                                            <span class="text-muted"><?php echo e($activity['entity_type']); ?></span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i>
                                                <?php echo format_datetime($activity['created_at']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No recent activities.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('admin/schools/add.php'); ?>" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add New School
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('admin/users/add.php'); ?>" class="btn btn-success w-100">
                                <i class="fas fa-user-plus"></i> Add New User
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?php echo base_url('admin/boards/add.php'); ?>" class="btn btn-info w-100">
                                <i class="fas fa-clipboard-list"></i> Add Board
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="#" class="btn btn-warning w-100">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
