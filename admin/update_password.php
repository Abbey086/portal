<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['school_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}
function sanitize_input($u){
  return $u;
}
$current_password = sanitize_input($_POST['current_password']);
$new_password = sanitize_input($_POST['new_password']);
$school_id = $_SESSION['school_id'];

// Verify current password
$stmt = $conn->prepare("SELECT password FROM schools WHERE school_id = :school_id");
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$result = $stmt->execute();
$school = $result->fetchArray(SQLITE3_ASSOC);

if (!$school || !password_verify($current_password, $school['password'])) {
            header('Location: settings.php?message='."Current password is incorrect");
    
    exit;
}

// Update password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE schools SET password = :password WHERE school_id = :school_id");
$stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);

try {
    $result = $stmt->execute();
    if ($result) {
        header('Location: settings.php?message='."Password updated successfully");

    } else {
    
            header('Location: settings.php?message='."Failed to update password");
}
} catch (Exception $e) {
                header('Location: settings.php?message='."Failed to update password");

}

exit();

?>