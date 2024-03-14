<?php
include "includes/db.php"
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Changelog </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/changelog.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <h2> Working on </h2>
    <ul>
        <li> Ads </li>
        <li> New mention count in navbar </li>
        <li> auto scroll to new comments/scroll to new comments button </li>
    </ul>

    <h2> Completed updates </h2>
    <ul>
        <li> prettified timestamp </li>
        <li> comment timestamps </li>
        <li> edit comments </li>
        <li> delete comments </li>
        <li> edit posts (can't edit post title yet) </li>
        <li> @ mentions </li>
        <li> colored mod and admin usernames </li>
        <li> admin can access debug page </li>
        <li> lily is a moderator </li>
        <li> moderators can delete posts </li>
        <li> added sortby mentions </li>
        <li> added sortby comments </li>
        <li> added (empty title) for posts with empty titles </li>
        <li> admin page to ban users, delete users, and delete posts </li>
        <li> IP banning </li>
        <li> blocked users cannot post, comment, or vote </li>
        <li> userpages </li>
        <li> added sortby recent comments </li>
        <li> added purge unused avatars button to admin page </li>
        <li> report page for reporting comments and posts </li>
        <li> indicator showing if a post or comment has been edited </li>
        <li> reports page for moderators to review reports </li>
        <li> there is now a 1 in 10 chance for bibble to appear on the ban page </li>
        <li> moderators can delete reported posts and comments </li>
        <li> forum now hosted on a server with an actual domain (no more asking me for IP !!!!) </li>
        <li> pinned posts </li>
        <li> indicator for forum listing that shows whether there are new comments on a post that you haven't seen yet </li>
        <li> mark all as read button on forum page </li>
        <li> visited attribute of post on forum page </li>
        <li> fix avatar lfi vulnerability </li>
        <li> online users count on forum page  </li>
        <li class="update-interval"> 14/03/24 </li>
        <li> images </li>
        <li> changelog </li>
    </ul>

    <?php include "includes/footer.php" ?>
</body>

</html>