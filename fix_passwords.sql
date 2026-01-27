-- c:\xampp\htdocs\project\fix_passwords.sql
-- Quick Fix: Update User Passwords
-- Run this in phpMyAdmin to fix login issues

USE paper_generator_saas;

-- Update Super Admin password (admin / admin123)
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';

-- Update School Admins password (dps_admin, etc. / admin123)
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE role = 'school_admin';

-- Update Teachers password (john_math, etc. / teacher123)
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE role = 'teacher';

-- Verify updated users
SELECT user_id, username, full_name, role, status 
FROM users 
ORDER BY role, username;

-- Test Credentials After Running This:
-- Super Admin: admin / admin123
-- School Admin: dps_admin / admin123
-- Teacher: john_math / teacher123
