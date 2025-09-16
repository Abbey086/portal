<?php
session_start();
if (!isset($_SESSION['registration_success'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
     <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body >
    <div class="success-container md:w-1/2 mx-auto p-3">
          
        <h2 class="text-5xl font-bold text-center mt-7">🥳</h2>
        <h2 class="text-3xl font-bold text-center mb-8">Registration Successful!</h2>
        <p class="text-center px-5 text-sm ">Your school has been successfully registered. Wait for administrator approval. Contact administrator at 256753943599</p>
        <p class="font-bold text-teal-700 mt-8 text-center ">Your School ID is:</p>
        <div class="school-id w-9/12 text-3xl border-4 mx-auto p-6 border-dotted text-teal-700 text-center  border-teal-700">
            <?php echo htmlspecialchars($_SESSION['school_id']); ?>
          
        </div>
        <p class="italic mt-4">Please note this ID for future reference.</p>
        <p><a href="login.php" class="text-teal-600 underline">Proceed to Login</a></p>
    </div>
</body>
</html>

<?php
// Clear the session variables
unset($_SESSION['registration_success']);
unset($_SESSION['school_id']);
?>