<?php
require 'config.php';  // Adjust the path as needed

session_start();

if (!isset($_SESSION['teacher_id'])) {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $res = trim($_GET['response']) ?? "";
    $id = $_GET['id'] ?? "";
    echo($res . $id);
        $conn->exec("UPDATE submissions SET response = '$res' WHERE id = $id");
die("Done"); 
  
}
?>