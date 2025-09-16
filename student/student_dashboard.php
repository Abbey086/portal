<?php
session_start();
require 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: students_login.php");
    exit;
}
$student_name = $_SESSION['student_name'];

$sts = $conn->prepare("SELECT COUNT(*) as count FROM assignments WHERE school = :si AND class_id= :ci");
$sts->bindValue(":si", $_SESSION["school_id"],SQLITE3_TEXT);
$sts->bindValue(":ci", $_SESSION['grade_level'] ,SQLITE3_TEXT);
$result = $sts->execute();
$result = $result->fetchArray(SQLITE3_ASSOC);
$assigns  = $result["count"];

$sts = $conn->prepare("SELECT COUNT(*) as count FROM bookings as b JOIN office_hours as o, teachers as t ON b.office_hour_id != o.id AND t.id = o.teacher_id AND t.school_id = :si WHERE o.start_datetime > datetime('now')");
$sts->bindValue(":si", $_SESSION["school_id"],SQLITE3_TEXT);
$result = $sts->execute();
 $result = $result->fetchArray(SQLITE3_ASSOC);
 $bookings  = $result["count"];
 
 
$sts = $conn->prepare("SELECT COUNT(*) as count FROM notifications 
WHERE school_id = :si
AND (student_id = :sti OR class_id = :ci)
");
$sts->bindValue(":si", $_SESSION["school_id"],SQLITE3_TEXT);
$sts->bindValue(":ci", $_SESSION["grade_level"],SQLITE3_TEXT);
$sts->bindValue(":sti", $_SESSION["student_id"],SQLITE3_TEXT);
$result = $sts->execute();
 $result = $result->fetchArray(SQLITE3_ASSOC);
 $notfs  = $result["count"];
 
 
$sts = $conn->prepare("SELECT COUNT(*) as count FROM teacher_questions WHERE student_id = :si AND answer != NULL");
$sts->bindValue(":si", $_SESSION["student_id"],SQLITE3_TEXT);
$result = $sts->execute();
 $result = $result->fetchArray(SQLITE3_ASSOC);
 $qr  = $result["count"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Portal Dashboard</title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
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
           <div class="p-4 md:w-3/4 md:relative md:left-1/4 md:px-8 main-content">
            <div class="top-bar fixed top-0 bg-teal-600 left-0 max-w-screen w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Dashboard</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>

            <div class="welcome-section py-7 mt-[55px] px-2 -z-10 rounded-lg shadow-lg border-teal-600 border relative w-full ">
                <h2 class="text-2xl w-4/6 font-bold ">Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?>!</h2>
                <p class="text-sm w-4/6 text-gray-800">Here's your overview for today</p>
                <img class="absolute -right-3 -top-6 w-5/12 md:w-3/12" src="../global/dd6.png">
            </div>
            <style>
              .stat-card{
                transition: background 400ms ease-in-out;
              }
              .stat-card:hover p,h3{
                transition: color 400ms ease-in-out;
                color: white;
              }
              .sidebar{
                transition: left 400ms ease-in-out;
                
              }
            </style>
            <h2 class="font-bold pt-6 text-xl ">Insights</h2>
            <div class="pb-5 gap-1.5 grid grid-cols-2 grid-rows-2 md:grid-rows-1 md:grid-cols-4">
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Free Office Spots</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">                <?php echo $bookings;?>
</p></div>
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Total Assignments</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">                <?php echo $assigns;?>
</p></div>
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Questions Replies</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">                <?php echo $qr;?></p></div>
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Notifications</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">                <?php echo $notfs;?></p></div>
            </div>
                    <h2 class="font-bold text-xl ">Timetable (<?php echo $_SESSION["grade_level"];?>)</h2>
                    <iframe class="w-full h-[240px]" src="tttt.php">
                      
                    </iframe>

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
</body>
</html>