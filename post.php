<?php
include "includes/db.php";

if ($_SESSION["banned"]) {
    header("Location: forum.php");
}

if (!isset($_SESSION["user_id"])) {
    header('Location: index.php');

} elseif (isset($_POST["post"])) {
    $title = mysqli_real_escape_string($conn, $_POST["title"]);
    $body = mysqli_real_escape_string($conn, $_POST["body"]);
    $author_id = $_SESSION["user_id"];
    $image_href = mysqli_real_escape_string($conn, $_POST["image_href"]);

    if ($_SESSION["banned"]) {
        header("Location: forum.php");
        throw new Exception('Cannot post as a banned user', 401);
    }

    $conn->query("INSERT INTO posts (author_user_id, title, body, edited, pinned, image_href) VALUES ($author_id, '$title', '$body', 0, 0, '$image_href')");
    $post_id = $conn->insert_id;
    header("Location: view_post.php?id=$post_id");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Post </title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <form class="post-form" method="post">
        <input type="text" id="title" name="title" placeholder="Title" maxlength=60>
        <textarea type="text" id="body" name="body" placeholder="Body" rows=20 cols=100 maxlength=2000></textarea>
        <input type="text" name="image_href" placeholder="Image URL" maxlength=1000>
        <input type="submit" id="submit" name="post" value="Post">
    </form>

    <?php include "includes/footer.php" ?>
</body>

</html>