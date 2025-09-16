<?php
// Set timezone
date_default_timezone_set('UTC');

// Path to the SQLite database file
$dbPath = 'school_portal.db';

try {
    // Connect to SQLite database
    $conn = new SQLite3($dbPath);

    // Enable foreign key support
    $conn->exec("PRAGMA foreign_keys = ON");

    // Create the 'adminz' table if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS adminz (
        admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Optional: return true silently
    // return true;

} catch (Exception $e) {
    // Optional: handle error silently or redirect to an error page
    die("Database connection failed: " . $e->getMessage());
}
?>