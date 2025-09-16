<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode([]);  // Not logged in
    exit;
}

// Connect to the database
$conn = new SQLite3($dbPath);

// Fetch upcoming events for the student's school
$school_id = $_SESSION['school_id'];

// For demonstration, let's assume events are stored in a table called 'events'
$query = "
    SELECT id, title, start_datetime, end_datetime
    FROM events
    WHERE school_id = :school_id
    ORDER BY start_datetime ASC
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':school_id', $school_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$events = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start_datetime'],
        'end' => $row['end_datetime']
    ];
}

echo json_encode($events);
?>