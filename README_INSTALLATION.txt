# c:\xampp\htdocs\project\README_INSTALLATION.txt
================================================================================
    MULTI-SCHOOL QUESTION PAPER GENERATOR - INSTALLATION GUIDE
================================================================================

Project: Multi-School Question Paper Generator SaaS
Version: 1.0.0
Author: AI Generated Full-Stack Application
Technology Stack: PHP 8+, MySQL, HTML5, CSS3, Bootstrap 5, jQuery, AJAX

================================================================================
SYSTEM REQUIREMENTS
================================================================================

1. Web Server:
   - Apache 2.4+ with mod_rewrite enabled
   - OR XAMPP/WAMP (Recommended for development)

2. PHP Requirements:
   - PHP 8.0 or higher
   - Extensions: PDO, PDO_MySQL, GD, mbstring, fileinfo

3. Database:
   - MySQL 5.7+ or MariaDB 10.2+

4. Browser:
   - Modern browsers (Chrome, Firefox, Edge, Safari)
   - JavaScript enabled

================================================================================
INSTALLATION STEPS
================================================================================

STEP 1: SETUP PROJECT FILES
----------------------------
1. Extract/Copy the project folder to your web server directory:
   - XAMPP: C:\xampp\htdocs\project
   - WAMP: C:\wamp64\www\project
   - Linux: /var/www/html/project

2. Verify folder structure exists:
   project/
   ├── config/
   ├── includes/
   ├── modules/
   ├── templates/
   ├── assets/
   ├── uploads/
   ├── auth/
   ├── admin/
   ├── teacher/
   ├── schema.sql
   ├── dummy_data.sql
   └── index.php

STEP 2: CREATE REQUIRED DIRECTORIES
------------------------------------
Ensure these directories exist and are writable (chmod 755 on Linux):

1. uploads/questions/
2. uploads/schools/
3. logs/

On Windows (XAMPP):
   - Right-click each folder → Properties → Security
   - Grant "Modify" permissions to Users/IUSR

On Linux:
   chmod -R 755 uploads/
   chmod -R 755 logs/
   chown -R www-data:www-data uploads/
   chown -R www-data:www-data logs/

STEP 3: DATABASE SETUP
----------------------
1. Start Apache and MySQL from XAMPP/WAMP Control Panel

2. Open phpMyAdmin (http://localhost/phpmyadmin)

3. Create Database:
   - Click "New" in the left sidebar
   - Database name: paper_generator_saas
   - Collation: utf8mb4_unicode_ci
   - Click "Create"

4. Import Schema:
   - Select the database "paper_generator_saas"
   - Click "Import" tab
   - Choose file: schema.sql
   - Click "Go"
   - Wait for success message

5. Import Dummy Data (Optional but Recommended):
   - Stay in "Import" tab
   - Choose file: dummy_data.sql
   - Click "Go"
   - Wait for success message

STEP 4: CONFIGURE DATABASE CONNECTION
--------------------------------------
1. Open file: config/constants.php

2. Update database credentials (Lines 9-12):
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'paper_generator_saas');
   define('DB_USER', 'root');           // Change if different
   define('DB_PASS', '');               // Add password if set

3. Update BASE_URL (Line 27):
   define('BASE_URL', 'http://localhost/project');
   
   Note: Change 'project' to your actual folder name if different

4. Save the file

STEP 5: VERIFY INSTALLATION
----------------------------
1. Open browser and navigate to:
   http://localhost/project

2. You should be redirected to:
   http://localhost/project/auth/login.php

3. If you see the login page, installation is successful!

================================================================================
DEFAULT LOGIN CREDENTIALS (From dummy_data.sql)
================================================================================

Super Administrator:
   Username: admin
   Password: admin123
   Access: Full system control, manage all schools

School Administrator (DPS):
   Username: dps_admin
   Password: admin123
   Access: Manage school users, view reports

Teacher (Mathematics):
   Username: john_math
   Password: teacher123
   Access: Create questions, generate papers

IMPORTANT: Change these passwords immediately after first login!

================================================================================
POST-INSTALLATION STEPS
================================================================================

1. SECURITY CHECKLIST:
   ☐ Change all default passwords
   ☐ Update config/constants.php - Set DEBUG_MODE to false in production
   ☐ Enable HTTPS (SSL Certificate) for production
   ☐ Update .htaccess - Uncomment HTTPS redirect
   ☐ Review file permissions
   ☐ Setup regular database backups

2. APPLICATION SETUP:
   ☐ Login as Super Admin
   ☐ Add Schools from Admin Panel
   ☐ Create School Admins
   ☐ Setup Boards (CBSE, ICSE, etc.)
   ☐ Add Standards (Classes)
   ☐ Add Subjects
   ☐ Add Chapters
   ☐ Teachers can now add Questions

3. TESTING:
   ☐ Test dependent dropdowns (Board → Standard → Subject → Chapter)
   ☐ Add sample questions with different difficulty levels
   ☐ Create a paper blueprint
   ☐ Generate a test paper
   ☐ Preview and print paper

================================================================================
FOLDER STRUCTURE EXPLANATION
================================================================================

