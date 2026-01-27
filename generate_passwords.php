<?php
// c:\xampp\htdocs\project\generate_passwords.php
// Password Hash Generator - DELETE THIS FILE AFTER USE

echo "<h2>Password Hash Generator</h2>";
echo "<p>Copy these hashes to update dummy_data.sql</p><hr>";

$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_BCRYPT),
    'teacher123' => password_hash('teacher123', PASSWORD_BCRYPT),
];

foreach ($passwords as $plain => $hash) {
    echo "<strong>$plain:</strong><br>";
    echo "<code>$hash</code><br><br>";
}

echo "<hr>";
echo "<h3>Quick Test Login:</h3>";
echo "Username: <strong>admin</strong> / Password: <strong>admin123</strong><br>";
echo "Username: <strong>dps_admin</strong> / Password: <strong>admin123</strong><br>";
echo "Username: <strong>john_math</strong> / Password: <strong>teacher123</strong><br>";
