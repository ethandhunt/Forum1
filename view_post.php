<?php

include "includes/db.php";
include "includes/prettify.php";

if (!isset($_GET["id"])) {
    header("Location: index.php");
}

if (!intval($_GET["id"])) {
    throw new Exception("Invalid post id parameter", 404);
}

if (isset($_POST["comment"])) {
    $body = mysqli_real_escape_string($conn, $_POST["body"]);
    $image_href = mysqli_real_escape_string($conn, $_POST["image_href"]);
    $author_id = $_SESSION["user_id"];
    $post_id = intval($_GET["id"]);

    if (!intval($post_id)) {
        throw new Exception('Invalid post_id', 404);
    } elseif ($_SESSION["banned"]) {
        throw new Exception("Cannot edit comment while banned");
    } else {
        $conn->query("INSERT INTO comments (body, author_user_id, post_id, edited, image_href) VALUES ('$body', $author_id, $post_id, 0, '$image_href')");
    }
}

if (isset($_POST["block_user"])) {
    $blocked_user_id = $_POST["blocked_user_id"];
    $user_id = $_SESSION["user_id"];

    if (!intval($blocked_user_id)) {
        throw new Exception("Invalid user_id", 404);
    } elseif ($_SESSION["banned"]) {
        throw new Exception("Cannot block users while banned");
    } else {
        $blocked_user = $conn->query("SELECT blocked_user_id FROM blocked_users WHERE user_id=$user_id")->fetch_array();

        if ($blocked_user['blocked_user_id'] == $blocked_user_id) {
            throw new Exception("Cannot block user more than once");
        }
    }

    $conn->query("INSERT INTO blocked_users (user_id, blocked_user_id) VALUES ('$user_id', '$blocked_user_id')");
}

if (isset($_POST["edit_comment"])) {
    $body = mysqli_real_escape_string($conn, $_POST["body"]);
    $image_href = mysqli_real_escape_string($conn, $_POST["image_href"]);
    $comment_id = $_POST["comment_id"];

    if (!intval($comment_id)) {
        throw new Exception('Invalid comment_id', 404);
    } else {
        $comment = $conn->query("SELECT author_user_id FROM comments WHERE comment_id=$comment_id")->fetch_array();

        if ($comment["author_user_id"] != $_SESSION["user_id"]) {
            throw new Exception("Cannot edit another users comment", 401);
        } elseif ($_SESSION["banned"]) {
            throw new Exception("Cannot edit comment while banned");
        } else {
            $conn->query("UPDATE comments SET body='$body', edited=1, image_href='$image_href' WHERE comment_id=$comment_id");
        }
    }
}

if (isset($_POST["delete_comment"])) {
    $comment_id = $_POST["comment_id"];

    if (!intval($comment_id)) {
        throw new Exception('Invalid comment id', 404);
    } else {
        $comment = $conn->query("SELECT author_user_id FROM comments WHERE comment_id=$comment_id")->fetch_array();

        if ($comment["author_user_id"] != $_SESSION["user_id"] && !$_SESSION["moderator"]) {
            throw new Exception("Cannot delete another users comment", 401);
        } else {
            $conn->query("DELETE FROM comments WHERE comment_id=$comment_id");
        }
    }
}

if (isset($_POST["edit_post"])) {
    $post_id = intval($_GET["id"]);
    $body = mysqli_real_escape_string($conn, $_POST["body"]);
    $image_href = mysqli_real_escape_string($conn, $_POST["image_href"]);

    // double check post_id for no real reason
    if (!intval($post_id)) {
        throw new Exception('Invalid post id', 404);
    } else {
        $post = $conn->query("SELECT author_user_id FROM posts WHERE post_id=$post_id")->fetch_array();

        if ($post["author_user_id"] != $_SESSION["user_id"]) {
            throw new Exception("Cannot edit another users post", 401);
        } elseif ($_SESSION["banned"]) {
            throw new Exception("Cannot edit comment while banned");
        } else {
            $conn->query("UPDATE posts SET body='$body', edited=1, image_href='$image_href' WHERE post_id=$post_id");
        }
    }
}

