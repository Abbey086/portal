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
        <h3>Update Comment</h3>
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
        <form id="comment-form" action="save_comment.php" method="POST">
                <input type="hidden" name="comment_id" value="<?php echo $_GET['comment_id'] ?>" >
                <div class="form-group">
                    <label for="sender" class="control-label">Sender</label>
                    <input type="text" name="sender" id="sender" class="form-control form-control-sm rounded-0" value="<?php echo isset($_SESSION['comment-post']) ? $_SESSION['comment-post']['sender'] : (isset($result['sender']) ? $result['sender'] : "") ?>" required/>
                </div>
                <div class="form-group">
                    <label for="comment" class="control-label">Comment</label>
                    <textarea name="comment" id="comment" cols="30" rows="2" class="form-control" style="resize:none" placeholder="Write your comment here" required><?php echo isset($_SESSION['comment-post']) ? $_SESSION['comment-post']['comment'] : (isset($result['comment']) ? $result['comment'] : "") ?></textarea>
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
        <?php } ?>
        <hr>
    </div>

</body>

</html>