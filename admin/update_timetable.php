<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}

$school_id = $_SESSION['school_id'];

$class_id = $_GET['class'];
$data = $_GET['data'];
  $cit = $conn->querySingle("SELECT COUNT(*) FROM timetables WHERE school_id = '$school_id' AND class_id = '$class_id'");
$exists = $cit == 0?false:true;
if(!$exists){
try {
    $stmt = $conn->prepare("
        INSERT INTO timetables (class_id, school_id, data) VALUES (:class_id, :school_id, :data)
    ");
    $stmt->bindParam(':data', $data, SQLITE3_TEXT);
    $stmt->bindParam(':school_id', $school_id, SQLITE3_TEXT);
    $stmt->bindParam(':class_id', $class_id, SQLITE3_TEXT);
    $stmt->execute();

    echo "success";
} catch (PDOException $e) {
    echo $e->getMessage();
}}
else{
try {
    $stmt = $conn->prepare("
        UPDATE timetables SET data = :data WHERE school_id = :school_id AND class_id = :class_id
    ");
    $stmt->bindParam(':data', $data, SQLITE3_TEXT);
    $stmt->bindParam(':school_id', $school_id, SQLITE3_TEXT);
    $stmt->bindParam(':class_id', $class_id, SQLITE3_TEXT);
    $stmt->execute();

    echo "success";
} catch (PDOException $e) {
    echo $e->getMessage();
}}

?>