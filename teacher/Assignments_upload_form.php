<?php
session_start();
require 'config.php';

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Step 1: Get teacher's school ID
$teacher_result = $conn->query("SELECT school_id FROM teachers WHERE id = $teacher_id");
$teacher_row = $teacher_result->fetchArray(SQLITE3_ASSOC);
$school_id = $teacher_row['school_id'] ?? null;

if (!$school_id) {
    die("Error: Could not determine teacher's school.");
}

// Step 2: Fetch only classes from this teacher's school
$classes = $conn->query("SELECT classes FROM schools WHERE school_id = '$school_id'");
$all_classes = [];

$ro = $classes->fetchArray(SQLITE3_ASSOC);
$ro = $ro["classes"];
$escaped_school_id = SQLite3::escapeString($school_id);
$class_query = $conn->query("SELECT id, class_name FROM classes WHERE school_id = '$escaped_school_id' ORDER BY class_name ASC");

if ($class_query) {
    while ($row = $class_query->fetchArray(SQLITE3_ASSOC)) {
        $classes[] = $row;
    }
} else {
    die("Error fetching classes: " . $conn->lastErrorMsg());
}
// Step 3: Handle assignment upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $instructions = $_POST['instructions'];
    $due_date = $_POST['due_date'];
    $class_id = $_POST['class_id'];
    $uploaded_file = null;
    // File upload handling
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_name = basename($_FILES['attachment']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg'];

        if (in_array($file_ext, $allowed_extensions)) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $unique_file_name = uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $file_name);
            $destination = $upload_dir . $unique_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $uploaded_file = $destination;
            } else {
                echo "<p class='bg-red-100 border border-red-500 p-2  rounded text-red-500' >Failed to upload the file.</p>";
            }
        } else {
            echo "<p class='bg-red-100 border border-red-500 p-2  rounded text-red-500' >Invalid file type. Allowed: PDF, DOC, DOCX, TXT, PNG, JPG, JPEG.</p>";
        }
    }

    // Insert into assignments table
    $stmt = $conn->prepare("
        INSERT INTO assignments (class_id, teacher_id, title, instructions, due_date, attachment,school)
        VALUES (:class_id, :teacher_id, :title, :instructions, :due_date, :attachment, :school_id)
    ");
    $stmt->bindValue(':class_id', $class_id);
    $stmt->bindValue(':teacher_id', $teacher_id);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':instructions', $instructions);
    $stmt->bindValue(':due_date', $due_date);
    $stmt->bindValue(':school_id', $school_id);
    $stmt->bindValue(':attachment', $uploaded_file);

    
}
?>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <title>Assignment Upload</title>
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
           <div class="p-4 md:w-3/4 md:relative md:left-1/4 md:px-8 main-content">
            <div class="top-bar fixed top-0 bg-teal-600 left-0 max-w-screen w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="md:hidden ri-menu-2-line text-2xl"></span>Assignments</h2>
              

                <form action="logout.php" method="POST" class="mb-0" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>

<h2 class="my-8 relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd12.png" class="-top-1 -left-8 w-2/5 md:w-2/12 md:top-3 object-cover absolute object-center z-10">Send Assignments To Students</h2>

<form method="POST" class="mt-12 md:px-16 w-full"enctype="multipart/form-data">
      <?php
      if($_SERVER["REQUEST_METHOD"]=="POST"){
    if ($stmt->execute()) {
        echo "<p class='bg-green-100 border border-green-500 p-2  rounded text-green-500' >Assignment uploaded successfully!</p>";
    } else {
        echo "<p   class='bg-red-100 border border-red-500 p-2  rounded text-red-500' >Error uploading assignment: " . $conn->lastErrorMsg() . "</p>";
    }
    }
    ?>
    <label class="text-gray-400 uppercase text-sm">Title:</label><br>
    <input class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded " type="text" name="title" required><br><br>

    <label class="text-gray-400 uppercase text-sm">Instructions:</label><br>
    <textarea class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded " rows="5"  name="instructions" required></textarea><br><br>

    <label class="text-gray-400 uppercase text-sm">Due Date & Time:</label><br>
    <input class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded " type="datetime-local" name="due_date" required><br><br>

    <label class="text-gray-400 uppercase text-sm">Class:</label><br>
    <select class="border border-teal-500  p-2 outline-teal-500 mb-5 w-full rounded" name="class_id" id="cl" required>
        <option value="">-- Select Class --</option>
     
    </select><br><br>

    <label class="text-gray-600 uppercase text-sm">Attach a file (PDF, DOC, DOCX, TXT, PNG, JPG, JPEG):</label>
<label for="attachment" class="border border-teal-500 bg-teal-100 h-36 text-teal-800 text-lg p-2 outline-teal-500 w-4/6 mx-auto block rounded "><span class="ri-file-upload-line text-5xl block" ></span><i>Click here to add file attachment</i><p class="font-bold pl-3 my-2" id="pv"></p></label>
    <input class="hidden" id="attachment" type="file" name="attachment"><br><br>

    <button class="bg-teal-600 text-white py-1.5 px-2.5 rounded"  type="submit">Upload Assignment</button>
</form>

<script>
  var classes = [<?php echo $ro;?>];
  var slt = document.getElementById("cl");
  for(i = 0; i< classes.length; i++){
    slt.innerHTML += `<option value="${classes[i]}">${classes[i]}</option>`;
  }
  
  var f = document.getElementById("attachment");
  f.addEventListener("change",(e)=>{
    var fn = e.target.files[0].name;
    document.getElementById("pv").innerHTML = fn;
  })
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
      var classes = [<?php echo $ro;?>];
  var slt = document.getElementById("classes");
  slt.innerText = classes.length
    </script>  

</div>
  </body>
</html>