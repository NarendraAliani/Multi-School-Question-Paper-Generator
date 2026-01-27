<?php
// c:\xampp\htdocs\project\setup_passwords.php
// One-Time Password Setup - DELETE THIS FILE AFTER USE!

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db_connect.php';

$db = getDB();

// Define correct passwords for each role
$users_to_update = [
    // Super Admin
    ['username' => 'admin', 'password' => 'admin123'],
    
    // School Admins
    ['username' => 'dps_admin', 'password' => 'admin123'],
    ['username' => 'sxs_admin', 'password' => 'admin123'],
    ['username' => 'mis_admin', 'password' => 'admin123'],
    
    // Teachers
    ['username' => 'john_math', 'password' => 'teacher123'],
    ['username' => 'sarah_science', 'password' => 'teacher123'],
    ['username' => 'rajesh_english', 'password' => 'teacher123'],
    ['username' => 'priya_hindi', 'password' => 'teacher123'],
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Password Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .success { color: green; }
        .error { color: red; }
        .info { background: #f0f0f0; padding: 15px; margin: 20px 0; border-left: 4px solid #007bff; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>
    <h1>🔧 Password Setup Tool</h1>
    <p>This tool will set up all user passwords correctly.</p>";

echo "<h2>Updating Passwords...</h2>";

$updated_count = 0;
$errors = [];

foreach ($users_to_update as $user) {
    $password_hash = password_hash($user['password'], PASSWORD_BCRYPT);
    
    $query = "UPDATE users SET password_hash = ? WHERE username = ?";
    $result = $db->update($query, [$password_hash, $user['username']]);
    
    if ($result !== false) {
        echo "<div class='success'>✓ Updated: {$user['username']} → Password: {$user['password']}</div>";
        $updated_count++;
    } else {
        echo "<div class='error'>✗ Failed: {$user['username']}</div>";
        $errors[] = $user['username'];
    }
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>✓ Successfully updated:</strong> $updated_count users</p>";

if (!empty($errors)) {
    echo "<p class='error'><strong>✗ Failed:</strong> " . implode(', ', $errors) . "</p>";
}

// Display all users
$all_users = $db->select("SELECT user_id, username, full_name, role, status FROM users ORDER BY role, username");

echo "<h2>All Users in Database:</h2>";
echo "<table>";
echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Status</th></tr>";

foreach ($all_users as $user) {
    echo "<tr>";
    echo "<td>{$user['user_id']}</td>";
    echo "<td><strong>{$user['username']}</strong></td>";
    echo "<td>{$user['full_name']}</td>";
    echo "<td>" . ucfirst(str_replace('_', ' ', $user['role'])) . "</td>";
    echo "<td>{$user['status']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<div class='info'>
    <h3>📋 Test Credentials:</h3>
    <table>
        <tr><th>Role</th><th>Username</th><th>Password</th></tr>
        <tr><td>Super Admin</td><td><strong>admin</strong></td><td><strong>admin123</strong></td></tr>
        <tr><td>School Admin</td><td><strong>dps_admin</strong></td><td><strong>admin123</strong></td></tr>
        <tr><td>Teacher</td><td><strong>john_math</strong></td><td><strong>teacher123</strong></td></tr>
    </table>
    
    <p><strong>🎯 You can now login at:</strong> <a href='auth/login.php'>http://localhost/project/auth/login.php</a></p>
    
    <p style='color: red;'><strong>⚠️ IMPORTANT: Delete this file (setup_passwords.php) after use for security!</strong></p>
</div>

</body>
</html>";
