<?php
include "includes/db.php";
include "includes/prettify.php";

if (!$_SESSION["moderator"]) {
    throw new Exception("Cant access page as a non-moderator");
}

if (!intval($_GET["id"])) {
    throw new Exception("Invalid report id");
}

$report_id = intval($_GET["id"]);
$report_query = $conn->query("SELECT * FROM post_reports WHERE report_id=$report_id");

if ($report_query->num_rows != 1) {
    throw new Exception("Could not find report with id $report_id");
}

$report = $report_query->fetch_array();

if (isset($_POST["delete_report"])) {
    $conn->query("DELETE FROM post_reports WHERE report_id=$report_id");
    header("Location: reports.php");
    exit(0);
}

if (isset($_POST["dismiss_report"])) {
    $conn->query("UPDATE post_reports SET dismissed=1 WHERE report_id=$report_id");
    header("Location: reports.php");
    exit(0);
}

if (isset($_POST["delete_post"])) {
    $post_id = $report["post_id"];
    $conn->query(
        "DELETE posts, comments, post_votes FROM posts " .
        "INNER JOIN comments ON comments.post_id=posts.post_id " .
        "INNER JOIN post_votes ON post_votes.post_id=posts.post_id" .
        "WHERE posts.post_id=$post_id"
    );
    $conn->query("UPDATE post_reports SET dismissed=1 WHERE report_id=$report_id");
    header("Location: reports.php");
    exit(0);
}

if (isset($_POST["ban"])) {
    $user_id = $report["user_id_tor"];
    $conn->query("UPDATE users SET banned=1 WHERE user_id=$user_id");
}

$reported_user_id = $report["user_id_tor"];
$reported_user_query = $conn->query("SELECT * FROM users WHERE user_id=$reported_user_id");
$reported_user = $reported_user_query->fetch_array();

$invalid_reported_user = $reported_user_query->num_rows != 1;

$reporter_user_id = $report["reporter_user_id"];
$reporter_user_query = $conn->query("SELECT * FROM users WHERE user_id=$reporter_user_id");
$reporter_user = $reporter_user_query->fetch_array();

$invalid_reporter_user = $reporter_user_query->num_rows != 1;

$post_id = $report["post_id"];
$post_query = $conn->query("SELECT * FROM posts WHERE post_id=$post_id");

$invalid_post = $post_query->num_rows != 1;

$post = $post_query->fetch_array();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Post report </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/view_post_report.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <div class="report-details">
        <h2> Reported post </h2>
        <table class="reported-post-table">
            <tr>
                <th> Title </th>
                <th> Body </th>
                <th> Post_id </th>
                <th> Report date </th>
                <?php
                if (!$invalid_post) {
                    ?>
                    <th title="date that the post was made"> Post date </th>
                    <?php
                }
                ?>
            </tr>
            <tr>
                <td> <?php echo prettify_title($report["title_tor"]) ?> </td>
                <td> <?php echo prettify_body($report["body_tor"]) ?> </td>
                <td> <?php echo $report["post_id"] ?> </td>
                <td> <?php echo prettify_datetime($report["report_timestamp"]) ?> </td>
                <?php
                if (!$invalid_post) {
                    ?>
                    <td> <?php echo prettify_datetime($post["timestamp"]) ?> </td>
                    <?php
                }
                ?>
            </tr>
        </table>
        <h2> Statement </h2>
        <div class="statement">
            <?php echo prettify_body($report["statement"]) ?>
        </div>
        <?php
        if (!$invalid_reported_user) {
            ?>
            <h2> Reported user </h2>
            <table class="reported-user-table">
                <tr>
                    <th> username </th>
                    <th> user_id </th>
                    <th> join date </th>
                    <th> registration ip </th>
                    <th> banned </th>
                </tr>
                <tr>
                    <td> <?php echo prettify_username($reported_user["username"]) ?> </td>
                    <td> <?php echo $report["user_id_tor"] ?> </td>
                    <td> <?php echo prettify_datetime($reported_user["join_datetime"]) ?> </td>
                    <td> <?php echo $reported_user["register_address"] ?> </td>
                    <td>
                        <?php
                        if ($reported_user["banned"]) {
                            echo "banned";
                        } else {
                            echo "not";
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        }
        
        if (!$invalid_reporter_user) {
            ?>
            <h2> Reporting user </h2>
            <table class="reporter-user-table">
                <tr>
                    <th> username </th>
                    <th> user_id </th>
                    <th> join date </th>
                    <th> registration ip </th>
                    <th title="the ip of this user when they reported this post"> report ip </th>
                    <th> banned </th>
                </tr>
                <tr>
                    <td> <?php echo prettify_username($reporter_user["username"]) ?> </td>
                    <td> <?php echo $report["reporter_user_id"] ?> </td>
                    <td> <?php echo prettify_datetime($reporter_user["join_datetime"]) ?> </td>
                    <td> <?php echo $reporter_user["register_address"] ?> </td>
                    <td> <?php echo $report["reporter_ip"] ?> </td>
                    <td>
                        <?php
                        if ($reporter_user["banned"]) {
                            echo "banned";
                        } else {
                            echo "not";
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>
        <form method="post" class="action-form">
            <input type="submit" name="delete_report" value="Delete report" class="button-danger">
            <input type="submit" name="dismiss_report" value="Dismiss report" class="button-warning">
            <input type="submit" name="ban" value="Ban reported user" class="button-danger">
            <?php
            if (!$invalid_post) {
                ?>
                <input type="submit" name="delete_post" value="Delete post" class="button-warning">
                <?php
            }
            ?>
        </form>
    </div>
    <img class="goober" src="images/report_companion.png" title="goober watches you" width=300>

    <?php include "includes/footer.php" ?>
</body>

</html>