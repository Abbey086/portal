<?php
session_start();
require_once 'config.php';

$school_id = $_SESSION['school_id'] ?? null;
$classId = $_GET['class_id'];

$c = $conn->querySingle("SELECT COUNT(*) as count FROM students WHERE grade_level = '$classId' AND school_id = '$school_id'");

        echo $c;
    


?>