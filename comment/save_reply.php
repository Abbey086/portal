<?php 
require_once('Connection.php');
if(isset($_SESSION['reply-post'])){
    unset($_SESSION['reply-post']);
}
$reply_id = isset($_POST['reply_id']) ? $_POST['reply_id'] : '';
$comment_id = $_POST['comment_id'];
$sender = $_POST['sender'];
$reply = $conn->escapeString($_POST['reply']);

if(empty($reply_id))
$sql = "INSERT INTO `replies` (`comment_id`,`sender`,`reply`) VALUES ('{$comment_id}','{$sender}','{$reply}')";
else
$sql = "UPDATE `replies` set `sender` = '{$sender}' ,`reply` = '{$reply}' where reply_id = '$reply_id'";

$save = $conn->query($sql);
if($save){
    $_SESSION['response_type'] = 'success';
    $_SESSION['response_msg'] = 'Reply Successfully saved.';
    header('location:./');
}else{  
    $_SESSION['response_type'] = 'danger';
    $_SESSION['response_msg'] = 'Saving reply failed. Error: '. $conn->lastErrorMsg();
    $_SESSION['reply-post'] = $_POST;
    header('location:'.$_SERVER['HTTP_REFERER']);
}
