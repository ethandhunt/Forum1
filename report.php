<?php
include "includes/db.php";
include "includes/prettify.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SESSION["banned"]) {
    throw new Exception("Cannot access report page as a reported user");
}

if (isset($_POST["report_post"])) {
    $title_tor = mysqli_real_escape_string($conn, $_POST["title_tor"]);
    $body_tor = mysqli_real_escape_string($conn, $_POST["body_tor"]);
    $statement = mysqli_real_escape_string($conn, $_POST["statement"]);

    if (!intval($_POST["user_id_tor"])) {
        throw new Exception('Invalid user_id');
    }
    if (!intval($_GET["post_id"])) {
        throw new Exception('Invalid post_id');
    }
    $user_id_tor = intval($_POST["user_id_tor"]);
    $post_id = intval($_GET["post_id"]);

    $reporter_user_id = $_SESSION["user_id"];
    $reporter_ip = $_SERVER["REMOTE_ADDR"];
    $conn->query("INSERT INTO post_reports (title_tor, body_tor, user_id_tor, post_id, reporter_user_id, reporter_ip, statement, dismissed) VALUES ('$title_tor', '$body_tor', '$user_id_tor', '$post_id', '$reporter_user_id', '$reporter_ip', '$statement', 0)");
    header("Location: view_post.php?id=$post_id");
}

if (isset($_POST["report_comment"])) {
    $body_tor = mysqli_real_escape_string($conn, $_POST["body_tor"]);
    $statement = mysqli_real_escape_string($conn, $_POST["statement"]);

    if (!intval($_POST["user_id_tor"])) {
        throw new Exception('Invalid user_id');
    }
    if (!intval($_GET["comment_id"])) {
        throw new Exception('Invalid comment_id');
    }
    $user_id_tor = intval($_POST["user_id_tor"]);
    $comment_id = intval($_GET["comment_id"]);

    $reporter_user_id = $_SESSION["user_id"];
    $reporter_ip = $_SERVER["REMOTE_ADDR"];
    $conn->query("INSERT INTO comment_reports (body_tor, user_id_tor, comment_id, reporter_user_id, reporter_ip, statement, dismissed) VALUES ('$body_tor', '$user_id_tor', '$comment_id', '$reporter_user_id', '$reporter_ip', '$statement', 0)");
    $go_back = true;
}

if (isset($_GET["post_id"])) {
    if (!intval($_GET["post_id"])) {
        throw new Exception('Invalid post_id', 404);
    }
    $post_id = intval($_GET["post_id"]);

    $post = $conn->query("SELECT author_user_id, title, body, post_id FROM posts WHERE post_id=$post_id")->fetch_array();
    $author_id = $post["author_user_id"];
    $author = $conn->query("SELECT username, user_id FROM users WHERE user_id=$author_id")->fetch_array();
}

if (isset($_GET["comment_id"])) {
    if (!intval($_GET["comment_id"])) {
        throw new Exception("Invalid comment_id", 404);
    }
    $comment_id = intval($_GET["comment_id"]);

    $comment = $conn->query("SELECT author_user_id, body, post_id FROM comments WHERE comment_id=$comment_id")->fetch_array();
    $author_id = $comment["author_user_id"];
    $author = $conn->query("SELECT username, user_id FROM users WHERE user_id=$author_id")->fetch_array();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Report </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/report.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php
    if (isset($go_back) && $go_back) {
        ?>
        <script>
            history.go(-2) // go back to post after reporting comment
        </script>
        <?php
    }
    ?>

    <?php
    if (isset($_GET["comment_id"])) {
        ?>
        <form method="post" class="report-form">
            <table>
                <tr>
                    <th> username </th>
                    <th> user_id </th>
                    <th> comment </th>
                </tr>
                <tr>
                    <td> <?php echo $author["username"] ?> </td>
                    <td> <?php echo $author_id ?> </td>
                    <td> <?php echo prettify_body($comment["body"]) ?> </td>
                </tr>
            </table>
            <textarea name="statement" maxlength="1000"></textarea>
            <input type="submit" name="report_comment" value="Report" class="report-button">
            <input type="hidden" name="body_tor" value="<?php echo prettify_body($comment["body"]) ?>">
            <input type="hidden" name="user_id_tor" value="<?php echo prettify_body($comment["author_user_id"]) ?>">
        </form>
        <?php
    }

    if (isset($_GET["post_id"])) {
        ?>
        <form method="post" class="report-form">
            <table>
                <tr>
                    <th> username </th>
                    <th> user_id </th>
                    <th> post title </th>
                    <th> post body </th>
                    <th> post_id </th>
                </tr>
                <tr>
                    <td> <?php echo $author["username"] ?> </td>
                    <td> <?php echo $author_id ?> </td>
                    <td> <?php echo prettify_title($post["title"]) ?> </td>
                    <td> <?php echo prettify_body($post["body"]) ?> </td>
                    <td> <?php echo $post["post_id"] ?> </td>
                </tr>
            </table>
            <textarea name="statement" maxlength="1000" placeholder="Moderators will see information you enter here"></textarea>
            <input type="submit" name="report_post" value="Report" class="report-button">
            <input type="hidden" name="body_tor" value="<?php echo htmlentities($post["body"], ENT_QUOTES) ?>">
            <input type="hidden" name="title_tor" value="<?php echo htmlentities($post["title"], ENT_QUOTES) ?>">
            <input type="hidden" name="user_id_tor" value="<?php echo $post["author_user_id"] ?>">
        </form>
        <?php
    }
    ?>


    <?php include "includes/footer.php" ?>
</body>

</html>