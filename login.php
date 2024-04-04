<?php
include "includes/db.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $escaped_username = mysqli_escape_string($conn, $username);

    $user = $conn->query("SELECT * FROM users WHERE username='$escaped_username'");
    $user_arr = $user->fetch_array();

    if ($user->num_rows == 0) {
        $bad_user = TRUE;
    } elseif (!password_verify($password, $user_arr['password_hash'])) {
        $bad_user = TRUE;
    } else {
        $_SESSION['user_id'] = $user_arr['user_id'];
        $_SESSION['username'] = $user_arr['username'];
        $_SESSION['moderator'] = $user_arr['moderator'];
        $_SESSION['administrator'] = $user_arr['administrator'];
        $_SESSION['sortby'] = 'votes';
        $_SESSION['banned'] = $user_arr['banned'];
        $_SESSION['read_posts'] = array();
        header('Location: index.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Home </title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <form class="center-form" method="post">
        <input type="text" name="username" placeholder="username">
        <input type="password" name="password" placeholder="password">
        <input type="submit" name="submit" value="Log in">
        <?php
        if (isset($bad_user) && $bad_user == TRUE) {
            ?>
            <div class="alert">
                Incorrect username and password
                <span class="close-button" onclick="this.parentElement.style.display = 'none'"> &times; </span>
            </div>
            <?php
        }
        ?>
    </form>

    <?php include "includes/footer.php" ?>
</body>

</html>