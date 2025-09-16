<?php
require 'config.php';  // Adjust path if needed

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: students_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $teacher_id = $_POST['teacher_id'];
    $question = trim($_POST['question']);

    if (!empty($teacher_id) && !empty($question)) {
        $stmt = $conn->prepare("
            INSERT INTO teacher_questions (student_id, teacher_id, question)
            VALUES (:student_id, :teacher_id, :question)
        ");
        $stmt->bindValue(':student_id', $student_id);
        $stmt->bindValue(':teacher_id', $teacher_id);
        $stmt->bindValue(':question', $question);
        $stmt->execute();

        echo "Your question has been sent successfully!";
    } else {
        echo "Please select a teacher and enter your question.";
    }
}
?>