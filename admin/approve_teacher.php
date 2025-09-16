<?php
require 'config.php';
session_start();

if (!isset($_SESSION['school_id'])) {
    die("Access denied.");
}

$id = $_GET['id'] ?? null;
$st = $_GET['status'] ?? null;

if (!$id) {
    die("No teacher selected.");
}

$activate = $conn->prepare("UPDATE teachers SET status = :status WHERE id = :id AND school_id = :school_id");
$activate->bindValue(':id', $id, SQLITE3_INTEGER);
$activate->bindValue(':school_id', $_SESSION['school_id'], SQLITE3_TEXT);
$activate->bindValue(':status', $st, SQLITE3_TEXT);
$activate->execute();

header("Location: view_teachers.php?msg=approve");
exit;
?>