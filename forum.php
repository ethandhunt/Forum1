<?php
include "includes/db.php";
include "includes/prettify.php";

$user_id = $_SESSION["user_id"];
$likes = $conn->query("SELECT * FROM post_votes")->fetch_all(MYSQLI_BOTH);
$comments = $conn->query("SELECT comment_id, post_id, body, timestamp FROM comments")->fetch_all(MYSQLI_BOTH);
$posts = $conn->query("SELECT * FROM posts")->fetch_all(MYSQLI_BOTH);
$online_users = $conn->query("SELECT * FROM users WHERE last_online > NOW() - INTERVAL 1 MINUTE")->fetch_all(MYSQLI_BOTH);

function can_vote($post_id, $type) {
    global $likes, $user_id;

    if ($_SESSION["banned"]) {
        return false;
    }

    if ($type == "up") {
        $weight = 1;
    } elseif ($type == "down") {
        $weight = -1;
    } else {
        throw new Exception("Incorrect vote type");
    }

    $result = true;
    for ($i=0; $i < count($likes); $i++) {
        $row = $likes[$i];
        if ($row["user_id"] == $user_id && $row["post_id"] == $post_id && $row["weight"] == $weight) {
            $result = false;
            break;
        }
    }
    
    return $result;
}

function get_likes($post_id) {
    global $likes;

    $total = 0;
    for ($i=0; $i < count($likes); $i++) {
        $row = $likes[$i];
        if ($row["post_id"] == $post_id) {
            $total += $row["weight"];
        }
    }
    return $total;
}

function get_comments($post_id) {
    global $comments;

    $total = 0;
    for ($i=0; $i < count($comments); $i++) {
        $row = $comments[$i];
        if ($row["post_id"] == $post_id) {
            $total++;
        }
    }
    return $total;
}

function get_mentions($post_id) {
    global $comments, $posts;

    $total = 0;
    for ($i=0; $i < count($comments); $i++) {
        $row = $comments[$i];
        if ($row["post_id"] == $post_id && str_contains($row["body"], "@" . $_SESSION["username"])) {
            $total++;
        }
    }
    for ($i=0; $i < count($posts); $i++) {
        $row = $posts[$i];
        if ($row["post_id"] == $post_id && str_contains($row["body"], "@" . $_SESSION["username"])) {
            $total++;
        }
    }
    return $total;
}

function comment_recency($post_id) {
    global $comments, $posts;

    $most_recent = 0;
    for ($i=0; $i < count($posts); $i++) {
        $row = $posts[$i];
        if ($row["post_id"] == $post_id) {
            $most_recent = strtotime($row["timestamp"]);
        }
    }

    for ($i=0; $i < count($comments); $i++) {
        $row = $comments[$i];
        if ($row["post_id"] == $post_id && strtotime($row["timestamp"]) > $most_recent) {
            $most_recent = strtotime($row["timestamp"]);
            // var_dump($row["body"], $row["timestamp"]);
        }
    }
    return $most_recent;
}

function most_recent_comment($post_id) {
    global $comments;
    $most_recent = 0;
    $comment = NULL;
    for ($i=0; $i < count($comments); $i++) {
        $row = $comments[$i];
        if ($row["post_id"] == $post_id && strtotime($row["timestamp"]) > $most_recent) {
            $most_recent = strtotime($row["timestamp"]);
            $comment = $row;
            // var_dump($row["body"], $row["timestamp"]);
        }
    }
    return $comment;
}


if (isset($_POST["vote"])) {
    $post_id = $_POST["post_id"];
    $type = $_POST["vote_type"];
    if (can_vote($post_id, $type)) {
        $conn->query("DELETE FROM post_votes WHERE user_id=$user_id AND post_id=$post_id");

        if ($type == "up") {
            $weight = 1;
        } elseif ($type == "down") {
            $weight = -1;
        } else {
            throw new Exception("Incorrect vote type");
        }

        $conn->query("INSERT INTO post_votes (user_id, post_id, weight) VALUES ($user_id, $post_id, $weight)");
    }
}

if (isset($_GET["sortby"])) {
    $_SESSION["sortby"] = $_GET["sortby"];
}

