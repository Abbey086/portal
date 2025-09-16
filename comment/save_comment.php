<?php 
require_once('Connection.php');
if(isset($_SESSION['comment-post'])){
    unset($_SESSION['comment-post']);
}
$comment_id = isset($_POST['comment_id']) ? $_POST['comment_id'] : '';
$sender = $_POST['sender'];
$comment = $conn->escapeString($_POST['comment']);

if(empty($comment_id))
$sql = "INSERT INTO `comments` (`sender`,`comment`) VALUES ('{$sender}','{$comment}')";
else
$sql = "UPDATE `comments` set `sender` = '{$sender}' ,`comment` = '{$comment}' where comment_id = '$comment_id'";

$save = $conn->query($sql);
if($save){
    $_SESSION['response_type'] = 'success';
    $_SESSION['response_msg'] = 'Comment Successfully saved.';
    if(empty($comment_id))
        header('location:'.$_SERVER['HTTP_REFERER']);
    else
        header('location:./');
}else{
    $_SESSION['response_type'] = 'danger';
    $_SESSION['response_msg'] = 'Saving comment failed. Error: '. $conn->lastErrorMsg();
    $_SESSION['comment-post'] = $_POST;
header('location:'.$_SERVER['HTTP_REFERER']);
}
