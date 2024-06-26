<?php
include "includes/db.php";
include "includes/prettify.php";

if (!isset($_GET["id"]) || !intval($_GET["id"])) {
    throw new Exception("Invalid user_id", 404);
}

$user_id = intval($_GET["id"]);
$user = $conn->query("SELECT * FROM users WHERE user_id=$user_id")->fetch_array();
$posts = $conn->query("SELECT post_id, author_user_id, title FROM posts")->fetch_all(MYSQLI_BOTH);
$comments = $conn->query("SELECT * FROM comments")->fetch_all(MYSQLI_BOTH);
$post_votes = $conn->query("SELECT * FROM post_votes")->fetch_all(MYSQLI_BOTH)
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> <?php echo prettify_username($user["username"]) ?> </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/users.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <div class="account-details">
        <img src="<?php echo $user["avatar_path"] ?>" width=180px>

        <table>
            <tr>
                <td> <h2><?php echo $user["username"] ?></h2> </td>

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

            </tr>
            <tr>
                <td> <?php echo $user["about_me"] ?> </td>
                <td>•</td>
                <td> Joined <?php echo prettify_datetime($user["join_datetime"]) ?> </td>
            </tr>
            <td><br></td>
            <tr>
                <td>
                    <?php
                         $amount = 0;

                         for ($i=0; $i < count($comments); $i++) {
                             $row = $comments[$i];

                             if ($row["author_user_id"] == $user_id) {
                                 $amount++;
                             }
                         }
                         
                         echo $amount . " Comment(s)";
                    ?>
                </td>
                <td>•</td>
                <td>
                    <!-- <?php 
                        $amount = 0;

                        for ($i=0; $i < count($post_votes); $i++) {
                            $row = $post_votes[$i];

                            if ($row["user_id"] == $user_id) {
                                if ($row["weight"] >= 1) {
                                    $amount++;
                                }
                            }
                        }

                        echo $amount . " Upvote(s) Given"
                    ?> -->

                    <?php 
                        $amount = 0;

                        for ($i=0; $i < count($posts); $i++) {
                            $row = $posts[$i];

                            if ($row['author_user_id'] == $user_id) {
                                $amount++;
                            }
                        }

                        echo $amount . " Post(s)"
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="account-activity">
        <div class="recent-posts"> 
            <h2>Recent Posts</h2>   
            <table>
                <?php
                for ($i=0; $i < count($posts); $i++) {
                    if ($i == 5) break;
                    $row = $posts[$i];

                    if ($row["author_user_id"] == $user_id) {
                        ?>
                        <tr class="form-post">
                            <td><a href="view_post.php?id=<?php echo $row["post_id"] ?>"><?php echo prettify_title($row["title"])?></a></td>
                        </tr>
                        <?php
                    }
                }
                
                ?>
            </table>
        </div>
    </div>

    <?php
    if ($_SESSION["administrator"]) {
        ?>
        <div class="users-administrator-options">
            <a href="admin.php?delete_user&user_id=<?php echo $user["user_id"] ?>"> Delete User </a>
            <a href="admin.php?ban_user&user_id=<?php echo $user["user_id"] ?>"> Ban User </a>
        </div>
        <?php
    }
    ?>

    <?php include "includes/footer.php" ?>
</body>

</html>