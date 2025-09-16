<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schoolId = $_POST['school_id'];
    $name = $_POST['school_name'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE schools SET school_name = :name, email = :email, status = :status WHERE school_id = :id");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':id', $schoolId, SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo "School updated successfully.";
    } else {
        echo "Failed to update school.";
    }
}
?>