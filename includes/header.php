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
            <li>
                <?php
                if (!isset($_SESSION["user_id"])) {
                    ?>
                    <a href="register.php"> Register </a>
                    <a href="login.php"> Login </a>
                    <?php
                }
                if (isset($_SESSION["user_id"])) {
                    ?>
                    <a href="forum.php"> Home </a>

                    <?php
                }
                if (isset($_SESSION["banned"]) && !$_SESSION["banned"]) {
                    ?>
                    <a href="post.php"> Post </a>
                    <a href="chat.php?view=chatrooms"> Chat </a>
                    <?php
                }
                ?>
                <?php
                if (isset($_SESSION['administrator']) && $_SESSION['administrator']) {
                    ?>
                    <a href="admin.php"> Admin </a>
                    <?php
                }
                if (isset($_SESSION['moderator']) && $_SESSION['moderator']) {
                    ?>
                    <a href="reports.php"> Reports </a>
                    <?php
                }
                if (isset($_SESSION["banned"]) && $_SESSION["banned"]) {
                    ?>
                    <a class="ban-message" href="banned.php">
                        You are banned
                    </a>
                    <?php
                }
                ?>
            </li>
        </ul>   
    </div>

    <?php
    if (isset($_SESSION["user_id"])) {
        ?>
        <div class="dropdown">
            <img class="dropbtn" src="avatars/default.jpg" width=100>
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