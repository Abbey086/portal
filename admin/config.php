<?php
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbPath = '../school_portal.db';

try {
    $conn = new SQLite3($dbPath);
    $conn->exec('PRAGMA foreign_keys = ON;');
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

?>