config/           - Configuration files (DB, constants, session)
includes/         - Core utility functions and common files
modules/          - Business logic (paper generator, AJAX endpoints, models)
templates/        - View templates (paper print view, email templates)
assets/           - Static resources (CSS, JS, images)
uploads/          - User uploaded content (questions, school logos)
auth/             - Authentication (login, logout)
admin/            - Super Admin panel (schools, users, masters)
teacher/          - Teacher panel (questions, papers)
logs/             - Error and activity logs (auto-created)

================================================================================
TROUBLESHOOTING
================================================================================

PROBLEM: "Database connection error"
SOLUTION: 
- Check MySQL service is running
- Verify credentials in config/constants.php
- Ensure database exists

PROBLEM: "Page not found" or CSS not loading
SOLUTION:
- Check BASE_URL in config/constants.php
- Verify .htaccess file exists
- Ensure mod_rewrite is enabled in Apache

PROBLEM: "Headers already sent" error
SOLUTION:
- Check for whitespace before <?php tags
- Ensure no output before header() calls
- Save files with UTF-8 encoding without BOM

PROBLEM: "Permission denied" for uploads
SOLUTION:
- Make uploads/ directory writable
- Check folder permissions (755 or 777)
- Verify web server user has write access

PROBLEM: AJAX dependent dropdowns not working
SOLUTION:
- Check browser console for JavaScript errors
- Verify jQuery is loading (check network tab)
- Ensure AJAX endpoints are accessible
- Check BASE_URL in config/constants.php

PROBLEM: "Cannot modify header information"
SOLUTION:
- Check for echo/print statements before redirects
- Ensure no whitespace before <?php tags
- Check file encoding (UTF-8 without BOM)

================================================================================
FEATURES IMPLEMENTED
================================================================================

✓ Multi-School SaaS Architecture
✓ Role-Based Access Control (Super Admin, School Admin, Teacher)
✓ Complete Authentication System with Session Management
✓ Secure PDO Database Class with Prepared Statements
✓ Master Data Management (Boards, Standards, Subjects, Chapters)
✓ Question Bank with Image Upload Support
✓ Question Categorization (Type, Difficulty, Marks)
✓ Paper Blueprint/Template System
✓ Intelligent Paper Generation Algorithm
✓ AJAX Dependent Dropdowns (Board → Standard → Subject → Chapter)
✓ Responsive Design with Bootstrap 5
✓ A4 Print-Ready Paper Format
✓ Activity Logging System
✓ CSRF Protection
✓ Input Sanitization & Validation
✓ Flash Messages
✓ Pagination Support
✓ File Upload Security

================================================================================
DATABASE SCHEMA OVERVIEW
================================================================================

13 Tables in Normalized 3NF:
1. schools               - School information
2. users                 - User accounts (all roles)
3. boards                - Educational boards (CBSE, ICSE, etc.)
4. standards             - Classes/Standards (1-12)
5. subjects              - Subjects per standard
6. chapters              - Chapters/Units per subject
7. questions             - Question bank with metadata
8. paper_blueprints      - Paper templates
9. blueprint_sections    - Sections within blueprints
10. generated_papers     - Generated question papers
11. generated_paper_questions - Questions in each paper
12. activity_logs        - Audit trail
13. settings             - Application settings

2 Views for Reporting:
- vw_questions_complete  - Complete question hierarchy
- vw_papers_summary      - Paper generation summary

2 Triggers:
- trg_increment_question_usage - Track question usage
- trg_log_user_login     - Log login activities

2 Stored Procedures:
- sp_get_school_statistics - School-wise statistics
- sp_generate_paper_code   - Unique paper code generation

================================================================================
NEXT STEPS & FUTURE ENHANCEMENTS
================================================================================

Recommended Features to Add:
□ PDF Export using TCPDF/FPDF
□ Answer Key Generation
□ Paper Analytics Dashboard
□ Question Import from Excel/CSV
□ Multiple Question Sets (A, B, C)
□ Email Notifications
□ Advanced Search in Question Bank
□ Question Bank Sharing between Teachers
□ Paper Versioning
□ Bulk Operations
□ API for Mobile App Integration

================================================================================
SUPPORT & DOCUMENTATION
================================================================================

Technical Documentation:
- All code is well-commented
- Each file has its full path as first line comment
- Functions have proper PHPDoc comments

For Issues:
1. Check error logs: logs/error.log
2. Enable DEBUG_MODE in config/constants.php
3. Check browser console for JavaScript errors
4. Verify database schema is complete

================================================================================
LICENSE & CREDITS
================================================================================

This is a complete, production-ready MVP (Minimum Viable Product) generated
as per "God-Mode" specifications.

Technology Stack:
- Backend: PHP 8+ (Procedural/OOP Hybrid)
- Database: MySQL with strict 3NF normalization
- Frontend: HTML5, CSS3, Bootstrap 5
- JavaScript: jQuery, AJAX
- Architecture: MVC-ish pattern

Generated: 2025
Version: 1.0.0

================================================================================
END OF INSTALLATION GUIDE
================================================================================

Thank you for using Multi-School Question Paper Generator!
For best results, follow all steps carefully and refer to the troubleshooting
section if you encounter any issues.

Happy Teaching! 📚✨
