<?php
require 'config.php';

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$school_id = $_SESSION['school_id'];


// Fetch Suggestions for this class
$query = "
    SELECT *
    FROM suggestions
    WHERE school_id = :school_id";

$stmt = $conn->prepare($query);
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$result = $stmt->execute();

$suggestions = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $suggestions[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
    <title>Suggestions</title>
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
        <div class="p-3 mt-12 md:w-3/4 md:relative md:left-1/4 md:px-12 main-content">
            <div class="top-bar fixed top-0 bg-teal-600 left-0 w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Suggestions</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>


            <div class="welcome-section py-7 mt-[55px] px-2 -z-10 rounded-lg shadow-lg border-teal-600 border relative w-full ">
                <h2 class="text-2xl w-4/6 md:w-1/2 font-bold ">Suggestions to, <?php echo htmlspecialchars($_SESSION['school_name']); ?>!</h2>
                <p class="text-sm w-4/6 text-gray-800">Here's what your Students need.</p>
                <img class="absolute md:w-3/12 -right-3 -top-6 w-5/12" src="../global/dd9.png">
            </div>        
        <br>
        <br>
        <br>
          
    <?php if (empty($suggestions)): ?>
        <p class="text-center my-20 text-gray-600"> <span class="block text-8xl text-teal-600 ri-cloud-off-fill"></span>No Suggestions available.</p>
        <br>
        <br>
    <?php else: ?>
        <?php foreach ($suggestions as $suggestion): ?>
            <div class="w-full md:w-10/12 md:mx-auto  p-2 border border-teal-500 my-2 rounded-lg shadow">
                <p><?php echo nl2br(htmlspecialchars($suggestion['content'])); ?></p>
                <small class="text-xs text-teal-600">Posted on <?php echo $suggestion['created_at']; ?></small>
            </div>
    
        <?php endforeach; ?>
    <?php endif; ?>

    <p class="text-center text-teal-600"><a href="admin_dashboard.php" class="underline">Back to Dashboard</a></p>
    
    </div>
    
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
</body>
</html>