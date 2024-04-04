<?php
include "includes/db.php";
if (isset($_SESSION["user_id"]) && isset($_POST["logout"])) {
    unset($_SESSION["user_id"]);
    unset($_SESSION["username"]);
    unset($_SESSION["moderator"]);
    unset($_SESSION["administrator"]);
    unset($_SESSION["banned"]);
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Log out </title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <form method="post">
        <input type="submit" name="logout" value="Log out">
    </form>

    <?php include "includes/footer.php" ?>
</body>

</html>