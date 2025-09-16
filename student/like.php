<?php
session_start();
require_once 'config.php';

$student_id = $_SESSION['student_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $student_id) {
    $postId = $_GET['post_id'];

    $exists = $conn->querySingle("SELECT COUNT(*) FROM info_likes WHERE post_id = $postId AND student_id = '$student_id'");

    if (!$exists) {
        $conn->exec("INSERT INTO info_likes (post_id, student_id) VALUES ($postId, '$student_id')");
            $newLikeCount = $conn->querySingle("SELECT COUNT(*) as count FROM info_likes WHERE post_id = $postId AND student_id = '$student_id'");

        echo $newLikeCount;
    }
    else{
      echo("liked");
    }
}
?>