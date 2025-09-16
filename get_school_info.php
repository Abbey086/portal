<?php
require_once 'config.php';

// Check if school_id is provided
if (!isset($_GET['school_id']) || empty($_GET['school_id'])) {
    echo "No school ID provided.";
    exit;
}

$schoolId = $_GET['school_id'];

// Fetch school details
$stmt = $conn->prepare("SELECT * FROM schools WHERE school_id = :school_id");
$stmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
$result = $stmt->execute();

$school = $result->fetchArray(SQLITE3_ASSOC);

if ($school):
?>
    <form id="editSchoolForm">
        <div class="mb-2">
            <label class="form-label">School Name</label>
            <input type="text" name="school_name" class="form-control" value="<?php echo htmlspecialchars($school['school_name']); ?>" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($school['email']); ?>" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php if ($school['status'] === 'active') echo 'selected'; ?>>Active</option>
                <option value="pending" <?php if ($school['status'] === 'pending') echo 'selected'; ?>>Pending</option>
            </select>
        </div>
        <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($school['school_id']); ?>">
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-danger" onclick="deleteSchool('<?php echo $school['school_id']; ?>')">Delete</button>
        </div>
    </form>
    <p class="c">
<?php echo "[".$school["classes"]."]";?>
</p>
    <script>
    // Handle edit form
  document.getElementById("c").innerText = document.getElementById("c").innerText[2]
  
    document.getElementById('editSchoolForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('update_school.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            alert(response);
            location.reload();
        })
        .catch(() => alert('Error updating school.'));
    });

    // Handle delete
    function deleteSchool(schoolId) {
        if (confirm("Are you sure you want to delete this school?")) {
            fetch('delete_school.php?school_id=' + encodeURIComponent(schoolId), {
                method: 'GET'
            })
            .then(res => res.text())
            .then(response => {
                alert(response);
                location.reload();
            })
            .catch(() => alert('Error deleting school.'));
        }
    }
    </script>

<?php else: ?>
    <p>School not found.</p>
<?php endif; ?>