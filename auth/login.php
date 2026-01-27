<?php
// c:\xampp\htdocs\project\auth\login.php
// User Login Page and Handler

require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    $role = get_user_role();
    if ($role === ROLE_SUPER_ADMIN) {
        redirect(base_url('admin/dashboard.php'));
    } elseif ($role === ROLE_SCHOOL_ADMIN || $role === ROLE_TEACHER) {
        redirect(base_url('teacher/dashboard.php'));
    }
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate CSRF token
    if (!validate_csrf_token($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $db = getDB();
        
        // Get user from database
        $query = "SELECT * FROM users WHERE username = ? AND status = ? LIMIT 1";
        $user = $db->selectOne($query, [$username, STATUS_ACTIVE]);
        
        if ($user && verify_password($password, $user['password_hash'])) {
            // Password is correct - create session
            set_session('user_id', $user['user_id']);
            set_session('username', $user['username']);
            set_session('full_name', $user['full_name']);
            set_session('email', $user['email']);
            set_session('user_role', $user['role']);
            set_session('school_id', $user['school_id']);
            set_session('profile_image', $user['profile_image']);
            
            // Update last login time
            $update_query = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
            $db->update($update_query, [$user['user_id']]);
            
            // Log activity
            log_activity($user['user_id'], 'login', 'user', $user['user_id'], 'User logged in successfully');
            
            // Set flash message
            set_flash_message(MSG_SUCCESS, 'Welcome back, ' . $user['full_name'] . '!');
            
            // Redirect based on role
            if ($user['role'] === ROLE_SUPER_ADMIN) {
                redirect(base_url('admin/dashboard.php'));
            } elseif ($user['role'] === ROLE_SCHOOL_ADMIN) {
                redirect(base_url('school_admin/dashboard.php'));
            } else {
                redirect(base_url('teacher/dashboard.php'));
            }
        } else {
            $error = 'Invalid username or password.';
            
            // Log failed login attempt
            if ($user) {
                log_activity($user['user_id'], 'failed_login', 'user', $user['user_id'], 'Failed login attempt');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo assets_url('css/style.css'); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 700;
        }
        .login-header p {
            color: #666;
            margin-top: 10px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><?php echo APP_NAME; ?></h2>
                <p>Sign in to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php
            // Display flash message
            $flash = get_flash_message();
            if ($flash):
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrf_token_field(); ?>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo isset($_POST['username']) ? e($_POST['username']) : ''; ?>" 
                           required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">Login</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></small>
                </p>
            </div>
        </div>

        <!-- Test Credentials Card -->
        <div class="card mt-3" style="background: rgba(255,255,255,0.9);">
            <div class="card-body">
                <h6 class="card-title text-center mb-3">Test Credentials</h6>
                <div class="small">
                    <strong>Super Admin:</strong> admin / admin123<br>
                    <strong>School Admin:</strong> school_admin / admin123<br>
                    <strong>Teacher:</strong> teacher / teacher123
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
