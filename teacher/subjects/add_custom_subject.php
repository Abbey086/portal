<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST method required']);
    exit;
}

$required_fields = ['subject_name', 'school_id'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Field $field is required"]);
        exit;
    }
}

$subject_name = trim($_POST['subject_name']);
$school_id = $_POST['school_id'];
$subject_code = isset($_POST['subject_code']) ? trim($_POST['subject_code']) : strtoupper(substr($subject_name, 0, 4));
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$color_theme = isset($_POST['color_theme']) ? $_POST['color_theme'] : '#667eea';
$icon_class = isset($_POST['icon_class']) ? $_POST['icon_class'] : 'fas fa-book';

try {
    // Check if subject already exists for this school
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE school_id = ? AND subject_name = ?");
    $stmt->bindValue(1, $school_id);
    $stmt->bindValue(2, $subject_name);
    $result = $stmt->execute();
    
    if ($result->fetchArray()) {
        echo json_encode(['success' => false, 'message' => 'Subject already exists']);
        exit;
    }

    // Insert new subject
    $stmt = $conn->prepare("
        INSERT INTO subjects (school_id, subject_name, subject_code, description, color_theme, icon_class, is_default) 
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");
    
    $stmt->bindValue(1, $school_id);
    $stmt->bindValue(2, $subject_name);
    $stmt->bindValue(3, $subject_code);
    $stmt->bindValue(4, $description);
    $stmt->bindValue(5, $color_theme);
    $stmt->bindValue(6, $icon_class);
    
    $result = $stmt->execute();
    
    if ($result) {
        $subject_id = $conn->lastInsertRowID();
        echo json_encode([
            'success' => true, 
            'message' => 'Custom subject added successfully',
            'subject_id' => $subject_id
        ]);
    } else {
        throw new Exception('Failed to insert subject');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
