<?php
include "includes/db.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    // throw new Exception('stop');
    $username = $_POST['username'];
    $password = $_POST['password'];


    $pass_hash = password_hash($password, PASSWORD_DEFAULT);
    $escaped_username = mysqli_escape_string($conn, $username);

    $allowed_file_extensions = ["webp", "jpg", "jpeg", "gif", "png"];
    $avatar_path = '';
    if ($_FILES["avatar"] && $_FILES["avatar"]["size"]>0) {
        $file_ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        if (in_array($file_ext, $allowed_file_extensions)) {
            $target_filename = sha1_file($_FILES["avatar"]["tmp_name"]) . "." . $file_ext;

            move_uploaded_file($_FILES["avatar"]["tmp_name"], "avatars/$target_filename");

            $avatar_path = "avatars/$target_filename";
        } else {
            $bad_avatar = true;
            $bad_avatar_message = "avatar file extension must be one of " . implode(", ", $allowed_file_extensions) . " (case sensitive) <br> you can ask me to add a filetype to the allow list";
        }
    } else {
        $avatar_path = "avatars/default.jpg";
    }

    $ip_addr = $_SERVER["REMOTE_ADDR"];

    $same_username = $conn->query("SELECT user_id FROM users WHERE username='$escaped_username'");
    // $banned_ip = $conn->query("SELECT user_id FROM users WHERE register_address='$ip_addr' AND banned=1");
    
    // don't register if there is already a user with the username
    if ($same_username->num_rows != 0) {
        $bad_username = true;
        $bad_username_message = "That username is already in use";
    } elseif (ctype_space($username) || $username == "") {
        $bad_username = true;
        $bad_username_message = "You cannot have an empty or all-whitespace username";
    // } elseif ($banned_ip->num_rows != 0) {
    //     $bad_username = true;
    //     $bad_username_message = "You cannot register from this ip";
    } elseif ($bad_avatar) {

    } else {
        $conn->query("INSERT INTO users (username, password_hash, avatar_path, register_address, moderator, administrator, banned, about_me) VALUES ('$escaped_username', '$pass_hash', '$avatar_path', '$ip_addr', 0, 0, 0, '')");
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

    <form class="center-form" method="post" enctype="multipart/form-data">
        <input type="text" name="username" placeholder="Username" maxlength=30>
        <?php
        if (isset($bad_username) && $bad_username) {
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
        <?php
        if (isset($bad_avatar) && $bad_avatar) {
            ?>
            <div class="alert">
                <?php echo $bad_avatar_message ?>
                <span class="close-button" onclick="this.parentElement.style.display = 'none'"> &times; </span>
            </div>
            <?php
        }
        ?>
        <input type="submit" name="submit" value="Submit">
    </form>

    <?php include "includes/footer.php" ?>
</body>

</html>