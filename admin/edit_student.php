<?php
require 'config.php';
session_start();

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Student ID is required.");
}

// Fetch existing student
$stmt = $conn->prepare("SELECT * FROM students WHERE id = :id AND school_id = :school_id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$stmt->bindValue(':school_id', $_SESSION['school_id'], SQLITE3_TEXT);
$student = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$student) {
    die("Student not found.");
}
$school_id = $_SESSION['school_id'];
$classes = $conn->query("SELECT classes FROM schools WHERE school_id = '$school_id'");
$ro = $classes->fetchArray(SQLITE3_ASSOC);
$ro = $ro["classes"];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $grade = $_POST['grade_level'];

    $update = $conn->prepare("UPDATE students SET full_name = :name, email = :email, grade_level = :grade WHERE id = :id");
    $update->bindValue(':name', $name, SQLITE3_TEXT);
    $update->bindValue(':email', $email, SQLITE3_TEXT);
    $update->bindValue(':grade', $grade, SQLITE3_TEXT);
    $update->bindValue(':id', $id, SQLITE3_INTEGER);
    $update->execute();


    header("Location: view_students.php?msg=updated");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
        <script src="../tw.js"></script>
       <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    <h2 class="text-3xl text-teal-700 text-center font-bold my-9">Edit Student</h2>
    <form method="POST" class="p-3">
        <label >Full Name:<br> <input type="text" name="full_name" class="rounded border-gray-700 border outline-teal-600 block w-full p-2" value="<?= htmlspecialchars($student['full_name']) ?>" required></label><br>
        <label>Email: <br><input class="rounded border-gray-700 border outline-teal-600 block w-full p-2" type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required></label><br>
        <label>Class:<br>
            <select class="rounded border-gray-700 border outline-teal-600 block w-full p-2" name="grade_level" id="cl" required>
             <option>Select class</option>
            </select>
        </label><br>
        <button type="submit" class="py-2 px-3 bg-teal-600 text-white rounded">Save Changes</button>
        <p><a  class="block mt-12 text-teal-600 text-center underline"href="admin_dashboard.php">Back to dashboard.</a></p>
    </form>
    <script>
  var classes = [<?php echo $ro;?>];
  var active = '<?php echo $student['grade_level'] ;?>';
  var slt = document.getElementById("cl");
  for(i = 0; i< classes.length; i++){
    slt.innerHTML += `<option ${active==classes[i]?"selected":""} value="${classes[i]}">${classes[i]}</option>`;
  }
  </script>
</body>
</html>