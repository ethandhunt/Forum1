<?php
include "includes/db.php";
include "includes/prettify.php";

if (isset($_POST["update_avatar"])) {
    $target_filename = sha1_file($_FILES["avatar"]["tmp_name"]) . "." . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);

    move_uploaded_file($_FILES["avatar"]["tmp_name"], "avatars/$target_filename");

    $user_id = $_SESSION["user_id"];
    $conn->query("UPDATE users SET avatar_path='avatars/$target_filename' WHERE user_id=$user_id");
}
if (isset($_POST["update_about_me"])) {
    $escaped_about_me = mysqli_real_escape_string($conn, $_POST["about_me"]);
    
    $user_id = $_SESSION["user_id"];
    $conn->query("UPDATE users SET about_me='$escaped_about_me' WHERE user_id=$user_id");
}

if (isset($_POST["set_password"])) {
    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $user_id = $_SESSION["user_id"];
    $conn->query("UPDATE users SET password_hash='$password_hash' WHERE user_id=$user_id");
}

$user_id = $_SESSION["user_id"];
$user = $conn->query("SELECT username, about_me, avatar_path FROM users WHERE user_id=$user_id")->fetch_array();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Account </title>
    <link rel="stylesheet" href="style/style.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <div class="account-details">
        <table>
            <tr id="account-details-username">
                <td class="td-left"> username: </td> <td> <?php echo $user["username"] ?> </td>
            </tr>
            <tr id="account-details-about-me">
                <td class="td-left"> about me: </td> <td> <?php echo $user["about_me"] ?> </td>
            </tr>
        </table>
        avatar: <br> <img src="<?php echo $user["avatar_path"] ?>" width=100>
    </div>

    <form class="account-form" enctype="multipart/form-data" method="post">
        <label for="avatar"> Upload an avatar </label>
        <input type="file" name="avatar">
        <input type="submit" name="update_avatar" value="Update avatar">
    </form>
    <form class="account-form" method="post">
        <input type="text" name="about_me" id="about_me" placeholder="About Me" maxlength=100 value="<?php echo prettify_about_me($user["about_me"]) ?>">
        <input type="submit" name="update_about_me" value="Update about me">
    </form>

    <form class="account-form" method="post">
        <input type="password" name="password" placeholder="Password" maxlength=100 autocomplete="new-password">
        <input type="submit" name="set_password" value="Set password">
    </form>

    <?php include "includes/footer.php" ?>
</body>

</html>