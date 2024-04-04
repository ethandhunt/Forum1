<?php
include "includes/db.php";
include "includes/prettify.php";

if (!$_SESSION["moderator"]) {
    throw new Exception("Cannot access reports page as a non-moderator");
}

$post_reports = $conn->query("SELECT * FROM post_reports")->fetch_all(MYSQLI_BOTH);
$post_reports_dismissed = array();
foreach($post_reports as $i => $report) {
    $post_reports_dismissed[$i] = $report["dismissed"];
}
array_multisort($post_reports_dismissed, SORT_ASC, $post_reports);

$comment_reports = $conn->query("SELECT * FROM comment_reports")->fetch_all(MYSQLI_BOTH);
$comment_reports_dismissed = array();
foreach($comment_reports as $i => $report) {
    $comment_reports_dismissed[$i] = $report["dismissed"];
}
array_multisort($comment_reports_dismissed, SORT_ASC, $comment_reports);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Reports </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/reports.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <h1> Post reports </h1>
    <table class="reports-table">
        <tr>
            <th> title at time of report </th>
            <th> body at time of report </th>
            <th> user_id </th>
            <th> post_id </th>
            <th> reporter user_id </th>
            <th> reporter ip </th>
            <th> report date </th>
            <th> statement </th>
            <th></th>
        </tr>
        <?php
        for ($i=0; $i < count($post_reports); $i++) {
            $row = $post_reports[$i];
            $row_class = "";
            if ($row["dismissed"]) {
                $row_class = "dismissed";
            }
            ?>
            <tr class="<?php echo $row_class ?>">
                <td> <?php echo prettify_title($row["title_tor"]) ?> </td>
                <td> <?php echo prettify_body($row["body_tor"], 100) ?> </td>
                <td> <a href="users.php?id=<?php echo $row["user_id_tor"] ?>"> <?php echo $row["user_id_tor"] ?> </a> </td>
                <td> <a href="view_post.php?id=<?php echo $row["post_id"] ?>"> <?php echo $row["post_id"] ?> </a> </td>
                <td> <a href="users.php?id=<?php echo $row["reporter_user_id"] ?>"> <?php echo $row["reporter_user_id"] ?> </a> </td>
                <td> <?php echo $row["reporter_ip"] ?> </td>
                <td> <?php echo prettify_datetime($row["report_timestamp"]) ?> </td>
                <td> <?php echo prettify_body($row["statement"]) ?> </td>
                <td> <a href="view_post_report.php?id=<?php echo $row["report_id"]?>"> view report </a> </td>
            </tr>
            <?php
        }
        ?>
    </table>

    <h1> Comment reports </h1>
    <table class="reports-table">
        <tr>
            <th> body at time of report </th>
            <th> user_id </th>
            <th> comment_id </th>
            <th> reporter user_id </th>
            <th> reporter ip </th>
            <th> report date </th>
            <th> statement </th>
            <th></th>
        </tr>
        <?php
        for ($i=0; $i < count($comment_reports); $i++) {
            $row = $comment_reports[$i];
            $row_class = "";
            if ($row["dismissed"]) {
                $row_class = "dismissed";
            }
            ?>
            <tr class="<?php echo $row_class ?>">
                <td> <?php echo prettify_body($row["body_tor"], 100) ?> </td>
                <td> <a href="users.php?id=<?php echo $row["user_id_tor"] ?>"> <?php echo $row["user_id_tor"] ?> </a> </td>
                <td> <?php echo $row["comment_id"] ?> </td>
                <td> <a href="users.php?id=<?php echo $row["reporter_user_id"] ?>"> <?php echo $row["reporter_user_id"] ?> </a> </td>
                <td> <?php echo $row["reporter_ip"] ?> </td>
                <td> <?php echo prettify_datetime($row["report_timestamp"]) ?> </td>
                <td> <?php echo $row["statement"] ?> </td>
                <td> <a href="view_comment_report.php?id=<?php echo $row["report_id"]?>"> view report </a> </td>
            </tr>
            <?php
        }
        ?>
    </table>
    

    <?php include "includes/footer.php" ?>
</body>

</html>