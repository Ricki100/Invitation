<?php
// Heroku Deployment Configuration
// This file helps configure the app for Heroku deployment

// Heroku automatically sets the PORT environment variable
$port = getenv('PORT') ?: 8000;

// Database configuration for Heroku (if using add-ons)
$database_url = getenv('DATABASE_URL');

// Environment detection
$is_heroku = getenv('DYNO') !== false;

if ($is_heroku) {
    // Heroku-specific configurations
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    // Use Heroku's temp directory for uploads
    define('UPLOAD_DIR', '/tmp/uploads/');
    define('DATA_DIR', '/tmp/data/');
} else {
    // Local development
    define('UPLOAD_DIR', 'uploads/');
    define('DATA_DIR', 'data/');
}

// Create directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

echo "🚀 Heroku Configuration Loaded\n";
echo "📁 Upload Directory: " . UPLOAD_DIR . "\n";
echo "📁 Data Directory: " . DATA_DIR . "\n";
echo "🌐 Environment: " . ($is_heroku ? 'Heroku' : 'Local') . "\n";
?> 