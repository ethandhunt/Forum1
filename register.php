<?php
include "includes/db.php";
if (isset($_POST['submit'])) {
    // throw new Exception('stop');
    $username = $_POST['username'];
    $password = $_POST['password'];


    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    $escaped_username = mysqli_escape_string($conn, $username);

    $avatar_path = '';
    if ($_FILES["avatar"] && $_FILES["avatar"]["size"]>0) {
        $target_filename = sha1_file($_FILES["avatar"]["tmp_name"]) . "." . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);

        move_uploaded_file($_FILES["avatar"]["tmp_name"], "avatars/$target_filename");

        $avatar_path = "avatars/$target_filename";
    }

    $ip_addr = $_SERVER["REMOTE_ADDR"];

    $same_username = $conn->query("SELECT user_id FROM users WHERE username='$escaped_username'");
    $banned_ip = $conn->query("SELECT user_id FROM users WHERE register_address='$ip_addr' AND banned=1");
    
    // don't register if there is already a user with the username
    if ($same_username->num_rows != 0) {
        $bad_username = true;
        $bad_username_message = "That username is already in use";
    } elseif (ctype_space($username) || $username == "") {
        $bad_username = true;
        $bad_username_message = "You cannot have an empty or all-whitespace username";
    } elseif ($banned_ip->num_rows != 0) {
        $bad_username = true;
        $bad_username_message = "You cannot register from this ip";
    } else {
        $conn->query("INSERT INTO users (username, password_hash, avatar_path, register_address, moderator, administrator, banned) VALUES ('$escaped_username', '$pass_hash', '$avatar_path', '$ip_addr', 0, 0, 0)");
        header("Location: login.php"); // redirect to login.php on successful registration
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title> Register </title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <form class="center-form" method="post" enctype="multipart/form-data">
        <input type="text" name="username" placeholder="Username" maxlength=30>
        <?php
        if (isset($bad_username) && $bad_username == TRUE) {
            ?>
            <div class="alert">
                <?php echo $bad_username_message ?>
                <span class="close-button" onclick="this.parentElement.style.display = 'none'"> &times; </span>
            </div>
            <?php
        }
        ?>
        
        <input type="password" name="password" placeholder="Password">
        <label for="avatar"> Upload an avatar: </label>
        <input type="file" name="avatar" accept="image/png, image/jpeg">
        <input type="submit" name="submit" value="Submit">
    </form>

    <?php include "includes/footer.php" ?>
</body>

</html>