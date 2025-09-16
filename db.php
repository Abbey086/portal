<?php
session_start();
$dbPath = 'school_portal.db';
$conn = new SQLite3($dbPath);


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: admin_login.php");
  exit;
}

function displayTable($conn, $table) {
    $result = $conn->query("SELECT * FROM $table");
    if (!$result) {
        echo "<h3>Error fetching data from $table</h3>";
        return;
    }

    echo "<h2>Table: $table</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>";
    for ($i = 0; $i < $result->numColumns(); $i++) {
        echo "<th>" . htmlspecialchars($result->columnName($i)) . "</th>";
    }
    echo "</tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>" . htmlspecialchars($val) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table><br><br>";
}
$tables = [
    'schools',
    'suggestions',
    'teachers',
    'students',
    'info_posts',
    'info_replies',
    'info_likes',
    'announcements',
    'announcement_recipients',
    'assignments',
    'submissions',
    'teacher_questions',
    'notes',
    'notifications',
    'office_hours',
    'bookings',
    'settings',
    'timetables'
];

echo "<!DOCTYPE html><html><head><title>School Portal DB Viewer</title></head><body style='font-family: Arial, sans-serif'>";
echo "<h1>Database: school_portal.db</h1>";

foreach ($tables as $table) {
    displayTable($conn, $table);
}

echo "</body></html>";
?>