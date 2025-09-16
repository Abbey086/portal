<?php
session_start();
require 'config.php';

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}

$school_id = $_SESSION['school_id'];
$filter_class = $_GET['grade_level'] ?? '';
$search = $_GET['search'] ?? '';

// Build base query
$query = "SELECT * FROM teachers WHERE school_id = :school_id";
$params = [':school_id' => $school_id];

if ($filter_class !== '') {
    $query .= " AND grade_level = :grade_level";
    $params[':grade_level'] = $filter_class;
}

if ($search !== '') {
    $query .= " AND (full_name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY full_name";
$stmt = $conn->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, SQLITE3_TEXT);
}

$result = $stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
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
        <div class="p-3 mt-12 md:w-3/4 md:relative md:left-1/4 main-content">
             <div class="top-bar fixed top-0 bg-teal-600 left-0 w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Manage Teachers</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>
  
<?php if (isset($_GET['msg'])): ?>
    <p class="bg-green-100 border border-green-500 p-2  rounded text-green-500" id="message">
        <?php 
            if ($_GET['msg'] == 'deleted') echo "Teacher deleted successfully.";
            elseif ($_GET['msg'] == 'approve') echo "Teacher approval status changed successfully.";
        ?>
    </p>
<?php endif; ?>
<script>
  var x= document.getElementById("message");setTimeout(()=>{x.style.display='none'},3500)
</script>
<h2 class=" relative -z-10 text-2xl pl-20 py-10 font-bold"><img src="../global/dd12.png" class="-top-1 -left-8 w-2/5 md:w-2/12 md:top-3 object-cover absolute object-center -z-20">Teachers Registered Under Your School</h2>

<form method="GET" class="grid my-2 gap-0.5 grid-cols-8 md:grid-cols-10 grid-rows-1 w-full">
    <input type="text" class="col-span-7 md:col-span-9 outline-0 border rounded bg-white placeholder:text-teal-900/50 border-teal-600" name="search" placeholder="Search name or email" value="<?php echo htmlspecialchars($search); ?>">
    <button class="col-span-1 bg-teal-600 text-white aspect-square md:aspect-video rounded ri-search-2-line" type="submit"></button>
</form>
<style>
table{
  border-collapse: all;
  border-radius: 9px;
}
  th,td{
    border: solid 1px #999;
    overflow: scroll; 
  }
  th{
    font-weight: semibold;
    text-align: left;
    padding: 3px;
  }
  
</style>
<table class="w-full border border-collapse">
    <tr class="border bg-teal-500 text-white ">
        <th>Full Name</th>
        <th>Email</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            
            <td>
              
                <a href="delete_teacher.php?id=<?php echo $row['id']; ?>" class="bg-red-600 px-2 block text-sm py-1 m-0.5 rounded text-center text-white" onclick="return confirm('Delete this teacher?');">Delete</a>
                <a href="approve_teacher.php?id=<?php echo $row['id']; ?>&status=<?php echo $row['status']=='approved'?'unapproved':'approved'?>" class="<?php echo $row['status']=='approved'?'bg-green-600':'bg-red-600'?> px-2 block text-sm py-1 m-0.5 rounded text-center text-white" ><?php echo $row['status']=='approved'?'Unapprove':'Approve'?></a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
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

</body>
</html>
