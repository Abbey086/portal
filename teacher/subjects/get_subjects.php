<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'School ID required']);
    exit;
}

$school_id = $_GET['school_id'];

try {
    $stmt = $conn->prepare("
        SELECT id, subject_name, subject_code, description, color_theme, icon_class, is_default
        FROM subjects 
        WHERE school_id = ? AND is_active = 1 
        ORDER BY is_default DESC, subject_name ASC
    ");
    
    $stmt->bindValue(1, $school_id);
    $result = $stmt->execute();
    
    $subjects = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $subjects[] = [
            'id' => (int)$row['id'],
            'subject_name' => $row['subject_name'],
            'subject_code' => $row['subject_code'],
            'description' => $row['description'],
            'color_theme' => $row['color_theme'],
            'icon_class' => $row['icon_class'],
            'is_default' => (bool)$row['is_default']
        ];
    }
    
    echo json_encode(['success' => true, 'subjects' => $subjects]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
