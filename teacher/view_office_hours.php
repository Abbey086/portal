<?php
session_start();
require 'config.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

$results = $conn->query("SELECT o.*, s.full_name AS student_name
                         FROM office_hours o
                         LEFT JOIN bookings b ON o.id = b.office_hour_id
                         LEFT JOIN students s ON b.student_id = s.id
                         WHERE o.teacher_id = $teacher_id
                         ORDER BY o.start_datetime ASC");
                                                  ?>

<!DOCTYPE html>
<html>
<head><title>My Office Hours</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    
<style>
table{
  border-collapse: all;
  border-radius: 9px;
}
  th,td{
    border: solid 1px #999;
    overflow: scroll;
    padding: 4px;
  }
  th{
    font-weight: semibold;
    text-align: left;
    padding: 3px;
  }
  
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

      <h2 class="relative my-8 -z-10 text-2xl pl-20 py-10 font-bold"><img src="../global/dd2.png" class="-top-1 md:w-2/12 md:top-3 -left-8 w-2/5 object-cover absolute object-center -z-20">Office Hour Bookings<br><i style=" line-height: -18px" class="leading-4 text-gray-400 text-lg font-normal ml-1">Communicate on where to meet through the announcements for each slot.</i></h2>

<table class="w-full border border-collapse" >
    <tr class="border bg-teal-500 text-white ">
        <th>Start Time</th>
        <th>End Time</th>
        <th>Status</th>
    </tr>
    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)) : ?>
    <tr>
        <td><?= htmlspecialchars(str_replace("T","  ",$row['start_datetime'])) ?></td>
        <td><?= htmlspecialchars(str_replace("T","  ",$row['end_datetime']))?></td>
        <td>
            <?= $row['student_name'] ? "Booked by " . htmlspecialchars($row['student_name']) : ((strtotime($row["end_datetime"]) <= strtotime(date('Y-m-d', time())))? "<span class='text-red-600'>Expired</span>":"Available") ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<a class="block underline text-center my-4 text-teal-600 text-sm" href="dashboard.php">Back to Dashboard</a>
 
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