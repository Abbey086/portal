<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$email || !$password) {
        $error = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $teacher = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($teacher && password_verify($password, $teacher['password'])) {
          if($teacher["status"]=="approved"){
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['teacher_name'] = $teacher['full_name'];
            $_SESSION['school_id'] = $teacher['school_id'];
            header("Location: dashboard.php");
            exit;
          }else{
            
          }
            $error = "Unapproved account. Contact your school administrator to complete approval.";
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <title>Login - SchoolHub</title>
</head>
<body class="md:grid md:place-items-center md:h-[80vh]">
    <div class="p-4 md:py-6 login-container md:rounded-xl md:shadow-lg md:bg-white md:w-1/2 ">
        <div class="login-header">
            <h1 class="text-teal-600 text-center my-9  text-4xl font-bold ">Teacher Login</h1>
        </div>

        <?php if ($error): ?>
            <div class="error bg-red-100 border rounded p-1.5 border-red-600 text-red-600 "><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group my-4">
                <label class="text-sm text-gray-400 uppercase" for="email">Email</label>
                <input class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded"  type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group my-4">
                <label for="password" class="text-sm text-gray-400 uppercase">Password</label>
                <input type="password" class="block border w-full border-teal-600 outline-teal-600 p-1.5 rounded" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary bg-teal-600 text-white rounded py-2 px-3 ">Login</button>
        </form>

        <div class="register-link mt-4">
            <p>Don't have an account? <a href="teacher_registration.php" class="text-teal-600 underline">Create New Account</a></p>
        </div>
    </div>
</body>
</html>