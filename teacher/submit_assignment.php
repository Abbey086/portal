<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new SQLite3('school_portal.db');

    $title = $_POST['title'];
    $description = $_POST['description'];
    $class = $_POST['class'];
    $due_date = $_POST['due_date'];
    $posted_by = $_POST['posted_by'];

    $stmt = $db->prepare("INSERT INTO assignments (title, description, class, due_date, posted_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $title, SQLITE3_TEXT);
    $stmt->bindValue(2, $description, SQLITE3_TEXT);
    $stmt->bindValue(3, $class, SQLITE3_TEXT);
    $stmt->bindValue(4, $due_date, SQLITE3_TEXT);
    $stmt->bindValue(5, $posted_by, SQLITE3_TEXT);

    if ($stmt->execute()) {
        echo "Assignment posted successfully. <a href='view_assignments.php'>View All</a>";
    } else {
        echo "Failed to post assignment.";
    }

    $db->close();
}
?>