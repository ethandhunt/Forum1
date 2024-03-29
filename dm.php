<?php
include "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Change Me </title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <?php
    ?>

    <?php include "includes/footer.php" ?>
</body>

</html>