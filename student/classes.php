<?php
session_start();
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET' ) {
    $schoolId = $_GET['s'];

    $exists = $conn->querySingle("SELECT COUNT(*) FROM schools WHERE school_id = '$schoolId'");
    if ($exists) {
        $t = $conn->prepare("SELECT classes FROM schools WHERE school_id = :sid");
          $t->bindValue(':sid',$schoolId);
          $r = $t->execute();
          $ro = $r->fetchArray(SQLITE3_ASSOC);
          echo $ro["classes"];
    }
    else{
      echo("404");
    }
}
?>