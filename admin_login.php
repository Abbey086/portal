<?php
session_start();
require_once 'config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare and bind using SQLite3 syntax
    $stmt = $conn->prepare("SELECT * FROM adminz WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $admin = $result->fetchArray(SQLITE3_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $admin['name'];
        header("Location: admin_schools.php");
        exit;
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="bg-light">
  <div class="container">
    <div class="row justify-content-center mt-5">
      <div class="col-md-6 col-lg-4">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="card-title text-center mb-4">Admin Login</h3>

            <?php if ($error_message): ?>
              <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
              <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required/>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required/>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
              </div>
            </form>

            <div class="text-center mt-3">
              <a href="admin_signup.php">Create an admin account</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>