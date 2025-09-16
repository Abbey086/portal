<?php
require 'config.php';
if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}

$school_id = $_SESSION['school_id'];
$classes = $conn->query("SELECT classes FROM schools WHERE school_id = '$school_id'");
$ro = $classes->fetchArray(SQLITE3_ASSOC);
$ro = $ro["classes"];


$query = "SELECT * FROM students WHERE school_id = :school_id";
$params = [':school_id' => $school_id];
$query .= " ORDER BY grade_level, full_name";
$stmt = $conn->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, SQLITE3_TEXT);
}

$result = $stmt->execute();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_text = trim($_POST['message']);
    $recipient_type = $_POST['recipient_type'];
    $sender_id = $_SESSION['school_id'] ?? null; // Use teacher or admin session

    $stmt = $conn->prepare("
        INSERT INTO notifications (school_id, class_id, student_id, is_read, content)
        VALUES (:school_id, :class_id, :student_id, 0, :message)
    ");

    $stmt->bindValue(':message', $message_text, SQLITE3_TEXT);
    $stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);

    if ($recipient_type === 'student') {
        $stmt->bindValue(':student_id', $_POST['student_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':class_id', null, SQLITE3_NULL);
    } elseif ($recipient_type === 'class') {
        $stmt->bindValue(':class_id', $_POST['class_id'], SQLITE3_TEXT);
        $stmt->bindValue(':student_id', null, SQLITE3_NULL);
    }

    if ($stmt->execute()) {
        $message = "Notification sent!";
    } else {
        $message = "Failed to send notification.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Send Notification</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body>
  
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
        <div class="md:p-3 md:mt-9 md:w-3/4 md:relative md:left-1/4 p-3 mt-12 main-content">             <div class="top-bar fixed top-0 bg-teal-600 left-0 w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Send a Notification</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>
  <h2 class=" relative -z-20 text-2xl pl-20 py-10 font-bold"><img src="../global/dd7.png" class="-top-1 -left-8 w-2/5  md:w-2/12 md:top-3 object-cover absolute object-center z-10">Send specified information (Notifications)</h2>

    <?php if ($message) echo "<p class='bg-green-100 border border-green-500 p-2  rounded text-green-500' id='message'><strong>$message</strong></p>"; ?>
<script>
  var x= document.getElementById("message");setTimeout(()=>{x.style.display='none'},3500)
</script>


    <form method="POST" class=" mt-9">
        <label class="text-gray-400 uppercase text-sm">Send to:</label>
        <select name="recipient_type" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-2 rounded inline-block p-1 " onchange="toggleRecipient(this.value)">
            <option value="student">Individual Student</option>
            <option value="class">Entire Class</option>
        </select>
        <br/>
        <br/>
        <label class="text-gray-400 uppercase text-sm">SELECT RECIPIENT: </label>
        <select id="student_select" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-2 rounded inline-block p-1 "  name="student_id" >
          <option>Select Student</option>
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
              
           <?php  
           $id = $row["id"]; $name = $row["full_name"]; $class = $row["grade_level"];
           echo "<option value='$id'>$name ($class)</option>"; ?>
    <?php endwhile; ?>
        </select>
        <select id="class_select" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-2 rounded inline-block p-1 "  name="class_id" style="display:none;">
            <option>Select Class</option>
        </select>
        <br/>
        <br/>
        <label class="text-gray-400 uppercase text-sm">Message:</label><br>
        <textarea name="message" class="border border-teal-300 h-48 p-2 outline-teal-500 w-full rounded "required></textarea><br>


        <button type="submit" class="bg-teal-600 text-white py-1.5 px-2.5 rounded">Send</button>
    </form>
    <br><br><br>
    <a href="admin_dashboard.php" class="block underline text-center text-teal-600 text-sm">Back to dashboard</a>
    </div>
       <script>
      
      function showMenu(){
        var cover =  document.querySelector(".cover")
        var btn =  document.querySelector(".top-bar h2 span")
        var sidebar =  document.querySelector(".sidebar")
        setTimeout(()=>{cover.classList.remove("hidden")},500);
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

    <script>
    function toggleRecipient(type) {
        document.getElementById('student_select').style.display = (type === 'student') ? 'inline-block' : 'none';
        document.querySelector('#student_select option').setAttribute("selected","");
        document.querySelector('#class_select option').setAttribute("selected","");
        document.querySelector('#student_select option[selected]').removeAttribute("selected");
        document.querySelector('#class_select option[selected]').removeAttribute("selected");
        document.getElementById('class_select').style.display = (type === 'class') ? 'inline-block' : 'none';
    }
    </script>
    <script>
  var classes = [<?php echo $ro;?>];
  var slt = document.getElementById("class_select");
  for(i = 0; i< classes.length; i++){
    slt.innerHTML += `<option value="${classes[i]}">${classes[i]}</option>`;
  
  }
  </script>
</body>
</html>