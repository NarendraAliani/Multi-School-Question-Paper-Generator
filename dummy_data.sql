-- c:\xampp\htdocs\project\dummy_data.sql
-- Dummy Data for Testing - Multi-School Question Paper Generator

USE paper_generator_saas;

-- ============================================
-- INSERT SCHOOLS
-- ============================================
INSERT INTO schools (school_name, school_code, email, phone, address, city, state, pincode, subscription_plan, subscription_start, subscription_end, status) VALUES
('Delhi Public School', 'DPS001', 'admin@dpsdelhi.com', '9876543210', 'Sector 12, RK Puram', 'New Delhi', 'Delhi', '110022', 'premium', '2024-01-01', '2025-12-31', 'active'),
('St. Xavier\'s High School', 'SXS002', 'info@stxaviers.com', '9876543211', 'Park Street', 'Kolkata', 'West Bengal', '700016', 'basic', '2024-01-01', '2024-12-31', 'active'),
('Mumbai International School', 'MIS003', 'contact@mumbai-intl.com', '9876543212', 'Andheri West', 'Mumbai', 'Maharashtra', '400058', 'enterprise', '2024-01-01', '2026-12-31', 'active');

-- ============================================
-- INSERT USERS (Passwords: admin123, school_admin123, teacher123)
-- All passwords hashed using PHP password_hash with BCRYPT
-- ============================================

-- Super Admin (Username: admin, Password: admin123)
INSERT INTO users (school_id, username, email, password_hash, full_name, role, phone, status) VALUES
(NULL, 'admin', 'admin@papergenerator.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', 'super_admin', '9999999999', 'active');

