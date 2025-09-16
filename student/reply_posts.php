<?php
session_start();
require_once 'config.php';

$student_id = $_SESSION['student_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student_id) {
    $postId = (int)$_POST['post_id'];
    $reply = trim($_POST['reply']);

    if ($reply !== '') {
        $stmt = $conn->prepare("INSERT INTO info_replies (post_id, student_id, content) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $postId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $student_id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $reply, SQLITE3_TEXT);
        $stmt->execute();
    }
}
?>