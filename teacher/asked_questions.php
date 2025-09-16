<?php
require 'config.php';  // Adjust path if needed

session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  $reply = $_POST["reply"];
  $a = $conn->prepare("UPDATE teacher_questions SET  answer = :answer WHERE id = :id");
  $a->bindValue(":answer",$reply);
  $a->bindValue(":id",$_POST["id"]);
  $a->execute();
  $message = "Sent successfully!";
}
// Fetch all questions for this teacher
$teacher_id = $_SESSION['teacher_id'];
$stmt = $conn->prepare("
    SELECT tq.id, s.full_name AS student_name, tq.question, tq.asked_at, tq.answer
    FROM teacher_questions tq
    JOIN students s ON tq.student_id = s.id
    WHERE tq.teacher_id = :teacher_id
    ORDER BY tq.asked_at DESC
");
$stmt->bindValue(':teacher_id', $teacher_id);
$result = $stmt->execute();

// Function to format date
function humanReadableDate($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asked Questions</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Questions</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>
  
    <h2 class="relative my-8 -z-10 text-2xl pl-20 py-10 font-bold"><img src="../global/dd10.png" class="-top-1 -left-8 w-2/5 object-cover absolute object-center md:w-2/12 md:top-3 -z-20">Questions From Students</h2>
    <?php
    $hasQuestions = false;
    while ($question = $result->fetchArray(SQLITE3_ASSOC)) {
        $hasQuestions = true;
        ?>
        <div class="post my-3 bg-gray-50 rounded border border-teal-700 p-2">
            <p class="text-teal-600 mr-3 inline uppercase text-sm"><strong class="text-teal-800">From:</strong> <?php echo htmlspecialchars($question['student_name']); ?></p>
            <p class="text-teal-600 inline uppercase text-sm"><strong class="text-teal-900">Asked:</strong> <?php echo htmlspecialchars(humanReadableDate($question['asked_at'])); ?></p>
            <p><strong>Question:</strong><br><?php echo nl2br(htmlspecialchars($question['question'])); ?></p>
            <form method="POST" class="grid my-2 gap-0.5 grid-cols-10 md:grid-cols-12 grid-rows-1 md:gap-1.5 w-full">
              <input value="<?php echo htmlspecialchars($question['id']); ?>" class="hidden" name="id">
              <textarea type="text" name="reply" class="col-span-9 md:col-span-11 outline-0 border p-3 rounded bg-white placeholder:text-teal-900/50 border-teal-600" placeholder="Reply: <?php echo $question['answer'];?>" rows="2"></textarea>
               <button type="submit" class="col-span-1 bg-teal-600 text-white aspect-square rounded ri-reply-all-line"></button>
            </form>
        </div>
        <?php
    }

    if (!$hasQuestions) {
        echo '    <div class="w-full h-96 flex justify-center items-center flex-col text-center gap-3">
      
    <span class=" text-8xl text-teal-600  ri-information-off-fill"></span>
        <p class="text-gray-400">No questions for you available yet.</p>
    </div>';
    }
    ?>
     
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