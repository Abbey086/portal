<?php require_once('Connection.php') ?>
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
        <h3>Simple Comment Section</h3>
        <?php if(isset($_SESSION['response_msg'])): ?>
            <div class="alert alert-<?php echo $_SESSION['response_type']  ?>">
                <p class="m-0"><?php echo $_SESSION['response_msg']  ?></p>
            </div>
        <?php
            unset($_SESSION['response_msg']);
            unset($_SESSION['response_type']);
            endif;
            if(isset($_SESSION['comment-post']))
            unset($_SESSION['comment-post']);
            if(isset($_SESSION['reply-post']))
            unset($_SESSION['reply-post']);
        ?>
        <hr>
        <?php 
        $comment_qry = $conn->query("SELECT * FROM `comments` order by strftime(date_created) asc ");
        while($row = $comment_qry->fetchArray()):
        ?>
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex align-items-end">
                    <div class="fw-bold flex-grow-1"><?php echo ucwords($row['sender']) ?></div>
                    <span><small class="text-muted"><?php echo date("m d,Y h:i A",strtotime($row['date_created'])) ?></small></span>
                </div>
                <hr>
                <div class="lh-1">
                    <p class=""><span class="mx-3"></span><?php echo str_replace('\n','<br/>',$row['comment']) ?></p>
                </div>
                <hr>
                <div class="w-100 d-flex justify-content-end">
                    <a href="reply.php?comment_id=<?php echo $row['comment_id'] ?>" class="btn btn-primary btn-sm rounded-0 me-2">Reply</a>
                    <a href="edit_comment.php?comment_id=<?php echo $row['comment_id'] ?>" class="btn btn-primary btn-sm rounded-0 me-2">Edit</a>
                    <a href="delete_comment.php?comment_id=<?php echo $row['comment_id'] ?>" class="btn btn-danger btn-sm rounded-0">Delete</a>

                </div>
            </div>
        </div>
        <?php 
            $reply_qry = $conn->query("SELECT * FROM `replies` where comment_id ='{$row['comment_id']}' order by strftime(date_created) asc ");
            while($rrow = $reply_qry->fetchArray()):
        ?>
        <div class="card mb-2" style="margin-left:15%">
            <div class="card-body">
                <div class="d-flex align-items-end">
                    <div class="fw-bold flex-grow-1"><?php echo ucwords($rrow['sender']) ?></div>
                    <span><small class="text-muted"><?php echo date("m d,Y h:i A",strtotime($rrow['date_created'])) ?></small></span>
                </div>
                <hr>
                <div class="lh-1">
                    <p class=""><span class="mx-3"></span><?php echo str_replace('\n','<br/>',$rrow['reply']) ?></p>
                </div>
                <hr>
                <div class="w-100 d-flex justify-content-end">
                    <a href="reply.php?reply_id=<?php echo $rrow['reply_id'] ?>&comment_id=<?php echo $row['comment_id'] ?>" class="btn btn-primary btn-sm rounded-0 me-2">Edit</a>
                    <a href="delete_reply.php?reply_id=<?php echo $rrow['reply_id'] ?>" class="btn btn-danger btn-sm rounded-0">Delete</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endwhile; ?>
        <hr>
        <form id="comment-form" action="save_comment.php" method="POST">
            <div class="form-group">
                <label for="sender" class="control-label">Sender</label>
                <input type="text" name="sender" id="sender" class="form-control form-control-sm rounded-0" value="<?php echo isset($_SESSION['comment-post']) ? $_SESSION['comment-post']['sender'] : "" ?>" required/>
            </div>
            <div class="form-group">
                <label for="comment" class="control-label">Comment</label>
                <textarea name="comment" id="comment" cols="30" rows="2" class="form-control" style="resize:none" placeholder="Write your comment here" required><?php echo isset($_SESSION['comment-post']) ? $_SESSION['comment-post']['comment'] : "" ?></textarea>
            </div>
            <div class="form-group">
                <div class="col-12 mt-4">
                    <div class="d-flex justify-content-end align-items-end">
                            <button class="btn btn-sm btn-sm btn-primary rounded-0 me-2" type="submit">Save</button>
                            <button class="btn btn-sm btn-sm btn-secondary rounded-0" type="reset">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

</html>