<?php
session_start();
require_once 'config.php';

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: admin_login.php");
  exit;
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $schoolId = $_POST['school_id'];
  $newStatus = $_POST['status'];

  $stmt = $conn->prepare("UPDATE schools SET status = :status WHERE school_id = :school_id");
  $stmt->bindValue(':status', $newStatus, SQLITE3_TEXT);
  $stmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
  $stmt->execute();
}

// Get schools
$query = "SELECT school_id, school_name, email, status, created_at FROM schools ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - School Management</title>
</head>
<body>
<div class="container mt-5">
  <div class="d-flex justify-content-between mb-4">
    <h2>Registered Schools</h2>
    <a href="admin_logout.php" class="btn btn-danger">Logout</a>
  </div>

  <div class="card">
    <div class="card-body">
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>School ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($school = $result->fetchArray(SQLITE3_ASSOC)): ?>
          <tr>
            <td><?php echo htmlspecialchars($school['school_id']); ?></td>
            <td><?php echo htmlspecialchars($school['school_name']); ?></td>
            <td><?php echo htmlspecialchars($school['email']); ?></td>
            <td>
              <span class="badge bg-<?php echo $school['status'] === 'active' ? 'success' : 'warning'; ?>">
                <?php echo ucfirst($school['status']); ?>
              </span>
            </td>
            <td><?php echo date('Y-m-d', strtotime($school['created_at'])); ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="school_id" value="<?php echo $school['school_id']; ?>">
                <input type="hidden" name="status" value="<?php echo $school['status'] === 'active' ? 'pending' : 'active'; ?>">
                <button type="submit" name="update_status" class="btn btn-sm btn-<?php echo $school['status'] === 'active' ? 'warning' : 'success'; ?>">
                  <?php echo $school['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                </button>
              </form>
              <button class="btn btn-sm btn-info" onclick="viewSchoolDetails('<?php echo $school['school_id']; ?>')">View</button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- mdl -->
<div class="mdl fade" id="schoolDetailsmdl" tabindex="-1">
  <div class="mdl-dialog">
    <div class="mdl-content">
      <div class="mdl-header">
        <h5 class="mdl-title">School Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="mdl"></button>
      </div>
      <div class="mdl-body" id="schoolDetailsContent">
        Loading...
      </div>
    </div>
  </div>
</div>

<script>
function viewSchoolDetails(schoolId) {
  fetch('get_school_info.php?school_id=' + encodeURIComponent(schoolId))
    .then(response => response.text())
    .then(data => {
      document.getElementById('schoolDetailsContent').innerHTML = data;
      
    })
    .catch(() => {
      document.getElementById('schoolDetailsContent').innerHTML = 'Failed to load school details.';
    });
}
</script>
</body>
</html>