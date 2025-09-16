<?php
require 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['assignment_file'])) {
    $assignment_id = $_POST['assignment_id'];
    $student_id = $_SESSION['student_id'];

    $file = $_FILES['assignment_file'];
    $filename = time() . '_' . basename($file['name']);
    $target = 'submissions/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path)
            VALUES (:assignment_id, :student_id, :file_path)");
        $stmt->bindValue(':assignment_id', $assignment_id);
        $stmt->bindValue(':student_id', $student_id);
        $stmt->bindValue(':file_path', $target);
        $stmt->execute();
        echo "Assignment submitted!";
    } else {
        echo "Upload failed.";
    }
}
?>