<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<div class="header">
    <img src="images/logo.jpg" class="logo">
    <div class="title">
        Forum 1
    </div>

    <div class="navbar-container">
        <ul>
            <?php
                if (!isset($_SESSION["user_id"])) {
                    ?>
                    <li> <a href="register.php"> Register </a> </li>
                    <li> <a href="login.php"> Login </a> </li>
                    <?php
                }
                if (isset($_SESSION["user_id"])) {
                    ?>
                    <li> <a href="forum.php"> Home </a> </li>

                    <?php
                }
                if (isset($_SESSION["banned"]) && !$_SESSION["banned"]) {
                    ?>
                    <li> <a href="post.php"> Post </a> </li>
                    <li> <a href="chat.php?view=chatrooms"> Chat </a> </li>
                    <?php
                }
                ?>
                <?php
                if (isset($_SESSION['administrator']) && $_SESSION['administrator']) {
                    ?>
                    <li> <a href="admin.php"> Admin </a> </li>
                    <?php
                }
                if (isset($_SESSION['moderator']) && $_SESSION['moderator']) {
                    ?>
                    <li> <a href="reports.php"> Reports </a> </li>
                    <?php
                }
                if (isset($_SESSION["banned"]) && $_SESSION["banned"]) {
                    ?>
                    <li> <a class="ban-message" href="banned.php">
                        You are banned
                    </a> </li>
                    <?php
                }
                ?>
        </ul>
    </div>

    <?php
    if (isset($_SESSION["user_id"])) {
        ?>
        <div class="dropdown">
            <img class="dropbtn" src="avatars/default.png" width=100>
            <div class="dropdown-content">
                <a href="account.php">Profile</a>
                <form method="post">
                    <input type="submit" name="logout" value="Log out">
                </form>
            </div>
        </div>
        <?php
    }
    ?>

</div>