<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['user_type']) || !isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User type and ID required']);
    exit;
}

$user_type = $_GET['user_type'];
$user_id = $_GET['user_id'];

try {
    if ($user_type === 'teacher') {
        $stmt = $conn->prepare("
            SELECT s.id, s.subject_name, s.color_theme, s.icon_class
            FROM subjects s
            INNER JOIN teacher_subjects ts ON s.id = ts.subject_id
            WHERE ts.teacher_id = ? AND s.is_active = 1
        ");
    } else if ($user_type === 'student') {
        $stmt = $conn->prepare("
            SELECT s.id, s.subject_name, s.color_theme, s.icon_class
            FROM subjects s
            INNER JOIN student_subjects ss ON s.id = ss.subject_id
            WHERE ss.student_id = ? AND s.is_active = 1
        ");
    } else {
        throw new Exception('Invalid user type');
    }

    $stmt->bindValue(1, $user_id);
    $result = $stmt->execute();
    
    $subjects = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $subjects[] = [
            'id' => (int)$row['id'],
            'subject_name' => $row['subject_name'],
            'color_theme' => $row['color_theme'],
            'icon_class' => $row['icon_class']
        ];
    }
    
    echo json_encode(['success' => true, 'subjects' => $subjects]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
