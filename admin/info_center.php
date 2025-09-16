<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['school_logged_in']) || $_SESSION['school_logged_in'] !== true) {
    die("Access denied. Please log in as a school.");
}
$school = $_SESSION["school_id"];
// Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_post']) && !empty($_POST['school_id']) && !empty($_POST['content'])) {
        $stmt = $conn->prepare("INSERT INTO info_posts (school_id, content) VALUES (?, ?)");
        $stmt->bindValue(1, $_POST['school_id'], SQLITE3_TEXT);
        $stmt->bindValue(2, trim($_POST['content']), SQLITE3_TEXT);
        $stmt->execute();
    }

    if (isset($_POST['delete_post'])) {
        $conn->exec("DELETE FROM info_replies WHERE post_id = " . (int)$_POST['post_id']);
        $conn->exec("DELETE FROM info_likes WHERE post_id = " . (int)$_POST['post_id']);
        $conn->exec("DELETE FROM info_posts WHERE id = " . (int)$_POST['post_id']);
    }

    if (isset($_POST['delete_reply'])) {
        $conn->exec("DELETE FROM info_replies WHERE id = " . (int)$_POST['reply_id']);
    }
}

// Prepare filters
$filter_school = $_GET['filter_school'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$where = [];
if (!empty($filter_school)) {
    $where[] = "p.school_id = '{$filter_school}'";
}
if (!empty($from_date)) {
    $where[] = "DATE(p.created_at) >= DATE('{$from_date}')";
}
if (!empty($to_date)) {
    $where[] = "DATE(p.created_at) <= DATE('{$to_date}')";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$posts = $conn->query("
    SELECT p.*, s.school_name
    FROM info_posts p
    JOIN schools s ON p.school_id = s.school_id
    $where_clause
    ORDER BY p.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Info Center (Filtered)</title>
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
                <h2 onclick="showMenu()" class="font-bold gap-1 flex items-center"><span class="ri-menu-2-line text-2xl md:hidden"></span>Information Post</h2>
              

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

    <div class="filter-box py-4">
      <h4 class="text-gray-400 text-xs">FILTERS</h4>
        <form method="GET" class="flex justify-evenly items-center">
            <select name="filter_school" class="hidden">
                <?php
                    $schools = $conn->query("SELECT * FROM schools WHERE school_id = '$school'");
                    while ($school = $schools->fetchArray(SQLITE3_ASSOC)) {
                        $selected = ($filter_school === $school['school_id']) ? 'selected' : '';
                        echo "<option value='{$school['school_id']}' $selected>{$school['school_name']}</option>";
                    }
                ?>
            </select>
            <label class="text-sm ">From:</label>
            <input type="date" class="w-1/3" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>">

            <label class="text-sm">To:</label>
            <input type="date" class="w-4/12" name="to_date" value="<?php echo htmlspecialchars($to_date); ?>">
            <button class="bg-teal-600 text-white rounded ri-filter-2-line px-1.5 text-sm py-1" type="submit"></button>
        </form>
    </div>
      <h4 class="text-gray-400 text-xs">ADD NEW INFORMATION POST</h4>
    <form method="POST" class="grid gap-2 h-24 grid-cols-8 md:grid-cols-12 grid-rows-4 align-bottom">
        <select name="school_id" class="hidden" required>
            <?php
                $schools->reset();
                while ($school = $schools->fetchArray(SQLITE3_ASSOC)) {
                    echo "<option value='{$school['school_id']}'>{$school['school_name']}</option>";
                }
            ?>
        </select>
        <textarea name="content" class="row-span-4 col-span-7 md:col-span-11 rounded border border-teal-600 p-1" placeholder="Enter new information..." required></textarea>
        <button type="submit" class="rounded-lg bg-teal-600 text-white row-span-1 ri-arrow-up-line aspect-square md:aspect-video col-span-1" name="new_post"></button>
    </form>


    <hr>
      <h4 class="text-gray-400 mt-6 text-xs">POSTS</h4>

    <?php while ($post = $posts->fetchArray(SQLITE3_ASSOC)): 
        $post_id = $post['id'];
        $likes = $conn->querySingle("SELECT COUNT(*) FROM info_likes WHERE post_id = $post_id");
        $replies = $conn->query("
            SELECT r.*, s.full_name
            FROM info_replies r
            LEFT JOIN students s ON r.student_id = s.id
            WHERE r.post_id = $post_id
        ");
    ?>
        <div class="post shadow my-2 rounded border border-gray-200 p-2">
            <p class="text-gray-400 uppercase text-sm"><?php echo htmlspecialchars($post['school_name']); ?></p>
          <p class="mb-3"><?php echo htmlspecialchars($post['content']); ?></p>
            <div class="meta text-sm text-teal-600">Posted on: <?php echo $post['created_at']; ?> | Likes: <?php echo $likes; ?></div>

            <form method="POST" class="actions">
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <button type="submit" name="delete_post" class="ri-delete-bin-6-line text-sm mr-1.5 bg-red-100 text-red-600 border px-1.5 border-red-600 rounded" onclick="return confirm('Delete this post?')">          Delete Post</button>
            </form>
        </div>  
    <?php endwhile; ?>
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