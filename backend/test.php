<?php
// Test file to verify the backend is working

echo "<h1>SootheSpace Backend Test</h1>";
echo "<p>Backend structure created successfully!</p>";

// Test database connection
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// List created files
$backendDir = __DIR__;
$files = [
    'config/database.php',
    'config/config.php', 
    'includes/functions.php',
    'includes/auth.php',
    'models/User.php',
    'models/MoodEntry.php',
    'models/JournalEntry.php',
    'models/Reminder.php',
    'api/auth.php',
    'api/mood.php',
    'api/journal.php',
    'api/reminders.php',
    'api/insights.php',
    'index.php',
    'database_schema.sql'
];

echo "<h2>Created Files:</h2><ul>";
foreach ($files as $file) {
    $fullPath = $backendDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<li style='color: green;'>✓ $file</li>";
    } else {
        echo "<li style='color: red;'>✗ $file</li>";
    }
}
echo "</ul>";

echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Create the database using the SQL file: database_schema.sql</li>";
echo "<li>Update database credentials in config/database.php if needed</li>";
echo "<li>Test the API endpoints</li>";
echo "</ol>";
?>