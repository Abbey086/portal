<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}

$school_id = $_SESSION['school_id'];

// Fetch school data
$stmt = $conn->prepare("SELECT * FROM schools WHERE school_id = :school_id");
$stmt->bindValue(':school_id', $school_id, SQLITE3_TEXT);
$result = $stmt->execute();

$school = $result->fetchArray(SQLITE3_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - <?php echo htmlspecialchars($school['school_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link rel="stylesheet" href="../remixicon.css">
    <script src="../tw.js"></script>
</head>
<body onload="popClasses()">
   <div class="sidebar z-20 fixed top-0 w-1/2 bg-teal-800 h-screen md:w-1/4 md:left-0 -left-1/2">
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
        <div class="md:p-3 md:mt-9 md:w-3/4 md:relative md:left-1/4 p-3 md:px-12 mt-12 main-content">
            <div class="top-bar z-30 fixed top-0 bg-teal-600 left-0 w-full flex px-2 items-center justify-between text-white py-2 text-lg">
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl"></span>School Settings</h2>
              

                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn text-sm bg-white text-teal-600 font-bold rounded py-1 px-2 ">Logout</button>
                </form>
            </div>
  
    <?php
    $message = $_GET["message"];
    if ($message) echo "<p class='bg-teal-100 border border-teal-500 p-2  rounded text-teal-500' id='message'><strong>$message</strong></p>"; ?>
<script>
  var x= document.getElementById("message");setTimeout(()=>{x.style.display='none'},5500)
</script>

<div class="container">
<h3 class="text-black my-1 pb-1 border-b font-bold">School Bio Data</h3>
    <form action="save_settings.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="text-gray-400 uppercase text-sm block">School Name</label>
            <input type="text" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-1 w-full rounded mb-2 inline-block p-1 "  value="<?php echo htmlspecialchars($school['school_name']); ?>" readonly name="name">
        </div>

        <div class="form-group">
            <label class="text-gray-400 uppercase text-sm block">Upload Logo</label>
            <div class="w-28 z-10 mb-5 mr-1 relative aspect-square">
                <img class="w-full h-full object-center object-cover image rounded-xl" src="<?php echo $logo_url; ?>" alt="Current Logo">
            <input type="file" name="school_logo" accept="image/*"  class="imageinput absolute -bottom-3 -right-3 rounded-full p-0.5 w-9 shadow h-9 bg-white grid place-items-center text-2xl ri-edit-fill">
                <!--<label for="image" class="absolute -bottom-3 -right-3 rounded-full p-0.5 w-9 shadow h-9 bg-white grid place-items-center text-2xl ri-edit-fill"></label>-->
            </div>
        </div>

<div id="c" class="flex gap-1 flex-wrap"onload="popClasses()">
  
</div>        
    <div class="fixed grid place-items-center z-30 bg-black/70 w-full h-screen hidden inset-0" id="editdialog">
      <div class="bg-white rounded-lg w-5/6 p-4">
        <h2 class="font-bold text-2xl">Edit Class Name</h2>
        <input class="border-teal-500 border rounded w-full my-4 outline-0 p-1" id="editdialoginput"/>
        <button type="button" class="bg-teal-600 text-white py-1.5 px-2.5 x rounded">Confirm</button>
        <button onclick="unec()" type="button" class="bg-gray-300 py-1.5 px-2.5 rounded">Cancel</button>
      </div>
    </div>
        <script>
          
            var input = document.querySelector(".imageinput");
            input.addEventListener("change",(e)=>{
              
            var url = URL.createObjectURL(e.target.files[0]);
            document.querySelector(".image").src = url
          
            })
          var aclasses = <?php echo "[".$school["classes"]."];";?>
          
          function popClasses(){
                      var ctn = document.getElementById("c")
                      var ctnIn = document.getElementById("classes")
          ctn.innerHTML="";
          ctnIn.value="";
            for (i=0;i<aclasses.length;i++){
           ctnIn.value += (!i?"'":",'")+aclasses[i]+"'";
          ctn.innerHTML += "<span class='bg-gray-50 border border-gray-300 rounded p-2 text-sm'>"+aclasses[i]+"<i onclick='eC(\""+aclasses[i]+"\")' class='ml-2.5 w-5 bg-green-100 border border-green-500 text-green-500 ri-edit-fill aspect-square rounded mx-0.5 p-1'> </i>"+"<i onclick='dC(\""+aclasses[i]+"\")' class='w-7 bg-red-100 border border-red-500 text-red-500 ri-delete-bin-6-fill rounded aspect-square p-1'> </i>"+"</span>" ;
          }}
        
          function addClass(){
          aclasses.push(document.getElementById("newClass").value)
            popClasses()
          }
          async function dC(tbd){
          const f = await fetch("class_size.php?class_id="+tbd);
          const result = await f.json();
          
          if(result < 1){
            if(confirm("Are you sure you want to delete "+tbd+" class? This is irreversible")){
             var tempArray = []
            for (i=0;i<aclasses.length;i++){
                         if(aclasses[i] !== tbd){
                           tempArray.push(aclasses[i])
                         }
                       }
                aclasses=tempArray
                popClasses()}
            
          }else{alert("This class already has "+result+" members(s). It can not be deleted. Make sure the members first change to other class or change its name instead.");}
          }
          function finalec(thd,elem){
            elem.innerHTML = "Updating";
            var i = aclasses.indexOf(thd);
            aclasses[i] = document.getElementById("editdialoginput").value
            popClasses();
            unec();
          }
            var dialog = document.querySelector("#editdialog");
          function eC(x){
            document.getElementById("editdialoginput").value = x;
            document.querySelector("#editdialog button.x").setAttribute("onclick","finalec('"+x+"',this)");
            dialog.classList.remove("hidden");
          }
          function unec(){
            dialog.classList.add("hidden");
            
          }
        </script>
<div>
  <input name="classes" class="hidden" value="" id="classes">
              <label class="text-gray-400 uppercase text-sm block">Add a new class</label>

  <input placeholder="Add a new class" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-1 w-10/12 rounded mb-2 inline-block p-1 "  id="newClass"><button class="bg-teal-600 text-white w-1/12 h-8 text-xl ri-add-line aspect-square rounded" type="button" onclick="addClass()"></button>
</div>
        <button type="submit" class="bg-teal-600 text-white py-1.5 px-2.5 rounded" class="btn">Save Changes</button>
    </form>
    <br><br>
    <h3 class="text-black my-1 pb-1 border-b font-bold">Security Settings</h3>
<h4 class="text-gray-900 uppercase text-sm block"
>CHANGE Password</h4>
    <form method="POST" action="update_password.php">
        <div class="form-group">
            <label class="text-gray-400 uppercase text-sm block">Current Password</label>
            <input type="password" name="current_password" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-1 w-full rounded mb-2 inline-block p-1 "   required>
        </div>
        <div class="form-group">
            <label class="text-gray-400 uppercase text-sm block">New Password</label>
            <input type="password" name="new_password" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-1 w-full rounded mb-2 inline-block p-1 "   required>
        </div>
        <div class="form-group">
            <label class="text-gray-400 uppercase text-sm block">Confirm New Password</label>
            <input type="password" class="border border-teal-300 outline-teal-500 bg-teal-50 mx-1 w-full rounded mb-2 inline-block p-1 "   name="confirm_password" required>
        </div>
        <button type="submit" class="btn bg-teal-600 text-white py-1.5 px-2.5 rounded">Update Password</button>
    </form>
</div>
   <br><br>
    <h3 class="text-black my-1 pb-1 border-b font-bold">Privacy Rights</h3>
    <div class="bg-teal-100 border p-3 m-1 rounded border-teal-600 text-teal-600">
      <h3 class="my-1 capitalize font-bold">Request a copy of my data</h3>
      You will need to do this by sending us an email.<br><br>
     <a class="bg-teal-600 rounded py-1 px-2 text-white " href="mailto: info@amandainc.xyz">Ok, I understand</a>
    </div>
    <div class="bg-red-100 border p-3 m-1 rounded border-red-600 text-red-600">
      <h3 class="my-1 font-bold">Delete My Data</h3>
      You understand that this is a permanent action. It can't be undone and may lead data loss of your teachers, administrators and students.
      You will need to do this by sending us an email.<br><br>
     <a class="bg-red-600 rounded py-1 px-2 text-white " href="mailto: info@amandainc.xyz">I understand</a>
    </div>
   <br><br>
    <h3 class="text-black my-1 pb-1 border-b font-bold">Legal & Licensing</h3>
    <p class="text-center text-gray-600">This application was developed and is maintained by <a href="" class="underline text-teal-600">Amanda Inc.</a></p>
    <p class="text-center text-gray-600">You are licensed to use this software according to the <a href="" class="text-teal-600 underline">End User License Agreement.</a></p>
    <p class="text-center text-gray-600">No pages of this software may be redistributed, reprinted, republished for commercial purposes without written consent from its maintainers otherwise specified by the <a href="" class="text-teal-600 underline">End User License Agreement.</a> All rights reserved.</p>
<p class="text-center text-gray-600">Copyright &copy; 2025.</p>
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
</body>
</html>