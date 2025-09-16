<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schoolId = trim($_POST['schoolId']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, school_name, password, status FROM schools WHERE school_id = :schoolId");
    $stmt->bindValue(':schoolId', $schoolId, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $school = $result->fetchArray(SQLITE3_ASSOC);
    if ($school && password_verify($password, $school['password'])) {
    if (in_array($school['status'], ['approved', 'active'])) {
        $_SESSION['school_logged_in'] = true;
        $_SESSION['school_id'] = $school['school_id'];
        $_SESSION['school_name'] = $school['school_name'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $message = "Your account is not active. Please wait for admin approval.";
    }
} else {
    $message = "Invalid School ID or Password.";
}
    
    header("Location: login.php?error=invalid_credentials");
    exit;
}
?>