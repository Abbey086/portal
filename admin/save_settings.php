<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['school_id'])) {
    header('Location: login.html');
    exit();
}

$school_id = $_SESSION['school_id'];
$classes = $_POST["classes"];

// Update or insert academic year setting
$stmt = $conn->prepare("UPDATE schools SET classes = :classes WHERE school_id = :school_id");
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$stmt->bindValue(':classes', $classes, SQLITE3_TEXT);
$stmt->execute();

// Handle logo upload
if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileTmpPath = $_FILES['school_logo']['tmp_name'];
    $fileName = basename($_FILES['school_logo']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
foreach( $allowedExtensions as $ex){
    $url = "uploads/logo_$school_id.$ex";
  if(file_exists($url)){
    unlink($url);
  }
}

    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = 'logo_' . $school_id . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Update or insert logo path setting
$stmt = $conn->prepare("UPDATE schools SET logo_url = :logo WHERE school_id = :school_id");
            $stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
            $stmt->bindValue(':logo', $destPath, SQLITE3_TEXT);
            $stmt->execute();
            
            
        }
    }
}

header('Location: settings.php');
exit();
?>