<div class="header">
    <img src="images/logo.jpg" class="logo">
    <div class="title">
        Forum 1
    </div>

    <div class="navbar-container">
        <ul>
            <li>
                <a href="index.php"> Home </a>
                <?php
                if (!isset($_SESSION["user_id"])) {
                    ?>
                    <a href="register.php"> Register </a>
                    <a href="login.php"> Login </a>
                    <?php
                }
                if (isset($_SESSION["user_id"])) {
                    ?>
                    <a href="forum.php"> Forum </a>

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
            <img class="dropbtn" src="images/logo.jpg" class="logo">
            <div class="dropdown-content">
                <a href="account.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <?php
        }
    ?>

</div>