<?php
// Create uploads directory structure for school branding

$base_dir = __DIR__ . '/../uploads';
$logo_dir = $base_dir . '/schools';

// Create directories if they don't exist
if (!is_dir($base_dir)) {
    mkdir($base_dir, 0755, true);
    echo "Created: $base_dir\n";
}

if (!is_dir($logo_dir)) {
    mkdir($logo_dir, 0755, true);
    echo "Created: $logo_dir\n";
}

// Create .htaccess to prevent direct access to images
$htaccess_content = "# Deny direct access to upload files
<FilesMatch \"\\.(php|phar|phar|exe|pl|cgi|sh)$\">
    Order allow,deny
    Deny from all
</FilesMatch>
";

// Only write if file doesn't exist
if (!file_exists($base_dir . '/.htaccess')) {
    file_put_contents($base_dir . '/.htaccess', $htaccess_content);
    echo "Created: .htaccess protection\n";
}

echo "Done! Uploads directory ready.\n";
