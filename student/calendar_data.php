<?php
require 'config.php';

session_start();

if (!isset($_SESSION['student_id'])) {
    echo json_encode([]);
    exit;
}

$student_id = $_SESSION['student_id'];

// Get class (grade_level) of student
$classQuery = $conn->prepare("SELECT grade_level FROM students WHERE id = :id");
$classQuery->bindValue(':id', $student_id, SQLITE3_INTEGER);
$classRow = $classQuery->execute()->fetchArray(SQLITE3_ASSOC);
$class = $classRow['grade_level'];

$events = [];

// Fetch Assignments
$assignmentStmt = $conn->prepare("SELECT a.title, a.due_date FROM assignments a 
    JOIN classes c ON a.class_id = c.id WHERE c.class_name = :class");
$assignmentStmt->bindValue(':class', $class, SQLITE3_TEXT);
$assignmentRes = $assignmentStmt->execute();

while ($a = $assignmentRes->fetchArray(SQLITE3_ASSOC)) {
    $events[] = [
        'title' => 'Assignment: ' . $a['title'],
        'start' => $a['due_date'],
        'color' => '#ff5252'
    ];
}

// Fetch Office Hour Bookings
$bookingStmt = $conn->prepare("SELECT o.start_datetime, t.full_name 
    FROM bookings b 
    JOIN office_hours o ON b.office_hour_id = o.id 
    JOIN teachers t ON o.teacher_id = t.id 
    WHERE b.student_id = :id");
$bookingStmt->bindValue(':id', $student_id, SQLITE3_INTEGER);
$bookingRes = $bookingStmt->execute();

while ($b = $bookingRes->fetchArray(SQLITE3_ASSOC)) {
    $events[] = [
        'title' => 'Office Hour w/ ' . $b['full_name'],
        'start' => $b['start_datetime'],
        'color' => '#42a5f5'
    ];
}

// Fetch Announcements
$announcementStmt = $conn->prepare("SELECT a.title, a.created_at 
    FROM announcements a 
    JOIN classes c ON a.class_id = c.id 
    WHERE c.class_name = :class");
$announcementStmt->bindValue(':class', $class, SQLITE3_TEXT);
$annRes = $announcementStmt->execute();

while ($an = $annRes->fetchArray(SQLITE3_ASSOC)) {
    $events[] = [
        'title' => 'Announcement: ' . $an['title'],
        'start' => $an['created_at'],
        'color' => '#66bb6a'
    ];
}

header('Content-Type: application/json');
echo json_encode($events);