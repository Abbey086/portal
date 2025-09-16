<?php
require 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];


$classes = $conn->query("SELECT classes FROM schools WHERE school_id = '$school_id'");
$all_classes = [];

$ro = $classes->fetchArray(SQLITE3_ASSOC);
$ro = $ro["classes"];
// Fetch current student info
$stmt = $conn->prepare("SELECT full_name, email, grade_level, password FROM students WHERE id = :id AND school_id = :school_id");
$stmt->bindValue(':id', $student_id, SQLITE3_INTEGER);
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

if (!$student) {
    die("Student not found.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $grade_level = trim($_POST['grade_level']);
    $password = $_POST['password'];
    $oldpassword = $_POST['oldpassword'];
    $password_confirm = $_POST['password_confirm'];

    // Basic validation
    if (empty($full_name) || empty($email) || empty($grade_level)) {
        $message = "Name, Email, and Class cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif ($password && $password !== $password_confirm) {
        $message = "Passwords do not match.";
    } else {
        // Check if email is unique (except current user)

        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE email = :email AND id != :id");
        $checkStmt->bindValue(':email', $email, SQLITE3_TEXT);
        $checkStmt->bindValue(':id', $student_id, SQLITE3_INTEGER);
        $count = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC)['count'];

        if ($count > 0) {
            $message = "Email already in use by another student.";
        } else {
            // Update student data
            if ($password) {
              if(!password_verify($oldpassword,$student["password"])){
          $message= "Wrong old password";
        } else{
          $message = "Success";
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE students SET full_name = :full_name, email = :email, grade_level = :grade_level, password = :password WHERE id = :id");
                $update->bindValue(':password', $hashed_password, SQLITE3_TEXT);}
            } 
          
                $update = $conn->prepare("UPDATE students SET full_name = :full_name, email = :email, grade_level = :grade_level WHERE id = :id");
            
            $update->bindValue(':full_name', $full_name, SQLITE3_TEXT);
            $update->bindValue(':email', $email, SQLITE3_TEXT);
            $update->bindValue(':grade_level', $grade_level, SQLITE3_TEXT);
            $update->bindValue(':id', $student_id, SQLITE3_INTEGER);

            if ($update->execute()) {
                $message = "Profile updated successfully.";
                // Update session info
                $_SESSION['student_name'] = $full_name;
                $_SESSION['grade_level'] = $grade_level;
            } else {
                $message = "Update failed, please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <meta charset="UTF-8">
</head>
<body>
  
  
   <div class="sidebar fixed pt-14 top-0 w-1/2 bg-teal-800 h-screen overflow-y-scroll md:left-0 md:w-1/4 -left-1/2">
          <div onclick="hideMenu()" class="cover hidden bg-black/50 md:hidden fixed top-0 right-0 w-1/2 h-screen"></div>
            <div class="sidebar-header border-b border-gray-300 flex flex-col justify-center text-center text-white items-center w-full gap-0.5">
              
                <h3 class="font-bold "><?php echo htmlspecialchars($_SESSION['teacher_name']); ?></h3>
                <p class="font-semibold text-sm">Hello <?php echo htmlspecialchars($_SESSION['student_name']); ?></p>
                <p class="opacity-80 mb-3 text-sm">School ID: <?php echo htmlspecialchars($_SESSION['school_id']); ?></p>
            </div>
            <nav class="flex pt-4 text-white flex-col gap-3 px-2 overflow-y-scroll">
              <a href="student_dashboard.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-dashboard-fill"></span>
Dashboard
</a>


<a href="view_announcements.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-megaphone-fill"></span>
Announcements
</a>


<a href="view_assignments.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-sticky-note-fill "></span>
Assignments
</a>


<a href="ask_the_teacher.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-question-fill "></span>
Ask a Teacher
</a>


<a href="book_office_hours.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-calendar-check-fill"></span>
Book Office Hours
</a>


<a href="download_notes.php" class="menu-item rounded flex items-center">
<span class="ri-file-2-fill text-2xl mr-1 "></span>
Notes
</a>

 <a href="view_timetable.php" class="menu-item rounded flex items-center">
<span class="text-2xl ri-calendar-schedule-fill mr-1"></span>
Timetable
</a>

<a href="info_posts.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-information-fill">️</span>
Information Center
</a>


<a href="fetch_notification.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-notification-3-fill"></span>
Notifications
</a>


<a href="suggestions.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-login-box-fill"></span>
Suggestions Box
</a>

<a href="edit_profile.php" class="menu-item rounded flex items-center">
<span class="text-2xl mr-1 ri-settings-fill "></span>
Edit Profile
</a>

            </nav>
        </div>
           <div class="md:w-3/4 md:relative md:left-1/4 md:px-8 pt-8 main-content">
            <div class="top-bar fixed top-0 bg-teal-600 left-0 max-w-screen w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl  md:hidden"></span>Edit Profile</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
                
          <script>
    function activate(){
              var path = location.pathname;
              var links = document.querySelectorAll(".menu-item");
              for(i=0;i<links.length;i++){
                if(links[i].href.endsWith(path)){
                  links[i].classList.add("bg-white");
                  links[i].classList.add("text-teal-600");
                }
              }
      }
      setTimeout(()=>{
        activate();
      },500)
      function showMenu(){
        var cover =  document.querySelector(".cover")
        var btn =  document.querySelector(".top-bar h2 span")
        var sidebar =  document.querySelector(".sidebar")
        setTimeout(()=>{cover.classList.remove("hidden")},400);
        btn.classList.remove("ri-menu-2-line");
        document.querySelector(".top-bar h2 ").setAttribute("onclick","hideMenu()")
        btn.classList.add("ri-close-line")
        sidebar.classList.remove("-left-1/2")
        sidebar.classList.add("left-0")
      }
      function hideMenu(){
        var cover =  document.querySelector(".cover")
        var btn =  document.querySelector(".ri-close-line")
        var sidebar =  document.querySelector(".sidebar")

        cover.classList.add("hidden");
        btn.classList.add("ri-menu-2-line");
        document.querySelector(".top-bar h2").setAttribute("onclick","showMenu()")
        btn.classList.remove("ri-close-line")
        sidebar.classList.add("-left-1/2")
        sidebar.classList.remove("left-0")
      }
      var classes = [<?php echo $ro;?>];
  var slt = document.getElementById("classes");
  slt.innerText = classes.length
    </script>
            </div>

  <h2 class="my-10 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd12.png" class="-top-1 md:w-2/12 md:top-3 -left-8 w-2/5 object-cover absolute object-center z-10">Edit Profile</h2>

    <?php if ($message): ?>
        <p class="text-teal-600"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <form method="POST" class="p-3 md:px-16" action="">
        <label class="text-gray-400 uppercase text-sm" >Full Name:</label><br>
        <input type="text" class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required><br><br>

        <label class="text-gray-400 uppercase text-sm" >Email:</label><br>
        <input class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br><br>

        <label class="text-gray-400 uppercase text-sm" >Class (Grade Level):</label><br>
            <select class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  name="grade_level" id="cl" required>
        <option  value="">-- Select Class --</option>
    </select><br><br>

        <br>

        <label class="text-gray-400 uppercase text-sm" >Old Password (leave blank to keep current):</label><br>
        <input class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  type="password" name="oldpassword"><br><br>
        
        <label class="text-gray-400 uppercase text-sm" >New Password (leave blank to keep current):</label><br>
        <input class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  type="password" name="password"><br><br>

        <label class="text-gray-400 uppercase text-sm" >Confirm New Password:</label><br>
        <input type="password" class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  name="password_confirm"><br><br>

        <button class="bg-teal-600 text-white py-1.5 px-2.5 rounded" type="submit">Update Profile</button>
    </form>

    <p class="text-center underline text-teal-600"><a href="student_dashboard.php">Back to Dashboard</a></p>
    
    <script>
  var classes = [<?php echo $ro;?>];
  var aCl = "<?php echo $student["grade_level"];?>";
  var slt = document.getElementById("cl");
  for(i = 0; i< classes.length; i++){
    slt.innerHTML += `<option ${classes[i]==aCl? "selected ":""} value="${classes[i]}">${classes[i]}</option>`;
  
  }
  </script>
  </div>
</body>
</html>