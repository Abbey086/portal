<?php
require 'config.php';  // Adjust the path as needed

session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch student's class
$teacher_id = $_SESSION['teacher_id'];

//$grade_level="S.2";
// Fetch assignments for the student's class
$ij = "";
if(isset($_GET["id"])){
$avi = $_GET["id"];
$ij = "AND id = $avi";
}

$stmt = $conn->prepare("
    SELECT *
    FROM assignments
    WHERE teacher_id = :teacher_id ".$ij."
     ORDER BY due_date ASC
");
$stmt->bindValue(':teacher_id', $_SESSION['teacher_id']);
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
        echo $filename;
        move_uploaded_file($file['tmp_name'], $filename);
    }
$assid = $_POST["assignment_id"];
        echo $filename;
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl"></span>Submissions</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>

      <h2 class="relative my-8 -z-10 text-2xl pl-20 py-10 font-bold"><img src="../global/dd2.png" class="-top-1 md:w-2/12 md:top-3 -left-8 w-2/5 object-cover absolute object-center -z-20">Assignments by <?php echo htmlspecialchars($_SESSION["teacher_name"]); ?></h2>

    <?php
    $hasAssignments = false;
    while ($assignment = $result->fetchArray(SQLITE3_ASSOC)) {
        $hasAssignments = true;
    ?>
        <div class="assignment text-lg my-3 border border-black p-3 rounded ">
            <h3 class="font-semibold text-2xl "><?php echo htmlspecialchars($assignment['title']); ?></h3>
            <p ><strong class="text-teal-600 block mr-1 inline uppercase font-normal text-sm">Instructions:</strong> <?php echo nl2br(htmlspecialchars($assignment['instructions'])); ?></p>
            <p class="due"><strong class="text-teal-600 block mr-1 font-normal inline uppercase text-sm">Due:</strong><?php echo htmlspecialchars(humanReadableDate($assignment['due_date'])); ?> 
            <strong class="text-teal-600 block mr-1 inline uppercase font-normal text-sm">class:</strong><?php echo htmlspecialchars($assignment['class_id']); ?></p>
            <?php if (!empty($assignment['attachment'])): ?>
                <div class="attachment">
                    <strong>Attachment:</strong>
                    <a class="bg-teal-600 text-white py-1 text-sm px-2 rounded"  href="<?php echo htmlspecialchars($assignment['attachment']); ?>" target="_blank">Download</a>
                </div>
            <?php endif; ?>
        <?php 
                $aid = $assignment['id'];
        $cit = $conn->querySingle("SELECT COUNT(*) FROM submissions WHERE assignment_id = $aid");
       if($cit < 1):
        ?>
        <p class="mt-6 text-gray-800">No submissions yet!</p>
        <?php endif;?>
        <?php if($cit>0 && !isset($_GET["id"])):?>
         <a class="underline text-teal-600 mt-6" href="view_submissions.php?id=<?php echo $aid;?>">View Submissions (<?php echo $cit;?>)</a>
        <?php endif;?>
        </div>
           <?php
              if(isset($_GET["id"]) && $cit>=1){
           
$stmt2 = $conn->prepare("
    SELECT a.*, s.full_name
    FROM submissions a
    LEFT JOIN students s ON s.id = a.student_id
    WHERE a.assignment_id = :aid
     ORDER BY a.submitted_at ASC
");
$stmt2->bindValue(':aid', $_GET["id"]);
$result2 = $stmt2->execute();
    while ($submission = $result2->fetchArray(SQLITE3_ASSOC)) {
      $name = $submission['full_name'];
      $text = $submission['text'];
      $id = $submission['id'];
      $res = $submission['response'];
      $path = $submission['file_path'];
      echo "
      <div class='ml-4 p-2 my-2 border'>
         <b>$name:</b><br>$text<br> 
         Attachment:                     <a class='bg-teal-600 text-white py-1 text-sm px-2 rounded'  href='../student/$path' target='_blank'>Download</a>

         <div class='flex h-8 mt-4 justify-evenly'><input class='border border-teal-500 rounded p-2' value='$res'/><button onclick='send(this, $id)' class='bg-teal-600 text-white py-1 text-sm px-2 rounded' >Send</button></div>
      </div>
      ";
    }
              }

          ?>

    <?php
    }

    if (!$hasAssignments) {
        echo "<p>You have not given any assignments yet.</p>";
    }
    ?>
    <script>
    async  function send(e,i){
  e.innerText = "Sending.."
     var  response = e.parentElement.querySelector("input").value
        const updateRes = await fetch(`subhandler.php?id=${i}&response=${response}`);

  setTimeout(()=>{
  e.innerText = ""
  e.classList.add("ri-chat-check-fill");
  },2000)
  setTimeout(()=>{
  e.classList.remove("ri-chat-check-fill");
  e.innerText = "Send"
  },4500)
      }
    </script>
    
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
    </script>  
    </div>
</body>
</html>