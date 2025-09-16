<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$school_id = $_SESSION['school_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
    exit();
}

// Fetch current password hash
$stmt = $conn->prepare("SELECT password FROM schools WHERE school_id = :school_id");
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

if (!$row || !password_verify($current_password, $row['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit();
}

// Update password
$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE schools SET password = :new_password WHERE school_id = :school_id");
$stmt->bindValue(':new_password', $new_password_hash, SQLITE3_TEXT);
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
?>