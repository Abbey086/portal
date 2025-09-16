<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['school_logged_in']) || !$_SESSION['school_logged_in']) {
    header("Location: login.php");
    exit;
}

$schoolId = $_SESSION['school_id'];
$message = '';

// Fetch all classes for this school
$classesStmt = $conn->prepare("SELECT id, class_name FROM classes WHERE school_id = :school_id");
$classesStmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
$classesRes = $classesStmt->execute();

$classes = [];
while ($row = $classesRes->fetchArray(SQLITE3_ASSOC)) {
    $classes[$row['id']] = $row['class_name'];
}

// Fetch all teachers for this school
$teachersStmt = $conn->prepare("SELECT id, full_name FROM teachers WHERE school_id = :school_id");
$teachersStmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
$teachersRes = $teachersStmt->execute();

$teachers = [];
while ($row = $teachersRes->fetchArray(SQLITE3_ASSOC)) {
    $teachers[$row['id']] = $row['full_name'];
}

// Handle Add Entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $class_id = intval($_POST['class_id'] ?? 0);
    $day = $_POST['day'] ?? '';
    $period = intval($_POST['period'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $teacher_id = intval($_POST['teacher_id'] ?? 0) ?: null;

    if ($class_id && $day && $period && $subject) {
        $stmt = $conn->prepare("INSERT INTO timetable (school_id, class_id, day, period, subject, teacher_id) 
                                VALUES (:school_id, :class_id, :day, :period, :subject, :teacher_id)");
        $stmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
        $stmt->bindValue(':class_id', $class_id, SQLITE3_INTEGER);
        $stmt->bindValue(':day', $day, SQLITE3_TEXT);
        $stmt->bindValue(':period', $period, SQLITE3_INTEGER);
        $stmt->bindValue(':subject', $subject, SQLITE3_TEXT);
        if ($teacher_id) {
            $stmt->bindValue(':teacher_id', $teacher_id, SQLITE3_INTEGER);
        } else {
            $stmt->bindValue(':teacher_id', null, SQLITE3_NULL);
        }
        if ($stmt->execute()) {
            $message = "Entry added.";
        } else {
            $message = "Failed to add entry.";
        }
    } else {
        $message = "Please fill all required fields.";
    }
}

// Handle Delete Entry
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delStmt = $conn->prepare("DELETE FROM timetable WHERE id = :id AND school_id = :school_id");
    $delStmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $delStmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
    $delStmt->execute();
    header("Location: admin_timetable.php");
    exit;
}

// Fetch timetable entries for this school, ordered
$timetableStmt = $conn->prepare("SELECT t.id, t.day, t.period, t.subject, t.teacher_id, t.class_id, 
    c.class_name, 
    te.full_name AS teacher_name
FROM timetable t
JOIN classes c ON t.class_id = c.id
LEFT JOIN teachers te ON t.teacher_id = te.id
WHERE t.school_id = :school_id
ORDER BY 
    CASE t.day 
      WHEN 'Monday' THEN 1
      WHEN 'Tuesday' THEN 2
      WHEN 'Wednesday' THEN 3
      WHEN 'Thursday' THEN 4
      WHEN 'Friday' THEN 5
      WHEN 'Saturday' THEN 6
      WHEN 'Sunday' THEN 7
      ELSE 8
    END,
    t.period ASC,
    c.class_name ASC
");
$timetableStmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
$timetableRes = $timetableStmt->execute();

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Timetable Management</title>
<style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #f4f4f4; }
form { margin-bottom: 20px; }
input, select { padding: 6px; margin-right: 10px; }
.message { color: green; margin-bottom: 15px; }
.delete-link { color: red; text-decoration: none; }
</style>
</head>
<body>
<h1>Manage Timetable</h1>

<?php if ($message): ?>
<p class="message"><?=htmlspecialchars($message)?></p>
<?php endif; ?>

<form method="POST">
    <label>Class:
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php foreach ($classes as $id => $name): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Day:
        <select name="day" required>
            <option value="">Select Day</option>
            <?php foreach ($days as $d): ?>
                <option value="<?= $d ?>"><?= $d ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>Period:
        <input type="number" name="period" min="1" max="12" required />
    </label>

    <label>Subject:
        <input type="text" name="subject" required />
    </label>

    <label>Teacher:
        <select name="teacher_id">
            <option value="">-- None --</option>
            <?php foreach ($teachers as $id => $name): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <button type="submit" name="add_entry">Add Entry</button>
</form>

<table>
    <thead>
        <tr>
            <th>Class</th>
            <th>Day</th>
            <th>Period</th>
            <th>Subject</th>
            <th>Teacher</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $timetableRes->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?= htmlspecialchars($row['class_name']) ?></td>
            <td><?= htmlspecialchars($row['day']) ?></td>
            <td><?= htmlspecialchars($row['period']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['teacher_name'] ?? '') ?></td>
            <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this entry?');" class="delete-link">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>