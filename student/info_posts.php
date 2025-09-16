<?php
require_once 'config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
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

$sid = $_SESSION['student_id'];
$posts = $conn->query("SELECT p.id, p.content, p.created_at, s.school_name
    FROM info_posts p
    LEFT JOIN schools s ON p.school_id = s.school_id
    ORDER BY p.created_at DESC");
echo('
<!DOCTYPE html>
<html>
  <head>
    <title>Information Center</title>
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <style>
    .ri-heart-fill{
      color:rgb(20,184,166);
    }
    </style>
    
  </head>
  <body>
  
 ');
 ?>
   
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Information Posts</h2>
              

                <form action="logout.php" method="POST" class="flex items-center">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 h-full">Logout</button>
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

  <h2 class="my-10 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd10.png" class="-top-1 md:w-2/12 md:top-3 -left-8 w-2/5 object-cover absolute object-center z-10">Information Posts
  </h2>
<?php
  if (empty($posts->fetchArray(SQLITE3_ASSOC))){
echo "<i class='ri-cloud-off-fill text-9xl block text-center mt-20 text-gray-700'></i><p class='text-center text-teal-700 mb-20'>No available posts found at the moment.</p>";
    
  } 
else{
while ($row = $posts->fetchArray(SQLITE3_ASSOC)) {
    $postId = $row['id'];
    $schoolName = htmlspecialchars($row['school_name']);
    $content = htmlspecialchars($row['content']);
        $likedA = $conn->querySingle("SELECT COUNT(*) FROM info_likes WHERE post_id = $postId AND student_id = '$sid'");
        
        if($likedA==0){
          $lof = "ri-heart-line";
        }else{
          $lof = "ri-heart-fill";
          
        }

    echo "<div class='border rounded border-gray-300 p-2  m-3 post'>
        <div class='text-gray-400 uppercase text-sm'>$schoolName</div>
        <p>$content</p>
        <p class='flex items-center gap-1.5'><button class='$lof text-2xl' onclick='likePost($postId,this)'></button>";

    $likeCount = $conn->querySingle("SELECT COUNT(*) FROM info_likes WHERE post_id = $postId");
    echo "<span class='likes'>$likeCount like(s)</span>";
    $dt= humanReadableDate($row['created_at']);
echo " • <span class='text-teal-600 text-sm'>$dt</span>";
    echo "</p>";
    echo "</div>";
}
}
?>
<script>
  async function likePost(id,btn){
    const like = await fetch("like.php?post_id="+id);
    const result = await like.json();
    if(result == "liked"){
      
    }
    else {
      btn.classList.remove("ri-heart-line");
      btn.classList.add("ri-heart-fill");
      btn.parentElement.querySelector(".likes").innerHTML = result +" like(s)"
    }
  }
</script>
</div>
 </body>
</html>