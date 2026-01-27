<?php
// c:\xampp\htdocs\project\config\constants.php
// Global Constants - Application Configuration

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'paper_generator_saas');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// PATH CONFIGURATION
// ============================================
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('QUESTIONS_UPLOAD_PATH', UPLOAD_PATH . '/questions');
define('SCHOOLS_UPLOAD_PATH', UPLOAD_PATH . '/schools');

// ============================================
// URL CONFIGURATION
// ============================================
define('BASE_URL', 'http://localhost/project');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('QUESTIONS_UPLOAD_URL', UPLOAD_URL . '/questions');
define('SCHOOLS_UPLOAD_URL', UPLOAD_URL . '/schools');

// ============================================
// USER ROLES
// ============================================
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_SCHOOL_ADMIN', 'school_admin');
define('ROLE_TEACHER', 'teacher');

// ============================================
// USER STATUS
// ============================================
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_SUSPENDED', 'suspended');

// ============================================
// QUESTION TYPES
// ============================================
define('QUESTION_TYPE_MCQ', 'mcq');
define('QUESTION_TYPE_SHORT', 'short_answer');
define('QUESTION_TYPE_LONG', 'long_answer');
define('QUESTION_TYPE_TRUE_FALSE', 'true_false');
define('QUESTION_TYPE_FILL_BLANK', 'fill_blank');
define('QUESTION_TYPE_NUMERICAL', 'numerical');

// ============================================
// DIFFICULTY LEVELS
// ============================================
define('DIFFICULTY_EASY', 'easy');
define('DIFFICULTY_MEDIUM', 'medium');
define('DIFFICULTY_HARD', 'hard');

// ============================================
// PAPER STATUS
// ============================================
define('PAPER_STATUS_DRAFT', 'draft');
define('PAPER_STATUS_FINALIZED', 'finalized');
define('PAPER_STATUS_PRINTED', 'printed');
define('PAPER_STATUS_ARCHIVED', 'archived');

// ============================================
// SUBSCRIPTION PLANS
// ============================================
define('PLAN_TRIAL', 'trial');
define('PLAN_BASIC', 'basic');
define('PLAN_PREMIUM', 'premium');
define('PLAN_ENTERPRISE', 'enterprise');

// ============================================
// FILE UPLOAD CONFIGURATION
// ============================================
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_IMAGE_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// ============================================
// SESSION CONFIGURATION
// ============================================
define('SESSION_NAME', 'QPG_SESSION');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('REMEMBER_ME_DURATION', 2592000); // 30 days in seconds

// ============================================
// PAGINATION CONFIGURATION
// ============================================
define('RECORDS_PER_PAGE', 25);
define('PAGINATION_LINKS', 5);

// ============================================
// SECURITY CONFIGURATION
// ============================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// ============================================
// APPLICATION CONFIGURATION
// ============================================
define('APP_NAME', 'Question Paper Generator');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'Asia/Kolkata');
define('DATE_FORMAT', 'd-m-Y');
define('DATETIME_FORMAT', 'd-m-Y H:i:s');
define('TIME_FORMAT', 'H:i:s');

// ============================================
// PAPER GENERATION CONFIGURATION
// ============================================
define('PAPER_CODE_PREFIX', 'QPG');
define('PAPER_RANDOMIZE_QUESTIONS', true);
define('PAPER_AVOID_DUPLICATES', true);
define('PAPER_MAX_USAGE_COUNT', 10); // Max times a question can be reused

// ============================================
// EMAIL CONFIGURATION (Future Use)
// ============================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@papergenerator.com');
define('SMTP_FROM_NAME', APP_NAME);

// ============================================
// LOGGING CONFIGURATION
// ============================================
define('ENABLE_ACTIVITY_LOG', true);
define('ENABLE_ERROR_LOG', true);
define('ERROR_LOG_PATH', ROOT_PATH . '/logs/error.log');

// ============================================
// MAINTENANCE MODE
// ============================================
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'System is under maintenance. Please try again later.');

// ============================================
// DEVELOPMENT MODE
// ============================================
define('DEBUG_MODE', true); // Set to false in production
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// TIMEZONE SETTING
// ============================================
date_default_timezone_set(APP_TIMEZONE);

// ============================================
// API CONFIGURATION (Future Use)
// ============================================
define('API_ENABLED', false);
define('API_VERSION', 'v1');
define('API_KEY_LENGTH', 32);

// ============================================
// NOTIFICATION MESSAGES
// ============================================
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// ============================================
// DEFAULT VALUES
// ============================================
define('DEFAULT_PROFILE_IMAGE', ASSETS_URL . '/images/default-avatar.png');
define('DEFAULT_SCHOOL_LOGO', ASSETS_URL . '/images/logo.png');
define('DEFAULT_LANGUAGE', 'en');

// ============================================
// QUESTION BANK LIMITS
// ============================================
define('MAX_QUESTIONS_PER_CHAPTER', 1000);
define('MAX_CHAPTERS_PER_SUBJECT', 50);
define('MAX_SUBJECTS_PER_STANDARD', 20);

// ============================================
// PAPER GENERATION LIMITS
// ============================================
define('MAX_QUESTIONS_PER_PAPER', 100);
define('MIN_QUESTIONS_PER_SECTION', 1);
define('MAX_SECTIONS_PER_PAPER', 10);

// ============================================
// CACHE CONFIGURATION (Future Enhancement)
// ============================================
define('CACHE_ENABLED', false);
define('CACHE_DURATION', 3600); // 1 hour

// ============================================
// SUCCESS MESSAGE
// ============================================
// Constants loaded successfully
