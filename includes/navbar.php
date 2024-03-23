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
                <a href="logout.php"> Logout </a>
                <a href="forum.php"> Forum </a>
                <a href="account.php"> Account </a>
                <?php
            }
            if (isset($_SESSION["banned"]) && !$_SESSION["banned"]) {
                ?>
                <a href="post.php"> Post </a>
                <a href="chat.php?view=chatrooms"> Live Chat </a>
                <?php
            }
            ?>
            <a href="bug_report.php"> Bug report </a>
            <a href="changelog.php"> Changelog </a>
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