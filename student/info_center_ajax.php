<?php
session_start();
$dbPath = '/storage/emulated/0/htdocs/school_portal.db';

// Check if session variables are set
if (!isset($_SESSION['school_id']) || !isset($_SESSION['student_id'])) {
    die("Session variables not set.");
}

$conn = new SQLite3($dbPath);

// Human-friendly date function
function humanReadableDate($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $today = strtotime(date('Y-m-d', $now));
    $yesterday = strtotime('-1 day', $today);

    if (date('Y-m-d', $timestamp) === date('Y-m-d', $today)) {
        return 'Today at ' . date('g:i A', $timestamp);
    } elseif (date('Y-m-d', $timestamp) === date('Y-m-d', $yesterday)) {
        return 'Yesterday at ' . date('g:i A', $timestamp);
    } elseif ($timestamp >= strtotime('-6 days', $today)) {
        return date('l', $timestamp) . ' at ' . date('g:i A', $timestamp);
    } else {
        return date('M j, Y \a\t g:i A', $timestamp);
    }
}

// Fetch posts with likes count
$postsQuery = "SELECT p.id, p.content, p.created_at, s.school_name, 
                      (SELECT COUNT(*) FROM info_likes WHERE post_id = p.id) AS like_count
               FROM info_posts p
               JOIN schools s ON p.school_id = s.school_id
               ORDER BY p.created_at DESC";
$postsResult = $conn->query($postsQuery);

// Handle likes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    $postId = $_POST['post_id'];
    $schoolId = $_SESSION['school_id'];

    // Check if already liked
    $checkLikeQuery = "SELECT 1 FROM info_likes WHERE post_id = :post_id AND school_id = :school_id";
    $stmt = $conn->prepare($checkLikeQuery);
    $stmt->bindValue(':post_id', $postId, SQLITE3_INTEGER);
    $stmt->bindValue(':school_id', $schoolId, SQLITE3_INTEGER);
    $checkLikeResult = $stmt->execute();

    if (!$checkLikeResult->fetchArray()) {
        $likeQuery = "INSERT INTO info_likes (post_id, school_id) VALUES (:post_id, :school_id)";
        $stmt = $conn->prepare($likeQuery);
        $stmt->bindValue(':post_id', $postId, SQLITE3_INTEGER);
        $stmt->bindValue(':school_id', $schoolId, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Handle replies
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $postId = $_POST['post_id'];
    $studentId = $_SESSION['student_id'];
    $content = trim($_POST['content']);

    if (!empty($content)) {
        $replyQuery = "INSERT INTO info_replies (post_id, student_id, content) VALUES (:post_id, :student_id, :content)";
        $stmt = $conn->prepare($replyQuery);
        $stmt->bindValue(':post_id', $postId, SQLITE3_INTEGER);
        $stmt->bindValue(':student_id', $studentId, SQLITE3_INTEGER);
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Info Center</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        div.post {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        form {
            margin-top: 10px;
        }
        textarea {
            width: 100%;
            height: 60px;
        }
        .reply {
            background-color: #f9f9f9;
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Information Center</h1>
    <?php while ($post = $postsResult->fetchArray()): ?>
    <div class="post">
        <h2>Post by <?php echo htmlspecialchars($post['school_name']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        <p><small>Posted on <?php echo htmlspecialchars(humanReadableDate($post['created_at'])); ?></small></p>
        <p><strong>Likes: <?php echo $post['like_count']; ?></strong></p>

        <!-- Like Button -->
        <form method="post">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <button type="submit" name="like">Like</button>
        </form>

        <!-- Display Replies -->
        <h3>Replies:</h3>
        <?php
        $repliesQuery = "SELECT r.content, r.created_at, st.full_name
                         FROM info_replies r
                         JOIN students st ON r.student_id = st.id
                         WHERE r.post_id = :post_id
                         ORDER BY r.created_at ASC";
        $stmt = $conn->prepare($repliesQuery);
        $stmt->bindValue(':post_id', $post['id'], SQLITE3_INTEGER);
        $repliesResult = $stmt->execute();

        while ($reply = $repliesResult->fetchArray()) {
            echo "<div class='reply'>
                    <p>" . nl2br(htmlspecialchars($reply['content'])) . "</p>
                    <p><small>Replied by " . htmlspecialchars($reply['full_name']) . " on " . htmlspecialchars(humanReadableDate($reply['created_at'])) . "</small></p>
                  </div>";
        }
        ?>

        <!-- Reply Form -->
        <form method="post">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <textarea name="content" required></textarea>
            <button type="submit" name="reply">Reply</button>
        </form>
    </div>
    <hr>
    <?php endwhile; ?>
</body>
</html>