<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION["student_id"])){
  die("Access Denied");
}
function humanReadableDate($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
    $yesterday = strtotime('-1 day', $today);

    if (date('Y-m-d', $timestamp) === date('Y-m-d', $today)) {
        return 'Today at ' . date('g:i A', $timestamp);
    } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', $yesterday)) {
        return 'Yesterday at ' . date('g:i A', $timestamp);
    } elseif ($timestamp >= strtotime('-6 days', $today)) {
        return date('l', $timestamp) . ' at ' . date('g:i A', $timestamp);
    } else {
        return date('M j, Y \a\t g:i A', $timestamp);
    }
}

$school_id = $_SESSION['school_id'] ?? '';
$student_id = $_SESSION['student_id'] ?? '';
$stmt = $conn->prepare("
SELECT * FROM notifications 
WHERE school_id = :sid 
AND (student_id = :stid OR class_id = :cid)
ORDER BY id DESC");
$stmt->bindValue(":sid", $school_id);
$stmt->bindValue(":cid", $_SESSION["grade_level"]);
$stmt->bindValue(":stid", $student_id);
$r = $stmt->execute();
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Notifications</title>
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <meta charset="UTF-8">
    
    <style>
    .ri-heart-fill{
      color:rgb(20,184,166);
    }
    </style>
    
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl"></span>Notifications</h2>
              

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

  <h2 class="my-10 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd10.png" class="-top-1 -left-8 w-2/5 object-cover absolute object-center z-10">Notifications
  </h2>
      <?php if (empty($r->fetchArray(SQLITE3_ASSOC))): ?>
<i class="ri-cloud-off-fill text-9xl block text-center mt-20 text-gray-700 "></i>
        <p class="text-center text-teal-700 mb-20">No notifications yet at the moment.</p>    <?php else: ?>
<?php 
while ($row = $r->fetchArray(SQLITE3_ASSOC)){
  
    $content = htmlspecialchars($row['content']);
  
    echo "<div class='border rounded border-gray-300 p-2  m-3 post'>
        <p>$content</p>
        <p class='flex items-center gap-1.5'>";
$st = $row["student_id"]?"Sent Privately to You":("Sent to ".$row["class_id"]);
    echo "<span class='text-teal-600 text-sm'>$st</span>";
    $dt= humanReadableDate($row['created_at']);
echo " • <span class='text-teal-600 text-sm'>$dt</span>";
    echo "</p>";
    echo "</div>";
}

?>



<?php endif;?>

</script>
 </div>
 </body>
</html>