<?php
// c:\xampp\htdocs\project\includes\navbar.php
// Navigation Bar

$current_user = get_user_by_id(get_user_id());
$user_role = get_user_role();
$is_admin = is_super_admin();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo base_url(); ?>">
            <i class="fas fa-file-alt"></i> <?php echo APP_NAME; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($user_role === ROLE_SUPER_ADMIN): ?>
                    <!-- Super Admin Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('admin/dashboard.php'); ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-school"></i> Schools
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/schools/list.php'); ?>">View All</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/schools/add.php'); ?>">Add New</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/users/list.php'); ?>">View All</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/users/add.php'); ?>">Add New</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> Masters
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/boards/list.php'); ?>">Boards</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/standards/list.php'); ?>">Standards</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/subjects/list.php'); ?>">Subjects</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('admin/chapters/list.php'); ?>">Chapters</a></li>
                        </ul>
                    </li>
                <?php elseif ($user_role === ROLE_SCHOOL_ADMIN): ?>
                    <!-- School Admin Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('school_admin/dashboard.php'); ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-question-circle"></i> Questions
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/questions/list.php'); ?>">View All</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/questions/add.php'); ?>">Add New</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-file-alt"></i> Papers
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/papers/create.php'); ?>">Create New</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/papers/list.php'); ?>">View All</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('teacher/blueprints/list.php'); ?>">
                            <i class="fas fa-copy"></i> Blueprints
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('school_admin/settings.php'); ?>">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                <?php elseif ($user_role === ROLE_TEACHER): ?>
                    <!-- Teacher Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('teacher/dashboard.php'); ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-question-circle"></i> Questions
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/questions/list.php'); ?>">View All</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/questions/add.php'); ?>">Add New</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-file-alt"></i> Papers
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/papers/create.php'); ?>">Create New</a></li>
                            <li><a class="dropdown-item" href="<?php echo base_url('teacher/papers/list.php'); ?>">View All</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('teacher/blueprints/list.php'); ?>">
                            <i class="fas fa-copy"></i> Blueprints
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo e($current_user['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text"><strong><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></strong></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-key"></i> Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo base_url('auth/logout.php'); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php
// Display flash messages
$flash = get_flash_message();
if ($flash):
?>
<div class="container mt-3">
    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
