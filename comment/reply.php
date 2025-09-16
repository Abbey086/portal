<?php require_once('Connection.php') ?>
<?php
if(!isset($_GET['comment_id'])){
    echo "<script>alert('Comment ID is required.'); location.replace('./');</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Chat Section</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <style>
         :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }
        
        html,
        body {
            height: 100%;
            width: 100%;
            font-family: Apple Chancery, cursive;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary bg-gradient" id="topNavBar">
        <div class="container">
            <a class="navbar-brand" href="https://sourcecodester.com">
            Sourcecodester
            </a>
        </div>
    </nav>
    <div class="container py-3" id="page-container">
        <h3>Reply to</h3>
        <?php if(isset($_SESSION['response_msg'])): ?>
            <div class="alert alert-<?php echo $_SESSION['response_type']  ?>">
                <p class="m-0"><?php echo $_SESSION['response_msg']  ?></p>
            </div>
        <?php
            unset($_SESSION['response_msg']);
            unset($_SESSION['response_type']);
            endif;
        ?>
        <hr>
        <?php 
        $comment_qry = $conn->query("SELECT * FROM `comments` where comment_id = '{$_GET['comment_id']}' ");
        $result = $comment_qry->fetchArray();
        if(!$result){
            echo "<script>alert('Unkown Comment ID.'); location.replace('./');</script>";
            exit;
        }else{
        ?>
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex align-items-end">
                    <div class="fw-bold flex-grow-1"><?php echo ucwords($result['sender']) ?></div>
                    <span><small class="text-muted"><?php echo date("m d,Y h:i A",strtotime($result['date_created'])) ?></small></span>
                </div>
                <hr>
                <div class="lh-1">
                    <p class=""><span class="mx-3"></span><?php echo str_replace('\n','<br/>',$result['comment']) ?></p>
                </div>
            </div>
        </div>
        <?php } ?>
        <hr>
        <?php 
        if(isset($_GET['reply_id'])){
            $reply_qry = $conn->query("SELECT * FROM `replies` where reply_id = '{$_GET['reply_id']}' ");
            $result2 = $reply_qry->fetchArray();
            if(!$result2){
                echo "<script>alert('Unkown Reply ID.'); location.replace('./');</script>";
                exit;
            }
        }
        ?>
        <form id="comment-form" action="save_reply.php" method="POST">
            <input type="hidden" name="reply_id" value="<?php echo isset($_GET['reply_id']) ? $_GET['reply_id'] : '' ?>" >
            <input type="hidden" name="comment_id" value="<?php echo $_GET['comment_id'] ?>" >
            <div class="form-group">
                <label for="sender" class="control-label">Sender</label>
                <input type="text" name="sender" id="sender" class="form-control form-control-sm rounded-0" value="<?php echo isset($_SESSION['reply-post']) ? $_SESSION['reply-post']['sender'] : (isset($result2['sender']) ? $result2['sender'] : "") ?>" required/>
            </div>
            <div class="form-group">
                <label for="reply" class="control-label">Reply</label>
                <textarea name="reply" id="reply" cols="30" rows="2" class="form-control" style="resize:none" placeholder="Write your reply here" required><?php echo isset($_SESSION['reply-post']) ? $_SESSION['reply-post']['reply'] : (isset($result2['reply']) ? $result2['reply'] : "") ?></textarea>
            </div>
            <div class="form-group">
                <div class="col-12 mt-4">
                    <div class="d-flex justify-content-end align-items-end">
                            <button class="btn btn-sm btn-sm btn-primary rounded-0 me-2" type="submit">Save</button>
                            <a class="btn btn-sm btn-sm btn-secondary rounded-0" href="./">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

</body>

</html>