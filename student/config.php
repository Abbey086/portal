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
function hrd($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
//date('Y-m-d g:i A', $timestamp) <= date('Y-m-d g:i A', $today)
//date('Y-m-d', $timestamp) <= date('Y-m-d', $today) && date('g:i A', $timestamp) > date('g:i A', $today)

    $yesterday = strtotime('-1 day', $today);
    if (date('Y-m-d', $timestamp) == date('Y-m-d', $today)) {
        return 'Today at ' . date('g:i A', $timestamp);
    } elseif (date('Y-m-d', $timestamp) == date('Y-m-d', $yesterday)) {
        return 'Yesterday at ' . date('g:i A', $timestamp);
    } elseif ($timestamp >= strtotime('-6 days', $today)) {
        return date('l', $timestamp) . ' at ' . date('g:i A', $timestamp);
    } else {
        return date('M j, Y \a\t g:i A', $timestamp);
    }
}

?>