<?php 
require_once('Connection.php');
$sql = "DELETE FROM `comments` where comment_id = '{$_GET['comment_id']}'";
$delete = $conn->query($sql);
if($delete){
    $_SESSION['response_type'] = 'success';
    $_SESSION['response_msg'] = 'Comment Successfully deleted.';
    if(empty($_GET['comment_id']))
        header('location:'.$_SERVER['HTTP_REFERER']);
    else
        header('location:./');
}else{
    $_SESSION['response_type'] = 'danger';
    $_SESSION['response_msg'] = 'Deleting comment failed. Error: '. $conn->lastErrorMsg();
    $_SESSION['comment-post'] = $_POST;
header('location:'.$_SERVER['HTTP_REFERER']);
}
?>