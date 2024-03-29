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
$user = $conn->query("SELECT * FROM users WHERE user_id=$user_id")->fetch_array();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Account </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/account.css">
</head>

<body>
    <?php include "includes/header.php" ?>
    
    <div class="account">
        <img src="<?php echo $user["avatar_path"] ?>" width=180px>

        <table>
            <tr>
                <td> <h2><?php echo $user["username"] ?></h2> </td>

                <td>        
                    <?php
                        if ($user["banned"]) {
                            echo "BANNED";
                        } else {
                            if ($user["administrator"]) {
                                echo "(Administrator)";
                            } else {
                                if ($user["moderator"]) {
                                    echo "(Moderator)";
                                } 
                            }
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td> <?php echo $user["about_me"] ?> </td>
            </tr>
            <tr>
                <td> Joined <?php echo prettify_datetime($user["join_datetime"]) ?> </td>
            </tr>
        </table>
    </div>

    <div class="update-account">
        <h2>Update Profile</h2>

        <form class="account-form" enctype="multipart/form-data" method="post">
            <label for="avatar"> Upload Avatar </label>
            <input type="file" name="avatar">   
            <input type="submit" name="update_avatar" value="Update">
        </form>
        
        <form class="account-form" method="post">
            <label for="about_me"> Update About Me </label>
            <input type="text" name="about_me" id="about_me" placeholder="About Me" maxlength=100 value="<?php echo prettify_about_me($user["about_me"]) ?>">
            <input type="submit" name="update_about_me" value="Update">
        </form>

        <form class="account-form" method="post">
            <label for="password"> Update Password </label>
            <input type="password" name="password" placeholder="Password" maxlength=100 autocomplete="new-password">
            <input type="submit" name="set_password" value="Update">
        </form>
    </div>

    <?php include "includes/footer.php" ?>
</body>

</html>