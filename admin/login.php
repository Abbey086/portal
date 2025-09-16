<?php
session_start();
require_once 'config.php';  // Make sure $conn = new SQLite3('path_to_db') is in here

$message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schoolId = trim($_POST['schoolId'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($schoolId === '' || $password === '') {
        $message = "Please enter both School ID and Password.";
    } else {
        // Prepare and execute query safely
        $stmt = $conn->prepare("SELECT * FROM schools WHERE school_id = :school_id OR email = :email LIMIT 1");
        $stmt->bindValue(':school_id', $schoolId, SQLITE3_TEXT);
        $stmt->bindValue(':email', $schoolId, SQLITE3_TEXT);
        $result = $stmt->execute();

        $school = $result->fetchArray(SQLITE3_ASSOC);

        if ($school) {
            // Verify password hash
            if (password_verify($password, $school['password'])) {
                // Check if account is active/approved
                if (in_array($school['status'], ['approved', 'active'])) {
                    $_SESSION['school_logged_in'] = true;
                    $_SESSION['school_id'] = $school['school_id'];
                    $_SESSION['school_name'] = $school['school_name'];
                    header("Location: admin_dashboard.php");
                    exit;
                } else {
                    $message = "Your account is not active. Please wait for admin approval.";
                }
            } else {
                $message = "Invalid School ID or Password.";
            }
        } else {
            $message = "Invalid School ID or Password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>School Portal Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body>
    <form class="login-form md:w-1/2 md:mx-auto p-3" method="POST" action="">
        <img src="../global/dd4.png" class="fill-teal-600 w-2/6 mx-auto mt-6">
        <h2 class="text-3xl font-bold text-center mb-12">School Log In </h2>
        
        <?php if ($message): ?>
            <div class="error-message py-2 fixed top-0 left-0 bg-[#f00] px-3 text-white w-full"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="schoolId" class="text-sm font-semibold text-gray-700 block">School ID/Email</label>
            <input class="w-full p-2 border outline-teal-600 rounded mb-3"  type="text" id="schoolId" name="schoolId" required value="<?php echo htmlspecialchars($_POST['schoolId'] ?? '') ?>" />
        </div>

        <div class="form-group">
            <label for="password" class="text-sm font-semibold text-gray-700 block">Password</label>
            <input type="password" id="password" name="password" required class="w-full p-2 border outline-teal-600 rounded mb-3" />
        </div>
        <p class="text-sm mt-4 text-right">Don't have an account? <a href="signup.html" class="text-teal-600 underline">Sign Up</a></p>

        <button type="submit" class="bg-teal-600 text-white rounded font-bold text-sm py-2 px-4" >Login</button>
        <p class="text-sm mt-4 text-center">Forgot password? <a href="" class="text-teal-600 underline">Contact system admin</a> for recovery.</p>

        
    </form>
    <script>
      var mesage = document.querySelector(".error-message");
      setTimeout(()=>{
        mesage.style.display = "none"
      },3500)
    </script>
</body>
</html>