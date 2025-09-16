<?php
require_once 'config.php';

// Sample admin data (you can modify or get from a form)
$name = 'Super Admin';
$email = 'admin@example.com';
$password = 'admin123'; // Plain password

// Check if admin already exists
$stmt = $conn->prepare("SELECT admin_id FROM adminz WHERE email = :email");
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$result = $stmt->execute();

if ($result->fetchArray(SQLITE3_ASSOC)) {
    echo "Admin with this email already exists.";
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin
$stmt = $conn->prepare("INSERT INTO adminz (name, email, password) VALUES (:name, :email, :password)");
$stmt->bindValue(':name', $name, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

if ($stmt->execute()) {
    echo "Admin inserted successfully.";
} else {
    echo "Failed to insert admin.";
}
?>