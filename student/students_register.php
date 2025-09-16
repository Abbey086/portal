<?php
session_start();
require 'config.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = trim($_POST['school_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $grade_level = trim($_POST['grade_level']);

    // Check if school exists and is approved
    $stmt = $conn->prepare("SELECT * FROM schools WHERE school_id = :school_id AND status = 'active'");
    $stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
    $result = $stmt->execute();
    $school = $result->fetchArray(SQLITE3_ASSOC);

    if (!$school) {
        $error = "Invalid or unapproved School ID.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert student
        $stmt = $conn->prepare("INSERT INTO students (school_id, email, password, full_name, grade_level)
                                VALUES (:school_id, :email, :password, :full_name, :grade_level)");
        $stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
        $stmt->bindValue(':full_name', $full_name, SQLITE3_TEXT);
        $stmt->bindValue(':grade_level', $grade_level, SQLITE3_TEXT);

        if ($stmt->execute()) {
            $success = "Registration successful! <a href='students_login.php'>Login now</a>";
        } else {
            $error = "Error: Email is already be used.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>

    <script>
   async function clid(e)  {
     alert("x")
       const f = await fetch("classes.php?s"=e.value)
       const r = await f.json();
       if(r == "404"){
         document.getElementById("se").innerHTML = "School Does Not Exist";
         document.getElementById("se").style.display = "block";
         return 0;
       }
         
       var classes = JSON.stringify(r);
  var slt = document.getElementById("classes");
  for(i = 0; i< classes.length; i++){
    slt.innerHTML += `<option value="${classes[i]}">${classes[i]}</option>`;
  }}
  document.getElementById("x").addEventListener("change",function(){
    clid()
  })
    </script>
</head>
<body>
    
    <form method="POST" class="p-3 md:mx-[28vw]">
                      <img src="../global/dd2.png" class="fill-teal-600 w-1/3 mx-auto md:w-1/4 mt-6">

        <h2 class="text-3xl font-bold text-center mb-12">School Log In </h2>
        <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color:green;"><?php echo $success; ?></p><?php endif; ?>

        <input type="text"name="school_id" placeholder="School ID" id="x" class="w-full p-2 border outline-teal-600 rounded mb-3" required>
        <p id="se" class="text-red-600 "></p>
        <input type="text" class="w-full p-2 border outline-teal-600 rounded mb-3" name="full_name" placeholder="Full Name" required>
        <input type="email" class="w-full p-2 border outline-teal-600 rounded mb-3" name="email" placeholder="Email" required>
        <input type="password" class="w-full p-2 border outline-teal-600 rounded mb-3" name="password" placeholder="Password" required>
        
        <select name="grade_level" class="w-full p-2 border outline-teal-600 hidden rounded mb-3" id="classes"required>
            <option value="">Select Class</option>
            <option value="S1">S1</option>
            <option value="S2">S2</option>
            <option value="S3">S3</option>
            <option value="S4">S4</option>
            <option value="S5">S5</option>
            <option value="S6">S6</option>
        </select>

        <button class="bg-teal-600 text-white rounded font-bold text-sm py-2 px-4"  type="submit">Register</button>

        <p class="text-sm mt-4 text-right">Already have an account? <a class="text-teal-600 underline" href="students_login.php">Login</a></p>
    </form>
</body>
</html>