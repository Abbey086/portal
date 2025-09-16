<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    if ($user_type === 'teacher') {
        $stmt = $conn->prepare("SELECT full_name, school_id FROM teachers WHERE id = ?");
    } else if ($user_type === 'student') {
        $stmt = $conn->prepare("SELECT full_name, school_id FROM students WHERE id = ?");
    } else {
        throw new Exception('Invalid user type');
    }

    $stmt->bindValue(1, $user_id);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        echo json_encode([
            'success' => true,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'full_name' => $user['full_name'],
            'school_id' => $user['school_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
