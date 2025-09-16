<?php
// assignments_section.php

// Automatically delete expired assignments
$conn->exec("DELETE FROM assignments WHERE due_date < datetime('now')");

// Fetch student's class
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT grade_level FROM students WHERE id = :student_id");
$stmt->bindValue(':student_id', $student_id);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

if (!$student) {
    echo "<p>Student not found.</p>";
    return;
}

$grade_level = $student['grade_level'];
// First delete related records
$conn->exec("
    DELETE FROM submissions
    WHERE assignment_id IN (
        SELECT id FROM assignments WHERE due_date < datetime('now')
    )
");

// Then delete expired assignments
$conn->exec("DELETE FROM assignments WHERE due_date < datetime('now')");
// Fetch assignments for the student's class
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.instructions, a.due_date, a.attachment, c.class_name
    FROM assignments a
    JOIN classes c ON a.class_id = c.id
    WHERE c.class_name = :grade_level
    ORDER BY a.due_date ASC
");
$stmt->bindValue(':grade_level', $grade_level);
$result = $stmt->execute();

// Function to format date
function humanReadableDate($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
    $yesterday = strtotime('-1 day', $today);

    if (date('Y-m-d', $timestamp) == date('Y-m-d', $today)) {
        return 'Today at ' . date('g:i A', $timestamp);
    } elseif (date('Y-m-d', $timestamp) == date('Y-m-d', $yesterday)) {
        return 'Yesterday at ' . date('g:i A', $timestamp);
    } elseif ($timestamp >= strtotime('-6 days', $today)) {
        return date('l', $timestamp) . ' at ' . date('g:i A', $timestamp);
    } else {
        return date('M j, Y \a\t g:i A', $timestamp);
    }
}
?>

<div class="assignments-section">
    <h2>Your Assignments (<?php echo htmlspecialchars($grade_level); ?>)</h2>
    <div class="assignments-list">
        <?php
        $hasAssignments = false;
        while ($assignment = $result->fetchArray(SQLITE3_ASSOC)) {
            $hasAssignments = true;
        ?>
            <div class="assignment">
                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                <p><strong>Instructions:</strong> <?php echo nl2br(htmlspecialchars($assignment['instructions'])); ?></p>
                <p class="due"><strong>Due:</strong> <?php echo htmlspecialchars(humanReadableDate($assignment['due_date'])); ?></p>
                <?php if (!empty($assignment['attachment'])): ?>
                    <div class="attachment">
                        <strong>Attachment:</strong>
                        <a href="<?php echo htmlspecialchars($assignment['attachment']); ?>" target="_blank">Download</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php
        }
        if (!$hasAssignments) {
            echo "<p>No assignments available for your class at the moment.</p>";
        }
        ?>
    </div>
</div>

<style>
.assignments-section {
    margin-top: 20px;
}
.assignments-list {
    max-height: 400px;
    overflow-y: auto;
    background: #f9f9f9;
    border: 1px solid #ccc;
    padding: 10px;
}
.assignment {
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #fff;
}
.assignment h3 { margin: 0; }
.due { font-weight: bold; color: #d9534f; }
.attachment { margin-top: 8px; }
</style>