if (isset($_POST["delete_post"])) {
    $post_id = intval($_GET["id"]);

    if (!intval($post_id)) {
        throw new Exception('Invalid post id', 404);
    }
    $post = $conn->query("SELECT author_user_id FROM posts WHERE post_id=$post_id")->fetch_array();

    if ($post["author_user_id"] != $_SESSION["user_id"] && !$_SESSION["moderator"]) {
        throw new Exception("Cannot delete another users post", 401);
    }
    // $conn->query(
    //     "DELETE posts, comments, post_votes FROM posts " .
    //     "INNER JOIN comments ON comments.post_id=posts.post_id " .
    //     "INNER JOIN post_votes ON post_votes.post_id=posts.post_id" .
    //     "WHERE posts.post_id=$post_id"
    // );
    $conn->query("DELETE FROM posts WHERE post_id=$post_id");
    $conn->query("DELETE FROM comments WHERE post_id=$post_id");
    $conn->query("DELETE FROM post_votes WHERE post_id=$post_id");
}

if (isset($_POST["pin_post"])) {
    echo "fired pin_post";
    $post_id = intval($_GET["id"]);
    $pin_value = "0";

    if ($_POST["pin_value"] == "1") {
        $pin_value = "1";
    }
    if ($_POST["pin_value"] == "0") {
        $pin_value = "0";
    }

    if (!intval($post_id)) {
        throw new Exception("Invalid post id", 404);
    }

    if (!$_SESSION["moderator"]) {
        throw new Exception("Cannot pin post as non-moderator", 401);
    }

    $conn->query("UPDATE posts SET pinned=$pin_value WHERE post_id=$post_id");
}
?>

<!DOCTYPE html>
<html lang="en">

<?php
$post_id = intval($_GET["id"]);
$post = $conn->query("SELECT * FROM posts WHERE post_id=$post_id")->fetch_array();
if (is_null($post)) {
    header('Location: forum.php');
}
$author_id = $post["author_user_id"];
$author = $conn->query("SELECT * FROM users WHERE user_id=$author_id")->fetch_array();
$comments = $conn->query("SELECT * FROM comments WHERE post_id=$post_id")->fetch_all(MYSQLI_BOTH);
$blocked_users = $conn->query("SELECT * FROM blocked_users WHERE user_id=$user_id")->fetch_array();

if (count($comments) > 0) {
    $_SESSION["read_posts"][$post_id] = $comments[count($comments)-1]["comment_id"];
} else {
    $_SESSION["read_posts"][$post_id] = NULL;
}

$username_append_classes = "";
if ($author["moderator"]) {
    $username_append_classes = $username_append_classes . " moderator-username";
}
if ($author["administrator"]) {
    $username_append_classes = $username_append_classes . " administrator-username";
}
?>

<head>
    <title> <?php echo htmlentities($post["title"], ENT_QUOTES) ?> </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style/view_post.css">
    <script src="includes/edit.js"></script>
</head>

