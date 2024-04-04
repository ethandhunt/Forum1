<?php
include "includes/db.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$_SESSION["banned"]) {
    header("Location: index.php");
}

$user_id = $_SESSION["user_id"];
$user = $conn->query("SELECT * FROM users WHERE user_id=$user_id")->fetch_array();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Banned </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/banned.css">
</head>

<body>
    <?php include "includes/header.php" ?>
    
    <div class="ban-description">
        You've been banned, you can no longer make posts, comment on posts, or upvote posts
    </div>
    <?php
    if (random_int(0, 10) == 0) {
        ?>
        <img src="images/ban_creature.png" width=300 class="bibble">
        <?php
    }
    ?>

    <?php include "includes/footer.php" ?>
</body>

</html>