if (isset($_POST["mark_read"])) {
    for ($i=0; $i < count($posts); $i++) {
        $row = $posts[$i];
        if (!is_null(most_recent_comment($row["post_id"]))) {
            $_SESSION["read_posts"][$row["post_id"]] = most_recent_comment($row["post_id"])["comment_id"];
        } else {
            $_SESSION["read_posts"][$row["post_id"]] = NULL;
        }
    }
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Forum </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/forum.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="includes/vote.js"></script>
</head>

<body>
    <?php include "includes/header.php" ?>

    <div class="pre-post-table">
        <div class="pre-left">
            <form class="sortby-form">
                <input type="submit" name="sortby" value="Sort by:">
                <select name="sortby" title="press [Sort by:] to sort">
                    <option <?php if ($_SESSION["sortby"] == 'votes') {echo"selected";}?>> votes </option>
                    <option <?php if ($_SESSION["sortby"] == 'recent') {echo"selected";}?>> recent </option>
                    <option <?php if ($_SESSION["sortby"] == 'mentions') {echo"selected";}?>> mentions </option>
                    <option <?php if ($_SESSION["sortby"] == 'comments') {echo"selected";}?>> comments </option>
                    <option <?php if ($_SESSION["sortby"] == 'recent comments') {echo"selected";}?>> recent comments </option>
                </select>
            </form>

            <form class="mark-read-form" method="post">
                <input type="submit" name="mark_read" value="Mark all as read">
            </form>
        </div>

        <div class="online-div pre-right">
            Online users (<?php echo count($online_users) ?>): 
            <?php
            foreach ($online_users as $x) {
                $y = $x["username"];
                echo "<div class='online-user'>$y</div>";
            }
            ?>
        </div>
    </div>

    <table class="forum-table">
    <?php
    $posts_arr = array();
    $pin_arr = array();
    $likes_arr = array();
    $time_arr = array();
    $mentions_arr = array();
    $comments_arr = array();
    $comment_recency_arr = array();
    for ($i=0; $i < count($posts); $i++) {
        $row = $posts[$i];
        $post_user_id = $row["author_user_id"];
        $user = $conn->query("SELECT * FROM users WHERE user_id=$post_user_id")->fetch_array();
        $posts_arr[$i] = array(
            'post_id' => $row['post_id'],
            'username' => $user['username'],
            'user_id' => $row['author_user_id'],
            'title' => prettify_title($row['title']),
            'likes' => get_likes($row['post_id']),
            'comments' => get_comments($row['post_id']),
            'most_recent_comment' => most_recent_comment($row['post_id']),
            'timestamp' => $row['timestamp'],
            'timestamp_pretty' => prettify_timestamp(strtotime($row['timestamp'])),
            'mentions' => get_mentions($row['post_id']),
            'pinned' => $row["pinned"]
        );
        $pin_arr[$i] = $row["pinned"];
        $likes_arr[$i] = get_likes($row['post_id']);
        $time_arr[$i] = strtotime($row['timestamp']);
        $mentions_arr[$i] = get_mentions($row['post_id']);
        $comments_arr[$i] = get_comments($row['post_id']);
        $comment_recency_arr[$i] = comment_recency($row['post_id']);
        // var_dump($row['title'], comment_recency($row['post_id']));
    }


    $sortby = 'votes';

    if (isset($_SESSION['sortby'])) {
        $sortby = $_SESSION['sortby'];
    }
    
    if ($sortby == 'recent') {
        array_multisort($pin_arr, SORT_DESC, $time_arr, SORT_DESC, SORT_NUMERIC, $posts_arr);
    } elseif ($sortby == 'votes') {
        array_multisort($pin_arr, SORT_DESC, $likes_arr, SORT_DESC, SORT_NUMERIC, $posts_arr);
    } elseif ($sortby == 'mentions') {
        array_multisort($pin_arr, SORT_DESC, $mentions_arr, SORT_DESC, SORT_NUMERIC, $posts_arr);
    } elseif ($sortby == 'comments') {
        array_multisort($pin_arr, SORT_DESC, $comments_arr, SORT_DESC, SORT_NUMERIC, $posts_arr);
    } elseif ($sortby == 'recent comments') {
        array_multisort($pin_arr, SORT_DESC, $comment_recency_arr, SORT_DESC, SORT_NUMERIC, $posts_arr);
    }

    for ($i=0; $i < count($posts_arr); $i++) {
        $post = $posts_arr[$i];
        
        $upvote_append_class = "";
        if (can_vote($post["post_id"], "up")) {
            $upvote_append_class = "can_vote";
        }
        $downvote_append_class = "";
        if (can_vote($post["post_id"], "down")) {
            $downvote_append_class = "can_vote";
        }

        $anchor_append_class = " unread";
        if (
            // post was marked as read
            array_key_exists($post["post_id"], $_SESSION["read_posts"]) && (
                // most recent comment is the same
                isset($post["most_recent_comment"]) && $_SESSION["read_posts"][$post["post_id"]] == $post["most_recent_comment"]["comment_id"]
                // post doesn't have any comments
                || is_null($post["most_recent_comment"])
            )
        ) {
            $anchor_append_class = "";
        }
        ?>
        <!-- <tr class="forum-post-link">

            <td class="forum-post-pinned">
                <?php
                if($post["pinned"]) {
                    ?>
                    <i class="fa fa-map-pin"></i>
                    <?php
                }
                ?>
            </td> 
            <td class="forum-post-username"> <a href="users.php?id=<?php echo $post["user_id"] ?>"> <?php echo htmlentities($post["username"], ENT_QUOTES) ?> </a> </td>
            <td class="forum-post-mentions<?php if($post["mentions"]>0) {echo " mentioned";} ?>"> @<?php echo $post["mentions"] ?> </td>
            <td class="forum-post-title"> <a href="<?php echo "view_post.php?id=" . $post["post_id"] ?>" class="forum-post-link<?php echo $anchor_append_class ?>"> <?php echo $post["title"] ?> </a> </td>
            <td class="forum-post-timestamp"> <?php echo $post["timestamp_pretty"] ?> </td>
            <td class="forum-post-comments"> <?php echo $post["comments"] ?> comments </td>
            <td class="forum-post-likes" id="likes-<?php echo $post["post_id"] ?>"> <?php echo $post["likes"] ?> </td>
            <td> <i class="fa fa-caret-up vote <?php echo $upvote_append_class ?>" id="upvote-<?php echo $post["post_id"]?>" onclick="vote(<?php echo $post["post_id"]?>,'up')"></i> </td>
            <td> <i class="fa fa-caret-down vote <?php echo $downvote_append_class ?>" id="downvote-<?php echo $post["post_id"]?>" onclick="vote(<?php echo $post["post_id"]?>,'down')"></i> </td>
        </tr> -->

        <tr class="forum-post-link">
            <td class="forum-post-pinned">
                <?php
                if($post["pinned"]) {
                    ?>
                    <i class="fa fa-map-pin"></i>
                    <?php
                }
                ?>
            </td> 
            <!-- <td class="form-post-user-image"><img src="<?php echo $user["avatar_path"] ?>" width=45px></td> -->
            <td class="forum-post-username"> <a href="users.php?id=<?php echo $post["user_id"] ?>"><?php echo $post["username"] ?></a> </td>
            <td>        
                <?php
                    if ($user["banned"]) {
                        echo "(banned)";
                    } else {
                        if ($user["administrator"]) {
                            echo "(Administrator)";
                        } else {
                            if ($user["moderator"]) {
                                echo "(Moderator)";
                            } 
                        }
                    }
                ?>
            </td>
            <td>•</td>
            <td class="forum-post-title"> <a href="<?php echo "view_post.php?id=" . $post["post_id"] ?>" class="forum-post-link<?php echo $anchor_append_class ?>"> <?php echo $post["title"] ?> </a> </td>
            <td class="forum-post-mentions<?php if($post["mentions"]>0) {echo " mentioned";} ?>"> @<?php echo $post["mentions"] ?> </td>
            <td>•</td>
            <td class="forum-post-timestamp"> <?php echo $post["timestamp_pretty"] ?> </td>
            <td>•</td>
            <td class="forum-post-comments"> <?php echo $post["comments"] ?> Comments </td>
            <td>•</td>
            <td class="forum-post-likes" id="likes-<?php echo $post["post_id"] ?>"> <?php echo $post["likes"] ?> </td>
            <td> <i class="fa fa-caret-up vote <?php echo $upvote_append_class ?>" id="upvote-<?php echo $post["post_id"]?>" onclick="vote(<?php echo $post["post_id"]?>,'up')"></i> </td>
            <td> <i class="fa fa-caret-down vote <?php echo $downvote_append_class ?>" id="downvote-<?php echo $post["post_id"]?>" onclick="vote(<?php echo $post["post_id"]?>,'down')"></i> </td>

        </tr>

        <?php
    }
    ?>
    </table>

    <?php include "includes/footer.php" ?>
</body>

</html>