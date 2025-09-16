<?php 
require_once('Connection.php');
$sql = "DELETE FROM `replies` where reply_id = '{$_GET['reply_id']}'";
$delete = $conn->query($sql);
if($delete){
    $_SESSION['response_type'] = 'success';
    $_SESSION['response_msg'] = 'Reply Successfully deleted.';
    if(empty($_GET['reply_id']))
        header('location:'.$_SERVER['HTTP_REFERER']);
    else
        header('location:./');
}else{
    $_SESSION['response_type'] = 'danger';
    $_SESSION['response_msg'] = 'Deleting reply failed. Error: '. $conn->lastErrorMsg();
    $_SESSION['reply-post'] = $_POST;
header('location:'.$_SERVER['HTTP_REFERER']);
}
?>