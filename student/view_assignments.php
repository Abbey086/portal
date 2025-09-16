<?php
require 'config.php';  // Adjust the path as needed

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: students_login.php");
    exit;
}

// Fetch student's class
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT grade_level FROM students WHERE id = :student_id");
$stmt->bindValue(':student_id', $student_id);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

if (!$student) {
    die("Student not found.");
}

$grade_level = $student['grade_level'];
//$grade_level="S.2";
// Fetch assignments for the student's class
$stmt = $conn->prepare("
    SELECT id, title,instructions, due_date, attachment, class_id
    FROM assignments
    WHERE class_id = :grade_level AND school = :sid
    ORDER BY due_date ASC
");
$stmt->bindValue(':grade_level', $grade_level);
$stmt->bindValue(':sid', $_SESSION['school_id']);
$result = $stmt->execute();

// Function to format date
function humanReadableDate($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
//date('Y-m-d g:i A', $timestamp) <= date('Y-m-d g:i A', $today)
//date('Y-m-d', $timestamp) <= date('Y-m-d', $today) && date('g:i A', $timestamp) > date('g:i A', $today)

    $yesterday = strtotime('-1 day', $today);
    if (date('Y-m-d', $timestamp) == date('Y-m-d', $today)) {
        return 'Today at ' . date('g:i A', $timestamp);
    } elseif (date('Y-m-d', $timestamp) == date('Y-m-d', $yesterday)) {
        return 'Yesterday at ' . date('g:i A', $timestamp);
    } elseif ($timestamp >= strtotime('-6 days', $today)) {
        return date('l', $timestamp) . ' at ' . date('g:i A', $timestamp);
    } else {
        return date('M j, Y \a\t g:i A', $timestamp);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['text']) ?? "";
   $filename = "";
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'submissions/' . uniqid() . '.' . $ext;
        
        move_uploaded_file($file['tmp_name'], $filename);
    }
$assid = $_POST["assignment_id"];
      
        // Insert into notes table
        $conn->exec("INSERT INTO submissions (student_id, assignment_id, file_path, text)  VALUES ('$student_id', '$assid','$filename', '$text')");
        $success = "Submission uploaded successfully.";
    
  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <title>View Assignments</title>
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Assignments</h2>
              

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

    <h2 class="my-10 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd12.png" class="-top-1 md:w-2/12 md:top-3 -left-8 w-2/5 object-cover absolute object-center z-10">Assignments for <?php echo htmlspecialchars($grade_level); ?></h2>

    <?php
    $hasAssignments = false;
    while ($assignment = $result->fetchArray(SQLITE3_ASSOC)) {
        $hasAssignments = true;
    ?>
        <div class="assignment p-2.5 m-2 border border-teal-700 rounded ">
            <h3 class="text-2xl font-semibold"><?php echo htmlspecialchars($assignment['title']); ?></h3>
            <p><strong class="text-sm text-teal-600 uppercase font-normal">Instructions:</strong> <?php echo nl2br(htmlspecialchars($assignment['instructions'])); ?></p>
            <p class="due"><strong class="text-sm text-teal-600 uppercase font-normal">Due:</strong> <?php echo htmlspecialchars(humanReadableDate($assignment['due_date'])); ?></p>
            <?php if (!empty($assignment['attachment'])): ?>
                <div class="attachment">
                    <strong class="text-sm text-teal-600 uppercase font-normal">Attachment:</strong>
                    <a class="text-sm bg-teal-600 rounded text-white py-1 px-2.5"href="../teacher/<?php echo htmlspecialchars($assignment['attachment']); ?>" target="_blank">Download</a>
                </div>
            <?php endif; ?>
        <?php 
        $aid = $assignment['id'];
        $cit = $conn->querySingle("SELECT COUNT(*) FROM submissions WHERE assignment_id = $aid AND student_id = $student_id");
       $exists = $cit == 0?false:true;
        $timestamp = strtotime($assignment['due_date']);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
        if (date('Y-m-d g:i A', $timestamp) >= date('Y-m-d g:i A', $today)): ?>
        <?php if($exists): ?>
          <p class="text-teal-600 my-5">You have already submitted</p>
        <?php endif; ?>
        <?php if(!$exists): ?>
                 <form method="POST" class="grid grid-cols-10 gap-1" enctype="multipart/form-data">
                    <input id="vv" class="hidden" name="file" type="file">
                    <input class="col-span-7 border p-2" name="text" onclick="this.focus()" type="text">
                   <label id="vvi" for="vv" class="bg-teal-600 text-white rounded grid place-items-center col-span-1 ri-attachment-2"></label>
                    <input hidden name="assignment_id" value="<?php echo htmlspecialchars($assignment['id']); ?>">
                    <button class="bg-teal-600 text-white rounded py-2 col-span-2">Submit</button>
                 </form>
          <?php endif; ?>
            <?php endif; ?>
            <?php 
        $res = $conn->querySingle("SELECT response FROM submissions WHERE assignment_id = $aid AND student_id = $student_id");
            
            ?>            
            <p><b>Teacher Response:</b> <?php echo $res;?></p>
        </div>
    <?php
    }

    if (!$hasAssignments) {
        echo '<div class="w-full flex flex-col justify-center items-center gap-4 h-2/4">
       <i class="text-9xl text-gray-400 ri-cloud-off-fill"></i>
        <p class="text-center text-teal-600">No assignments available yet.</p>
    </div>';
    }
    ?>
    <script>
      var f = document.getElementById("vv");
  f.addEventListener("change",(e)=>{
    document.getElementById("vvi").classList.add("ri-file-check-line");
  })
    </script>
  </div>
</body>
</html>