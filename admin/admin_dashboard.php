<?php
session_start();

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$schoolid = $_SESSION["school_id"];

$sts = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE school_id = :si");
$sts->bindValue(":si",$schoolid,SQLITE3_TEXT);
$result = $sts->execute();
$result = $result->fetchArray(SQLITE3_ASSOC);
$students  = $result["count"];

$sts = $conn->prepare("SELECT COUNT(*) as count FROM teachers WHERE school_id = :si");
$sts->bindValue(":si", $schoolid,SQLITE3_TEXT);
$result = $sts->execute();
$result = $result->fetchArray(SQLITE3_ASSOC);
$teachers  = $result["count"];

$classes = $conn->query("SELECT classes FROM schools WHERE school_id = '$schoolid'");
$ro = $classes->fetchArray(SQLITE3_ASSOC);
$ro = $ro["classes"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body>
    <div class="dashboard p-3">
        <div class="sidebar fixed top-0 w-1/2 bg-teal-800 h-screen md:w-1/4 md:left-0 -left-1/2">
          <div onclick="hideMenu()" class="cover hidden md:hidden bg-black/50 fixed top-0 right-0 w-1/2 h-screen"></div>
            <div class="sidebar-header border-b border-gray-300 flex flex-col justify-center text-center text-white items-center w-full gap-0.5">
              <img src="<?php
               $q = $conn->prepare("SELECT logo_url FROM schools WHERE school_id = :school_id");
$q->bindValue(":school_id",$_SESSION["school_id"],SQLITE3_TEXT);
$lg = $q->execute();
$lg = $lg->fetchArray(SQLITE3_ASSOC);
$logo_url = $lg["logo_url"];
               if(strlen($logo_url)>5){
                 echo($logo_url);
               }else{
                 echo( "../default.png");
               }
               ?>" class="bg-white object-cover object-center rounded-2xl aspect-square mt-16 w-1/2"/>
                <h3 class="font-bold "><?php echo htmlspecialchars($_SESSION['school_name']); ?></h3>
                <p class="opacity-80 mb-3 text-sm">School ID: <?php echo htmlspecialchars($_SESSION['school_id']); ?></p>
            </div>
            <nav class="flex pt-4 text-white flex-col gap-3 px-2 ">
                <a href="admin_dashboard.php" class="menu-item rounded flex items-center"><span class="ri-dashboard-fill text-2xl mr-1"></span>Dashboard</a>
                <a href="view_students.php" class="menu-item rounded flex items-center"><span class="ri-user-fill text-2xl mr-1"></span>All Students</a>
                <a href="view_teachers.php" class="menu-item rounded flex items-center"><span class="ri-user-star-fill text-2xl mr-1"></span>Teachers</a>
                <a href="suggestions.php" class="menu-item rounded flex items-center"><span class="ri-login-box-fill text-2xl mr-1"></span>Suggestion Box</a>
                <a href="info_center.php" class="menu-item rounded flex items-center"><span class="ri-information-fill text-2xl mr-1"></span>Info center</a>
                <a href="send_notification.php" class="menu-item rounded flex items-center"><span class="ri-notification-3-fill text-2xl mr-1"></span>Notifications</a>
                <a href="settings.php" class="menu-item rounded flex items-center"><span class="ri-settings-fill text-2xl mr-1"></span>Settings</a>
               <a href="create_timetable.php" class="menu-item rounded flex items-center"><span class="ri-calendar-schedule-fill text-2xl mr-1"></span>Timetable</a>
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
            </script>
            </nav>
        </div>
        <div class="md:p-3 md:w-3/4 md:relative md:left-1/4 p-3 mt-12 main-content">
            <div class="top-bar fixed top-0 bg-teal-600 left-0 w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Dashboard</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>

            <div class="welcome-section py-7 mt-[55px] px-2 -z-10 rounded-lg shadow-lg border-teal-600 border relative w-full ">
                <h2 class="text-2xl w-4/6 font-bold ">Welcome, <?php echo htmlspecialchars($_SESSION['school_name']); ?>!</h2>
                <p class="text-sm w-4/6 text-gray-800">Here's your overview for today</p>
                <img class="absolute -right-3 -top-6 w-5/12 md:w-3/12" src="../global/dd.png">
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
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Total Teachers</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">                <?php echo $teachers;?>
</p></div>
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Total Students</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">                <?php echo $students;?>
</p></div>
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Total Classes</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400" id="classes">0</p></div>
                <div class="stat-card rounded border-2 border-teal-600 hover:bg-teal-600 shadow py-4 px-2 "><h3 class="text-center text-xs text-gray-800 uppercase">Active Courses</h3><p class="text-teal-600 text-center  text-4xl font-bold transition transition-all transition-400">0</p></div>
            </div>
        </div>
    </div>
    <script>
    
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
</body>
</html>