<body>
    <?php include "includes/header.php" ?>

    <!-- <div class="post-title">
        <?php echo prettify_title($post["title"]) ?>
        -
        <?php echo prettify_datetime($post["timestamp"]) ?>
    </div> -->

    <div hidden id="post-pinned"><?php echo $post["pinned"] ?></div>

    <div class="post-title">
         <?php echo ($post["title"]) ?>
         -
         <?php echo prettify_datetime($post["timestamp"]) ?>
    </div> 

    <div class="post">
        <div class="post-top">
            <img src="<?php echo $author["avatar_path"] ?>" class="post-avatar">
    
            <table>
                <tr>
                    <td class="post-username"> 
                        <a href="users.php?id=<?php echo $author_id?>">
                             <h2><?php echo prettify_username($author["username"]) ?></h2>
                        </a>       
                    </td>
                                    
                    <td class="post-author-rank">        
                        <?php
                            if ($author["banned"]) {
                                echo "(banned)";
                            } else {
                                if ($author["administrator"]) {
                                    echo "(Administrator)";
                                } else {
                                    if ($author["moderator"]) {
                                        echo "(Moderator)";
                                    } 
                                }
                            }
                        ?>
                    </td>
                    <td>•</td>
                    <td>
                        <?php
                            if ($author_id == $_SESSION["user_id"] && !$_SESSION["banned"]) {
                                ?>
                                <button class="edit-button" onclick="edit_post(<?php echo $post_id ?>)"><i class="fa fa-edit"></i>Edit</button>
                                <?php
                            }
                            if ($author_id != $_SESSION["user_id"]) {
                                ?>
                                <button class="block-button" onclick="block_user(<?php echo $author_id ?>)"><i class="fa fa-ban"></i>Block</button>
                                <?php
                            }
                            if ($author_id == $_SESSION["user_id"] || $_SESSION["moderator"]) {
                                ?>
                                <button class="delete-button" onclick="delete_post(<?php echo $post_id ?>)"><i class="fa fa-trash"></i>Delete</button>
                                <?php
                            }
                            if ($_SESSION["moderator"]) {
                                ?>
                                <button class="pin-button" onclick="pin_post()"><i class="fa fa-map-pin"></i>Pin</button>
                                <?php
                            }
                            if (!$_SESSION["banned"]) {
                                ?>
                                <button class="report-anchor" href="report.php?post_id=<?php echo $post_id ?>"><i class="fa fa-flag"></i>Report</button>
                                <?php
                            }
                        ?>
                    </td>
                </tr>
                                    
                <tr class="post-about-me">
                    <td> <?php echo $author["about_me"] ?> </td>
                </tr>
            </table>
        </div>

        <div class="post-bottom">
            <div class="post-body" id="post-body">
                <div class="post-body-body">
                    <?php echo prettify_body($post["body"]) ?>
                </div>

                <?php
                if ($post["edited"]) {
                    ?>
                    <br>
                    <span class="edited"> (edited) </span>
                    <?php
                }
                ?>

            </div>

            <?php
            if ($author_id == $_SESSION["user_id"]) {
                ?>
                <form class="edit-post-form" method="post" id="edit-post-form" hidden>
                    <textarea class="scripted-textarea" name="body" id="edit-post-body" placeholder="Type Comment Here.."><?php echo htmlentities($post["body"], ENT_QUOTES) ?></textarea>
                    <input type="text" name="image_href" placeholder="Image URL" value="<?php echo htmlentities($post["image_href"], ENT_QUOTES) ?>">
                    <input type="submit" name="edit_post" value="Edit">
                </form>
                <?php
            }
            ?>

            <!-- <div class="post-right">
                <div class="post-modify">
                <?php
                if ($post["image_href"] != "") {
                    ?>
                    <div class="post-image">
                        <img src="<?php echo filter_var(htmlentities($post["image_href"], ENT_QUOTES), FILTER_SANITIZE_URL) ?>">
                    </div>
                    <?php
                }
                ?>
            </div> -->
        </div>
    </div>
    <br>
    <?php
    if (count($comments) > 0) {
        ?>
        <div class="comment-title">
            <?php echo prettify_title("Comments") ?>
            <?php
                $amount = 0;
                for ($i=0; $i < count($comments); $i++) {
                    $amount++;
                }

                echo "(" . $amount . ")";
            ?>
        </div>

        <div class="comments">
            <?php
                for ($i=0; $i < count($comments); $i++) {
                    $row = $comments[$i];
                    $comment_author_id = $row["author_user_id"];
                    $comment_author = $conn->query("SELECT * FROM users WHERE user_id=$comment_author_id")->fetch_array();
                    ?>

                    <?php 
                        if ($blocked_users["blocked_user_id"] == $comment_author_id) {
                            ?>
                            <div class="blocked-post-comment">
                                <div class="blocked-post-top">
                                    <table>
                                        <tr>
                                            <td class="post-username"> 
                                                <h3><?php echo prettify_username("Blocked Comment") ?></h3>      
                                            </td>
                                            <td>•</td>
                                            <td>
                                                <button id="show-comment" onclick="show_comment(<?php echo $row["comment_id"] ?>)">Show Comment</button>
                                            </td>
                                            <td>•</td>
                                            <td>
                                                <?php
                                                    if ($comment_author_id != $_SESSION["user_id"]) {
                                                        ?>
                                                        <button class="block-button" onclick="unblock_user(<?php echo $comment_author_id ?>)"><i class="fa fa-ban"></i>Unblock</button>
                                                        <?php
                                                    }
                                                    if ($comment_author_id == $_SESSION["user_id"] || $_SESSION["moderator"]) {
                                                        ?>
                                                        <button class="delete-button" onclick="delete_comment(<?php echo $row["comment_id"] ?>)"><i class="fa fa-trash"></i>Delete</button>
                                                        <?php
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div style="display: none;" id="post-comment-<?php echo $row["comment_id"] ?>" class="post-comment post">
                                <div class="post-top">
                                    <img src="<?php echo $author["avatar_path"] ?>" class="post-avatar">

                                    <table>
                                        <tr>
                                            <td class="post-username"> 
                                                <a href="users.php?id=<?php echo $comment_author_id?>">
                                                     <h2><?php echo prettify_username($comment_author["username"]) ?></h2>
                                                </a>       
                                            </td>

                                            <td class="post-author-rank">        
                                                <?php
                                                    if ($comment_author["banned"]) {
                                                        echo "(banned)";
                                                    } else {
                                                        if ($comment_author["administrator"]) {
                                                            echo "(Administrator)";
                                                        } else {
                                                            if ($comment_author["moderator"]) {
                                                                echo "(Moderator)";
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </td>
                                            <td>•</td>
                                            <td class="post-timestamp"><?php echo prettify_timestamp(strtotime($row["timestamp"])) ?></td>
                                        </tr>

                                        <tr class="post-about-me">
                                            <td> <?php echo $comment_author["about_me"] ?> </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="post-bottom">
                                    <div class="post-body" id="comment-body-<?php echo $row["comment_id"] ?>">
                                        <div class="post-body-body">
                                            <?php echo prettify_body($row["body"]) ?>
                                        </div>
                                        <?php
                                        if ($row["edited"]) {
                                            ?>
                                            <br>
                                            <span class="edited"> (edited) </span>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    
                                    <?php
                                    if ($comment_author_id == $_SESSION["user_id"]) {
                                        ?>
                                        <form class="edit-comment-form" method="post" id="edit-comment-form-<?php echo $row["comment_id"]?>" hidden>
                                            <textarea class="scripted-textarea" name="body" id="edit-comment-body-<?php echo $row["comment_id"] ?>"><?php echo htmlentities($row["body"], ENT_QUOTES) ?></textarea>
                                            <input type="text" name="image_href" placeholder="Image URL" value="<?php echo htmlentities($row["image_href"], ENT_QUOTES) ?>">
                                            <input type="submit" name="edit_comment" value="Edit">
                                            <input type="hidden" name="comment_id" value="<?php echo $row["comment_id"] ?>">
                                        </form>
                                        <?php
                                        }
                                    ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="post-comment post">
                                <div class="post-top">
                                    <img src="<?php echo $author["avatar_path"] ?>" class="post-avatar">

                                    <table>
                                        <tr>
                                            <td class="post-username"> 
                                                <a href="users.php?id=<?php echo $comment_author_id?>">
                                                     <h2><?php echo prettify_username($comment_author["username"]) ?></h2>
                                                </a>       
                                            </td>

                                            <td class="post-author-rank">        
                                                <?php
                                                    if ($comment_author["banned"]) {
                                                        echo "(banned)";
                                                    } else {
                                                        if ($comment_author["administrator"]) {
                                                            echo "(Administrator)";
                                                        } else {
                                                            if ($comment_author["moderator"]) {
                                                                echo "(Moderator)";
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </td>
                                            <td>•</td>
                                            <td class="post-timestamp"><?php echo prettify_timestamp(strtotime($row["timestamp"])) ?></td>
                                            <td>•</td>
                                            <td>
                                                <?php
                                                    if ($comment_author_id == $_SESSION["user_id"] && !$_SESSION["banned"]) {
                                                        ?>
                                                        <button class="edit-button" onclick="edit_comment(<?php echo $row["comment_id"] ?>)"><i class="fa fa-edit"></i>Edit</button>
                                                        <?php
                                                    }
                                                    if ($comment_author_id != $_SESSION["user_id"]) {
                                                        ?>
                                                        <button class="block-button" onclick="block_user(<?php echo $comment_author_id ?>)"><i class="fa fa-ban"></i>Block</button>
                                                        <?php
                                                    }
                                                    if ($comment_author_id == $_SESSION["user_id"] || $_SESSION["moderator"]) {
                                                        ?>
                                                        <button class="delete-button" onclick="delete_comment(<?php echo $row["comment_id"] ?>)"><i class="fa fa-trash"></i>Delete</button>
                                                        <?php
                                                    }
                                                    if (!$_SESSION["banned"]) {
                                                        ?>
                                                        <a class="report-anchor" href="report.php?comment_id=<?php echo $row["comment_id"] ?>"><i class="fa fa-flag"></i>Report</a>
                                                        <?php
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                                        
                                        <tr class="post-about-me">
                                            <td> <?php echo $comment_author["about_me"] ?> </td>
                                        </tr>
                                    </table>
                                </div>
                                                        
                                <div class="post-bottom">
                                    <div class="post-body" id="comment-body-<?php echo $row["comment_id"] ?>">
                                        <div class="post-body-body">
                                            <?php echo prettify_body($row["body"]) ?>
                                        </div>
                                        <?php
                                        if ($row["edited"]) {
                                            ?>
                                            <br>
                                            <span class="edited"> (edited) </span>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    
                                    <?php
                                    if ($comment_author_id == $_SESSION["user_id"]) {
                                        ?>
                                        <form class="edit-comment-form" method="post" id="edit-comment-form-<?php echo $row["comment_id"]?>" hidden>
                                            <textarea class="scripted-textarea" name="body" id="edit-comment-body-<?php echo $row["comment_id"] ?>"><?php echo htmlentities($row["body"], ENT_QUOTES) ?></textarea>
                                            <input type="text" name="image_href" placeholder="Image URL" value="<?php echo htmlentities($row["image_href"], ENT_QUOTES) ?>">
                                            <input type="submit" name="edit_comment" value="Edit">
                                            <input type="hidden" name="comment_id" value="<?php echo $row["comment_id"] ?>">
                                        </form>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
        <?php
        }
    }

    if (!$_SESSION["banned"]) {
        ?>
        <form class="comment-form" method="post">
            <textarea class="scripted-textarea" name="body" id="body"></textarea>
            <input type="submit" name="comment" value="Comment">
        </form>
        <?php
    }
    ?>

    <script>
        // prevent confirm form resubmission dialogue
        if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
        }
        
        let textareas = document.getElementsByClassName('scripted-textarea')
        // resize comment textarea
        for (let i=0; i < textareas.length; i++) {
            textareas[i].addEventListener("input", function (e) {
                this.style.height = "auto";
                this.style.height = this.scrollHeight + "px";
            });
        }
    </script>

    <?php include "includes/footer.php" ?>
</body>

</html>