<?php
session_start();
require 'config.php';

if (!isset($_SESSION['student_id'])) {
   header("Location: students_login.php");
   exit;
}

$school_id = $_SESSION['school_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);

    if ($content) {
        // Insert into Suggestions
        $stmt = $conn->prepare("INSERT INTO suggestions (school_id, content) VALUES (:school,  :content)");
      $stmt->bindValue(':school', $school_id, SQLITE3_TEXT);
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->execute();

        $success = "Suggestion sent.";
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suggestions</title>
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Suggest</h2>
              

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

<h2 class="my-10 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd6.png" class="-top-1 -left-8 w-2/5 md:w-2/12 md:top-3 object-cover absolute object-center z-10">Create Suggestion</h2>

<form method="post" class="px-3 py-12 md:py-3">
    <label class="text-gray-400 uppercase text-sm" >Your suggestion:</label>

<?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <textarea name="content" class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded "  rows="6" cols="40" required></textarea><br><br>
    <button class="bg-teal-600 text-white py-1.5 px-2.5 rounded" type="submit">Post Suggestion</button>
</form>

<p class="underline text-center text-teal-600"><a href="student_dashboard.php">Back to Dashboard</a></p>

</div>
</body>
</html>