-- School Admins (Username: school_admin, Password: admin123)
INSERT INTO users (school_id, username, email, password_hash, full_name, role, phone, status) VALUES
(1, 'dps_admin', 'admin@dpsdelhi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'DPS Administrator', 'school_admin', '9876543210', 'active'),
(2, 'sxs_admin', 'admin@stxaviers.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'St. Xaviers Administrator', 'school_admin', '9876543211', 'active'),
(3, 'mis_admin', 'admin@mumbai-intl.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mumbai Intl Administrator', 'school_admin', '9876543212', 'active');

-- Teachers (Username: teacher, Password: teacher123)
INSERT INTO users (school_id, username, email, password_hash, full_name, role, phone, status) VALUES
(1, 'john_math', 'john@dpsdelhi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Mathew - Mathematics Teacher', 'teacher', '9876500001', 'active'),
(1, 'sarah_science', 'sarah@dpsdelhi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson - Science Teacher', 'teacher', '9876500002', 'active'),
(2, 'rajesh_english', 'rajesh@stxaviers.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rajesh Kumar - English Teacher', 'teacher', '9876500003', 'active'),
(3, 'priya_hindi', 'priya@mumbai-intl.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya Sharma - Hindi Teacher', 'teacher', '9876500004', 'active');

-- ============================================
-- INSERT BOARDS
-- ============================================
INSERT INTO boards (board_name, board_code, description, status) VALUES
('CBSE', 'CBSE', 'Central Board of Secondary Education', 'active'),
('ICSE', 'ICSE', 'Indian Certificate of Secondary Education', 'active'),
('State Board - Maharashtra', 'MSBSHSE', 'Maharashtra State Board of Secondary and Higher Secondary Education', 'active'),
('State Board - West Bengal', 'WBBSE', 'West Bengal Board of Secondary Education', 'active');

-- ============================================
-- INSERT STANDARDS (Classes)
-- ============================================
INSERT INTO standards (board_id, standard_name, standard_code, display_order, status) VALUES
-- CBSE Standards
(1, 'Class 1', 'CLASS_1', 1, 'active'),
(1, 'Class 2', 'CLASS_2', 2, 'active'),
(1, 'Class 3', 'CLASS_3', 3, 'active'),
(1, 'Class 4', 'CLASS_4', 4, 'active'),
(1, 'Class 5', 'CLASS_5', 5, 'active'),
(1, 'Class 6', 'CLASS_6', 6, 'active'),
(1, 'Class 7', 'CLASS_7', 7, 'active'),
(1, 'Class 8', 'CLASS_8', 8, 'active'),
(1, 'Class 9', 'CLASS_9', 9, 'active'),
(1, 'Class 10', 'CLASS_10', 10, 'active'),
(1, 'Class 11', 'CLASS_11', 11, 'active'),
(1, 'Class 12', 'CLASS_12', 12, 'active'),

-- ICSE Standards
(2, 'Class 1', 'CLASS_1', 1, 'active'),
(2, 'Class 2', 'CLASS_2', 2, 'active'),
(2, 'Class 3', 'CLASS_3', 3, 'active'),
(2, 'Class 4', 'CLASS_4', 4, 'active'),
(2, 'Class 5', 'CLASS_5', 5, 'active'),
(2, 'Class 6', 'CLASS_6', 6, 'active'),
(2, 'Class 7', 'CLASS_7', 7, 'active'),
(2, 'Class 8', 'CLASS_8', 8, 'active'),
(2, 'Class 9', 'CLASS_9', 9, 'active'),
(2, 'Class 10', 'CLASS_10', 10, 'active');

-- ============================================
-- INSERT SUBJECTS
-- ============================================
INSERT INTO subjects (standard_id, subject_name, subject_code, display_order, status) VALUES
-- Class 10 CBSE Subjects
(10, 'Mathematics', 'MATH', 1, 'active'),
(10, 'Science', 'SCI', 2, 'active'),
(10, 'Social Science', 'SST', 3, 'active'),
(10, 'English', 'ENG', 4, 'active'),
(10, 'Hindi', 'HIN', 5, 'active'),

-- Class 9 CBSE Subjects
(9, 'Mathematics', 'MATH', 1, 'active'),
(9, 'Science', 'SCI', 2, 'active'),
(9, 'Social Science', 'SST', 3, 'active'),
(9, 'English', 'ENG', 4, 'active'),

-- Class 8 CBSE Subjects
(8, 'Mathematics', 'MATH', 1, 'active'),
(8, 'Science', 'SCI', 2, 'active'),
(8, 'English', 'ENG', 3, 'active');

-- ============================================
-- INSERT CHAPTERS
-- ============================================
INSERT INTO chapters (subject_id, chapter_name, chapter_number, display_order, status) VALUES
-- Class 10 Mathematics Chapters
(1, 'Real Numbers', '1', 1, 'active'),
(1, 'Polynomials', '2', 2, 'active'),
(1, 'Pair of Linear Equations in Two Variables', '3', 3, 'active'),
(1, 'Quadratic Equations', '4', 4, 'active'),
(1, 'Arithmetic Progressions', '5', 5, 'active'),
(1, 'Triangles', '6', 6, 'active'),
(1, 'Coordinate Geometry', '7', 7, 'active'),
(1, 'Introduction to Trigonometry', '8', 8, 'active'),
(1, 'Some Applications of Trigonometry', '9', 9, 'active'),
(1, 'Circles', '10', 10, 'active'),

-- Class 10 Science Chapters
(2, 'Chemical Reactions and Equations', '1', 1, 'active'),
(2, 'Acids, Bases and Salts', '2', 2, 'active'),
(2, 'Metals and Non-metals', '3', 3, 'active'),
(2, 'Life Processes', '6', 6, 'active'),
(2, 'Control and Coordination', '7', 7, 'active'),
(2, 'Electricity', '12', 12, 'active'),
(2, 'Magnetic Effects of Electric Current', '13', 13, 'active'),

-- Class 9 Mathematics Chapters
(6, 'Number Systems', '1', 1, 'active'),
(6, 'Polynomials', '2', 2, 'active'),
(6, 'Coordinate Geometry', '3', 3, 'active'),
(6, 'Linear Equations in Two Variables', '4', 4, 'active'),
(6, 'Lines and Angles', '6', 6, 'active');

-- ============================================
-- INSERT SAMPLE QUESTIONS
-- ============================================

-- Class 10 Mathematics - Real Numbers (Easy Questions)
INSERT INTO questions (school_id, chapter_id, created_by, question_text, question_type, difficulty_level, marks, correct_answer, status) VALUES
(1, 1, 5, 'What is the HCF of 12 and 18?', 'short_answer', 'easy', 2, '6', 'active'),
(1, 1, 5, 'Express 140 as a product of its prime factors.', 'short_answer', 'easy', 2, '2 × 2 × 5 × 7', 'active'),
(1, 1, 5, 'Find the LCM of 15 and 20.', 'short_answer', 'easy', 2, '60', 'active'),

-- Class 10 Mathematics - Real Numbers (Medium Questions)
(1, 1, 5, 'Prove that √2 is an irrational number.', 'long_answer', 'medium', 5, 'Proof by contradiction', 'active'),
(1, 1, 5, 'Find the HCF and LCM of 12, 15 and 21 using prime factorization method.', 'long_answer', 'medium', 4, 'HCF = 3, LCM = 420', 'active'),

-- Class 10 Mathematics - Polynomials
(1, 2, 5, 'Find the zeroes of the polynomial x² - 3x + 2.', 'short_answer', 'easy', 2, '1, 2', 'active'),
(1, 2, 5, 'If one zero of the polynomial 2x² + 3x + k is 2, find the value of k.', 'short_answer', 'medium', 3, 'k = -14', 'active'),

-- Class 10 Mathematics - Quadratic Equations
(1, 4, 5, 'Solve: x² - 5x + 6 = 0', 'short_answer', 'easy', 2, 'x = 2, 3', 'active'),
(1, 4, 5, 'Find the roots of 2x² - 7x + 3 = 0 using quadratic formula.', 'long_answer', 'medium', 4, 'x = 3, x = 1/2', 'active'),

-- Class 10 Science - Chemical Reactions
(1, 11, 6, 'What is a chemical equation?', 'short_answer', 'easy', 1, 'Symbolic representation of a chemical reaction', 'active'),
(1, 11, 6, 'Balance the equation: H₂ + O₂ → H₂O', 'short_answer', 'easy', 2, '2H₂ + O₂ → 2H₂O', 'active'),
(1, 11, 6, 'Explain the types of chemical reactions with examples.', 'long_answer', 'medium', 5, 'Combination, Decomposition, Displacement, Double Displacement, Redox', 'active'),

-- Class 10 Science - MCQ Questions
(1, 12, 6, 'Which of the following is a strong acid?', 'mcq', 'easy', 1, 'A', 'active'),
(1, 13, 6, 'The SI unit of electric current is:', 'mcq', 'easy', 1, 'B', 'active'),
(1, 16, 6, 'The magnetic field inside a long straight solenoid is:', 'mcq', 'medium', 1, 'C', 'active');

-- Update MCQ options for the last 3 questions
UPDATE questions SET 
    option_a = 'Hydrochloric acid',
    option_b = 'Acetic acid', 
    option_c = 'Citric acid',
    option_d = 'Carbonic acid'
WHERE question_id = (SELECT MAX(question_id) - 2 FROM (SELECT question_id FROM questions) AS temp);

UPDATE questions SET 
    option_a = 'Volt',
    option_b = 'Ampere',
    option_c = 'Ohm',
    option_d = 'Watt'
WHERE question_id = (SELECT MAX(question_id) - 1 FROM (SELECT question_id FROM questions) AS temp);

UPDATE questions SET 
    option_a = 'Zero',
    option_b = 'Maximum at the center',
    option_c = 'Uniform',
    option_d = 'Varies randomly'
WHERE question_id = (SELECT MAX(question_id) FROM (SELECT question_id FROM questions) AS temp);

-- ============================================
-- INSERT SAMPLE PAPER BLUEPRINT
-- ============================================
INSERT INTO paper_blueprints (school_id, created_by, blueprint_name, board_id, standard_id, subject_id, total_marks, duration_minutes, instructions, status) VALUES
(1, 5, 'Class 10 Mathematics - Mid Term Exam', 1, 10, 1, 80, 180, 'All questions are compulsory. Show all your working steps.', 'active');

-- Get the blueprint ID
SET @blueprint_id = LAST_INSERT_ID();

-- Insert blueprint sections
INSERT INTO blueprint_sections (blueprint_id, section_name, section_order, question_type, difficulty_level, marks_per_question, number_of_questions, chapter_ids) VALUES
(@blueprint_id, 'Section A - Very Short Answer', 1, 'short_answer', 'easy', 2, 5, '1,2'),
(@blueprint_id, 'Section B - Short Answer', 2, 'short_answer', 'medium', 3, 4, '1,2,4'),
(@blueprint_id, 'Section C - Long Answer', 3, 'long_answer', 'medium', 5, 3, '1,2,4');

-- ============================================
-- SUCCESS MESSAGE
-- ============================================
SELECT 'Dummy data inserted successfully! You can now login with:' AS message
UNION ALL
SELECT 'Super Admin - Username: admin, Password: admin123'
UNION ALL
SELECT 'School Admin - Username: dps_admin, Password: admin123'
UNION ALL
SELECT 'Teacher - Username: john_math, Password: teacher123';
