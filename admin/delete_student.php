<?php
require 'config.php';
session_start();

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}
$id = $_GET['id'] ?? null;

if (!$id) {
    die("No student selected.");
}

$delete = $conn->prepare("DELETE FROM students WHERE id = :id AND school_id = :school_id");
$delete->bindValue(':id', $id, SQLITE3_INTEGER);
$delete->bindValue(':school_id', $_SESSION['school_id'], SQLITE3_TEXT);
$delete->execute();

header("Location: view_students.php?msg=deleted");
exit;
?>