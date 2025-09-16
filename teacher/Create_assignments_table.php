<?php
$db = new SQLite3('school_portal.db');

$query = "
CREATE TABLE IF NOT EXISTS assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    class TEXT NOT NULL,
    due_date TEXT,
    posted_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
";

if ($db->exec($query)) {
    echo "Assignments table created successfully.";
} else {
    echo "Failed to create table: " . $db->lastErrorMsg();
}

$db->close();
?>