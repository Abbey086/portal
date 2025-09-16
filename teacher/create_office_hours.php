<?php
session_start();
require 'config.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_datetime'];
    $end = $_POST['end_datetime'];
    $teacher_id = $_SESSION['teacher_id'];

    if ($start && $end) {
        $stmt = $conn->prepare("INSERT INTO office_hours (teacher_id, start_datetime, end_datetime) VALUES (:teacher_id, :start, :end)");
        $stmt->bindValue(':teacher_id', $teacher_id, SQLITE3_INTEGER);
        $stmt->bindValue(':start', $start, SQLITE3_TEXT);
        $stmt->bindValue(':end', $end, SQLITE3_TEXT);

        if ($stmt->execute()) {
            $msg = "Office hour slot created.";
        } else {
            $error = "Failed to create slot.";
        }
    } else {
        $error = "Both start and end times are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Create Office Hours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <style>
      
    </style>
</head>
<body>
 <div class="sidebar fixed pt-14 top-0 w-1/2 bg-teal-800 h-screen overflow-y-scroll md:left-0 md:w-1/4 -left-1/2">
          <div onclick="hideMenu()" class="cover hidden bg-black/50 md:hidden fixed top-0 right-0 w-1/2 h-screen"></div>
            <div class="sidebar-header border-b border-gray-300 flex flex-col justify-center text-center text-white items-center w-full gap-0.5">
              
                <h3 class="font-bold "><?php echo htmlspecialchars($_SESSION['teacher_name']); ?></h3>
                <p class="opacity-80  text-sm">Teacher ID: <?php echo htmlspecialchars($_SESSION['teacher_id']); ?></p>
                <p class="opacity-80 mb-3 text-sm">School ID: <?php echo htmlspecialchars($_SESSION['school_id']); ?></p>
            </div>
            <nav class="flex pt-4 text-white flex-col gap-3 px-2 overflow-y-scroll">
                 <a class="menu-item rounded flex items-center" href="dashboard.php"><span class="ri-dashboard-fill text-2xl mr-1"></span>Dashboard</a>
                 <a class="menu-item rounded flex items-center" href="create_announcements.php"><span class="ri-megaphone-fill text-2xl mr-1"></span>Create Announcements</a>
        <a class="menu-item rounded flex items-center" href="Assignments_upload_form.php"><span class="ri-sticky-note-fill text-2xl mr-1"></span>Assignments</a>
        <a class="menu-item rounded flex items-center" href="notes.php"><span class="ri-file-add-fill text-2xl mr-1"></span>Upload Notes</a>
        <a class="menu-item rounded flex items-center" href="view_notes.php"><span class="ri-file-2-fill text-2xl mr-1"></span>View Notes</a>
        <a class="menu-item rounded flex items-center" href="view_students.php"><span class="ri-folder-user-fill text-2xl mr-1"></span>My Students</a>
        <a class="menu-item rounded flex items-center" href="asked_questions.php"><span class="ri-question-fill text-2xl mr-1"></span>Questions to you</a>
        <a class="menu-item rounded flex items-center" href="view_submissions.php"><span class="ri-sticky-note-2-fill text-2xl mr-1"></span>Assignment Submissions</a>
        <a class="menu-item rounded flex items-center" href="create_office_hours.php"><span class="ri-calendar-event-fill text-2xl mr-1"></span>Create Office Slot</a>
        <a class="menu-item rounded flex items-center" href="view_office_hours.php"><span class="ri-calendar-check-fill text-2xl mr-1"></span>Office Hour Bookings</a>
        <a class="menu-item rounded flex items-center" href="view_timetable.php"><span class="ri-calendar-schedule-fill text-2xl mr-1"></span>View Timetable</a>
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
           <div class="p-4 md:w-3/4 md:relative md:left-1/4 md:px-8 main-content">            <div class="top-bar fixed top-0 bg-teal-600 left-0 max-w-screen w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl"></span>Office Hours</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>

      <h2 class="relative my-8 -z-10 text-2xl pl-20 py-10 font-bold"><img src="../global/dd8.png" class="-top-1 -left-8 w-2/5 object-cover absolute md:w-2/12 md:top-3 object-center -z-20">Create Office Hour Slot<br><i style=" line-height: -18px" class="leading-4 text-gray-400 text-lg font-normal ml-1">Create a slot for student consultation. Make them small (15min - 45min) because each will be booked by a single student.</i></h2>

<form class="p-4"method="post">
  <?php
if (!empty($msg)) echo "<p style='color:green;'>$msg</p>";
if (!empty($error)) echo "<p style='color:red;'>$error</p>";
?>
    <label class="text-gray-400 block mr-3 inline uppercase text-sm">Start Time: </label><br>
    <input class="col-span-7 outline-0 border rounded bg-white py-2 placeholder:text-teal-900/50 border-teal-600" type="datetime-local" name="start_datetime" required><br><br>
    
    <label class="text-gray-400 block mr-3 inline uppercase text-sm">End Time: </label><br>
    <input type="datetime-local" class="col-span-7 outline-0 border rounded bg-white placeholder:text-teal-900/50 py-2 border-teal-600" name="end_datetime" required><br><br>
    <button class="bg-teal-600 text-white py-1.5 px-2.5 rounded" type="submit">Create Slot</button>
</form>


<a href="dashboard.php" class="block underline text-center text-teal-600 text-sm">Back to Dashboard</a>
 
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

</div>
</body>
</html>