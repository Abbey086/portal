<?php
require 'config.php';

// Make sure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$school_id = $_SESSION['school_id'];
$student_class = $_SESSION['grade_level'];

// Fetch class ID based on name and school
$stmt = $conn->prepare("SELECT grade_level FROM students WHERE id = :student_id");
$stmt->bindValue(':student_id', $_SESSION['student_id'], SQLITE3_TEXT);
$class_result = $stmt->execute();
$class_data = $class_result->fetchArray(SQLITE3_ASSOC);

if (!$class_data) {
    die("Your class is not set up yet. Contact your school admin.");
}

$class_id = $class_data['grade_level'];

// Fetch announcements for that class
$stmt = $conn->prepare("
    SELECT a.title, a.content, a.created_at, t.full_name AS teacher_name
    FROM announcements a
    JOIN teachers t ON a.teacher_id = t.id
    WHERE a.class_id = :class_id
    ORDER BY a.created_at DESC
");
$stmt->bindValue(':class_id', $class_id, SQLITE3_TEXT);
$result = $stmt->execute();

$announcements = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $announcements[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Class Announcements</title>
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
           <div class="md:w-3/4 md:relative md:left-1/4 md:px-8 pt-8 main-content">
            <div class="top-bar fixed top-0 bg-teal-600 left-0 max-w-screen w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Announcements</h2>
              

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

<h2 class="my-10 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd.png" class="-top-1 -left-8 md:w-2/12 md:top-3 w-2/5 object-cover absolute object-center z-10">Announcements</h2>

    <?php if (empty($announcements)): ?>
    <div class="w-full flex flex-col justify-center items-center gap-4 h-2/4">
       <i class="text-9xl text-gray-400 ri-cloud-off-fill"></i>
        <p class="text-center text-teal-600">No announcements available yet.</p>
    </div>
    <?php else: ?>
        <?php foreach ($announcements as $a): ?>
            <div class="rounded-lg p-2 m-3" style="border:1px solid #ccc; padding:10px; margin-bottom:15px;">
                <h3 class="text-2xl font-bold"><?php echo htmlspecialchars($a['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
                <small class="block text-teal-700 mt-5">Posted by <?php echo htmlspecialchars($a['teacher_name']); ?> • <?php echo hrd($a['created_at']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p class="underline text-center text-teal-600"><a href="student_dashboard.php">Back to Dashboard</a></p>
    

</div>
</body>
</html>