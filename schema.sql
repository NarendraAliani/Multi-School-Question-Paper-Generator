-- c:\xampp\htdocs\project\schema.sql
-- Multi-School Question Paper Generator SaaS - Complete Database Schema
-- Database: paper_generator_saas
-- Normalization: 3NF (Third Normal Form)
-- Engine: InnoDB (Supports Foreign Keys and Transactions)

-- ============================================
-- DROP DATABASE IF EXISTS (USE WITH CAUTION)
-- ============================================
-- DROP DATABASE IF EXISTS paper_generator_saas;

-- ============================================
-- CREATE DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS paper_generator_saas 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE paper_generator_saas;

-- ============================================
-- TABLE: schools
-- Purpose: Store school information
-- ============================================
CREATE TABLE schools (
    school_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(255) NOT NULL,
    school_code VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    logo VARCHAR(255) DEFAULT NULL,
    subscription_plan ENUM('trial', 'basic', 'premium', 'enterprise') DEFAULT 'trial',
    subscription_start DATE,
    subscription_end DATE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school_code (school_code),
    INDEX idx_status (status),
    INDEX idx_subscription_end (subscription_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: users
-- Purpose: Store user accounts (Super Admin, School Admin, Teachers)
-- ============================================
CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED DEFAULT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'school_admin', 'teacher') NOT NULL,
    phone VARCHAR(20),
    profile_image VARCHAR(255),
    last_login TIMESTAMP NULL DEFAULT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_school_role (school_id, role),
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: boards
-- Purpose: Store educational boards (CBSE, ICSE, State Boards)
-- ============================================
CREATE TABLE boards (
    board_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_name VARCHAR(100) UNIQUE NOT NULL,
    board_code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_board_code (board_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: standards
-- Purpose: Store class/standard information (1st, 2nd, ..., 12th)
-- ============================================
CREATE TABLE standards (
    standard_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_id INT UNSIGNED NOT NULL,
    standard_name VARCHAR(50) NOT NULL,
    standard_code VARCHAR(20) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_board_standard (board_id, standard_code),
    INDEX idx_board_id (board_id),
    INDEX idx_standard_code (standard_code),
    INDEX idx_display_order (display_order),
    FOREIGN KEY (board_id) REFERENCES boards(board_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: subjects
-- Purpose: Store subject information
-- ============================================
CREATE TABLE subjects (
    subject_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    standard_id INT UNSIGNED NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_standard_subject (standard_id, subject_code),
    INDEX idx_standard_id (standard_id),
    INDEX idx_subject_code (subject_code),
    INDEX idx_display_order (display_order),
    FOREIGN KEY (standard_id) REFERENCES standards(standard_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: chapters
-- Purpose: Store chapter/unit information
-- ============================================
CREATE TABLE chapters (
    chapter_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id INT UNSIGNED NOT NULL,
    chapter_name VARCHAR(255) NOT NULL,
    chapter_number VARCHAR(20),
    description TEXT,
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subject_id (subject_id),
    INDEX idx_chapter_number (chapter_number),
    INDEX idx_display_order (display_order),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: questions
-- Purpose: Store question bank with image support
-- ============================================
CREATE TABLE questions (
    question_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    chapter_id INT UNSIGNED NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_image VARCHAR(255) DEFAULT NULL,
    question_type ENUM('mcq', 'short_answer', 'long_answer', 'true_false', 'fill_blank', 'numerical') NOT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard') NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    time_minutes INT DEFAULT 0,
    option_a TEXT,
    option_b TEXT,
    option_c TEXT,
    option_d TEXT,
    correct_answer TEXT,
    explanation TEXT,
    tags VARCHAR(255),
    usage_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school_chapter (school_id, chapter_id),
    INDEX idx_difficulty_marks (difficulty_level, marks),
    INDEX idx_question_type (question_type),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    FULLTEXT idx_question_text (question_text, tags),
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(chapter_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: paper_blueprints
-- Purpose: Store paper templates/blueprints
-- ============================================
CREATE TABLE paper_blueprints (
    blueprint_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    blueprint_name VARCHAR(255) NOT NULL,
    board_id INT UNSIGNED NOT NULL,
    standard_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    total_marks INT NOT NULL,
    duration_minutes INT NOT NULL,
    instructions TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school_subject (school_id, subject_id),
    INDEX idx_created_by (created_by),
    INDEX idx_board_standard (board_id, standard_id),
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (board_id) REFERENCES boards(board_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (standard_id) REFERENCES standards(standard_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: blueprint_sections
-- Purpose: Define sections in paper blueprint (e.g., "10 Easy MCQs from Ch1-3")
-- ============================================
CREATE TABLE blueprint_sections (
    section_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    blueprint_id INT UNSIGNED NOT NULL,
    section_name VARCHAR(100) NOT NULL,
    section_order INT NOT NULL,
    question_type ENUM('mcq', 'short_answer', 'long_answer', 'true_false', 'fill_blank', 'numerical') NOT NULL,
    difficulty_level ENUM('easy', 'medium', 'hard') NOT NULL,
    marks_per_question DECIMAL(5,2) NOT NULL,
    number_of_questions INT NOT NULL,
    chapter_ids VARCHAR(255) NOT NULL COMMENT 'Comma-separated chapter IDs',
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_blueprint_id (blueprint_id),
    INDEX idx_section_order (section_order),
    FOREIGN KEY (blueprint_id) REFERENCES paper_blueprints(blueprint_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: generated_papers
-- Purpose: Store generated question papers
-- ============================================
CREATE TABLE generated_papers (
    paper_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id INT UNSIGNED NOT NULL,
    blueprint_id INT UNSIGNED NOT NULL,
    generated_by INT UNSIGNED NOT NULL,
    paper_title VARCHAR(255) NOT NULL,
    paper_code VARCHAR(50) UNIQUE NOT NULL,
    board_id INT UNSIGNED NOT NULL,
    standard_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    total_marks INT NOT NULL,
    duration_minutes INT NOT NULL,
    instructions TEXT,
    header_text TEXT,
    footer_text TEXT,
    status ENUM('draft', 'finalized', 'printed', 'archived') DEFAULT 'draft',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school_subject (school_id, subject_id),
    INDEX idx_paper_code (paper_code),
    INDEX idx_generated_by (generated_by),
    INDEX idx_blueprint_id (blueprint_id),
    INDEX idx_status (status),
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (blueprint_id) REFERENCES paper_blueprints(blueprint_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (board_id) REFERENCES boards(board_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (standard_id) REFERENCES standards(standard_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: generated_paper_questions
-- Purpose: Store questions included in generated papers (Junction Table)
-- ============================================
CREATE TABLE generated_paper_questions (
    paper_question_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paper_id INT UNSIGNED NOT NULL,
    question_id INT UNSIGNED NOT NULL,
    section_name VARCHAR(100),
    question_order INT NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_paper_id (paper_id),
    INDEX idx_question_id (question_id),
    INDEX idx_question_order (question_order),
    UNIQUE KEY unique_paper_question (paper_id, question_id),
    FOREIGN KEY (paper_id) REFERENCES generated_papers(paper_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: activity_logs
-- Purpose: Track user activities for audit trail
-- ============================================
CREATE TABLE activity_logs (
    log_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: settings
-- Purpose: Store application-wide settings
-- ============================================
CREATE TABLE settings (
    setting_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT SETTINGS
-- ============================================
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('app_name', 'Question Paper Generator', 'string', 'Application Name'),
('app_version', '1.0.0', 'string', 'Application Version'),
('max_upload_size', '5242880', 'integer', 'Maximum file upload size in bytes (5MB)'),
('allowed_image_types', 'jpg,jpeg,png,gif', 'string', 'Allowed image file extensions'),
('paper_code_prefix', 'QPG', 'string', 'Prefix for generated paper codes'),
('session_timeout', '3600', 'integer', 'Session timeout in seconds (1 hour)'),
('maintenance_mode', '0', 'boolean', 'Maintenance mode (0=off, 1=on)');

-- ============================================
-- VIEWS FOR REPORTING
-- ============================================

-- View: Complete Question Details with Hierarchy
CREATE VIEW vw_questions_complete AS
SELECT 
    q.question_id,
    q.question_text,
    q.question_image,
    q.question_type,
    q.difficulty_level,
    q.marks,
    q.time_minutes,
    q.usage_count,
    q.status AS question_status,
    s.school_name,
    s.school_code,
    ch.chapter_name,
    ch.chapter_number,
    sub.subject_name,
    sub.subject_code,
    std.standard_name,
    b.board_name,
    u.full_name AS created_by_name,
    q.created_at,
    q.updated_at
FROM questions q
INNER JOIN schools s ON q.school_id = s.school_id
INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
INNER JOIN standards std ON sub.standard_id = std.standard_id
INNER JOIN boards b ON std.board_id = b.board_id
INNER JOIN users u ON q.created_by = u.user_id;

-- View: Generated Papers Summary
CREATE VIEW vw_papers_summary AS
SELECT 
    gp.paper_id,
    gp.paper_title,
    gp.paper_code,
    gp.total_marks,
    gp.duration_minutes,
    gp.status,
    s.school_name,
    b.board_name,
    std.standard_name,
    sub.subject_name,
    u.full_name AS generated_by_name,
    COUNT(gpq.question_id) AS total_questions,
    gp.generated_at
FROM generated_papers gp
INNER JOIN schools s ON gp.school_id = s.school_id
INNER JOIN boards b ON gp.board_id = b.board_id
INNER JOIN standards std ON gp.standard_id = std.standard_id
INNER JOIN subjects sub ON gp.subject_id = sub.subject_id
INNER JOIN users u ON gp.generated_by = u.user_id
LEFT JOIN generated_paper_questions gpq ON gp.paper_id = gpq.paper_id
GROUP BY gp.paper_id;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger: Increment question usage count when added to a paper
DELIMITER $$
CREATE TRIGGER trg_increment_question_usage
AFTER INSERT ON generated_paper_questions
FOR EACH ROW
BEGIN
    UPDATE questions 
    SET usage_count = usage_count + 1 
    WHERE question_id = NEW.question_id;
END$$
DELIMITER ;

-- Trigger: Log user login activity
DELIMITER $$
CREATE TRIGGER trg_log_user_login
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login != OLD.last_login THEN
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description)
        VALUES (NEW.user_id, 'login', 'user', NEW.user_id, 'User logged in');
    END IF;
END$$
DELIMITER ;

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Procedure: Get question statistics by school
DELIMITER $$
CREATE PROCEDURE sp_get_school_statistics(IN p_school_id INT UNSIGNED)
BEGIN
    SELECT 
        COUNT(DISTINCT q.question_id) AS total_questions,
        COUNT(DISTINCT gp.paper_id) AS total_papers,
        COUNT(DISTINCT u.user_id) AS total_teachers,
        SUM(CASE WHEN q.difficulty_level = 'easy' THEN 1 ELSE 0 END) AS easy_questions,
        SUM(CASE WHEN q.difficulty_level = 'medium' THEN 1 ELSE 0 END) AS medium_questions,
        SUM(CASE WHEN q.difficulty_level = 'hard' THEN 1 ELSE 0 END) AS hard_questions
    FROM schools s
    LEFT JOIN questions q ON s.school_id = q.school_id AND q.status = 'active'
    LEFT JOIN generated_papers gp ON s.school_id = gp.school_id
    LEFT JOIN users u ON s.school_id = u.school_id AND u.role = 'teacher' AND u.status = 'active'
    WHERE s.school_id = p_school_id;
END$$
DELIMITER ;

-- Procedure: Generate unique paper code
DELIMITER $$
CREATE PROCEDURE sp_generate_paper_code(OUT p_paper_code VARCHAR(50))
BEGIN
    DECLARE v_prefix VARCHAR(10);
    DECLARE v_timestamp VARCHAR(20);
    DECLARE v_random VARCHAR(10);
    
    SELECT setting_value INTO v_prefix FROM settings WHERE setting_key = 'paper_code_prefix';
    SET v_timestamp = DATE_FORMAT(NOW(), '%Y%m%d%H%i%s');
    SET v_random = LPAD(FLOOR(RAND() * 10000), 4, '0');
    SET p_paper_code = CONCAT(v_prefix, '-', v_timestamp, '-', v_random);
END$$
DELIMITER ;

-- ============================================
-- END OF SCHEMA
-- ============================================

-- Success message
SELECT 'Database schema created successfully!' AS message;
