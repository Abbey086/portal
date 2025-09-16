<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schoolName = trim($_POST['schoolName']);
    $email = trim($_POST['schoolEmail']);
    $password = $_POST['password'];

    // Generate unique school ID
    function generateUniqueSchoolId($conn) {
        do {
            $prefix = 'SCH';
            $timestamp = substr(time(), -6);
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $schoolId = $prefix . $timestamp . $random;

            $stmt = $conn->prepare("SELECT id FROM schools WHERE school_id = :schoolId");
            $stmt->bindValue(':schoolId', $schoolId, SQLITE3_TEXT);
            $result = $stmt->execute();
        } while ($result->fetchArray(SQLITE3_ASSOC));

        return $schoolId;
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM schools WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($result->fetchArray(SQLITE3_ASSOC)) {
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit;
        }

        $schoolId = generateUniqueSchoolId($conn);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new school
        $stmt = $conn->prepare("INSERT INTO schools (school_id, school_name, email, password) VALUES (:schoolId, :schoolName, :email, :password)");
        $stmt->bindValue(':schoolId', $schoolId, SQLITE3_TEXT);
        $stmt->bindValue(':schoolName', $schoolName, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);

        if ($stmt->execute()) {
            
            $_SESSION['registration_success'] = true;
            $_SESSION['school_id'] = $schoolId;
            header("Location: registration_success.php");
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed. Please try again.']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>