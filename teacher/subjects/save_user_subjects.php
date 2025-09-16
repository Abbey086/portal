<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST method required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_type']) || !isset($input['user_id']) || !isset($input['subject_ids'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_type = $input['user_type'];
$user_id = $input['user_id'];
$subject_ids = $input['subject_ids'];

try {
    $conn->exec('BEGIN TRANSACTION');

    // Delete existing subject assignments
    if ($user_type === 'teacher') {
        $stmt = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ?");
    } else if ($user_type === 'student') {
        $stmt = $conn->prepare("DELETE FROM student_subjects WHERE student_id = ?");
    } else {
        throw new Exception('Invalid user type');
    }

    $stmt->bindValue(1, $user_id);
    $stmt->execute();

    // Insert new subject assignments
    if (!empty($subject_ids)) {
        if ($user_type === 'teacher') {
            $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        } else {
            $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
        }

        foreach ($subject_ids as $subject_id) {
            $stmt->bindValue(1, $user_id);
            $stmt->bindValue(2, $subject_id);
            $stmt->execute();
        }
    }

    $conn->exec('COMMIT');
    echo json_encode(['success' => true, 'message' => 'Subjects saved successfully']);
} catch (Exception $e) {
    $conn->exec('ROLLBACK');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
