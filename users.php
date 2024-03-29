<?php
include "includes/db.php";
include "includes/prettify.php";

if (!isset($_GET["id"]) || !intval($_GET["id"])) {
    throw new Exception("Invalid user_id", 404);
}

$user_id = intval($_GET["id"]);
$user = $conn->query("SELECT * FROM users WHERE user_id=$user_id")->fetch_array();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> <?php echo prettify_username($user["username"]) ?> </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/users.css">
</head>

<body>
    <?php include "includes/header.php" ?>

    <div class="account-details">
        <img src="<?php echo $user["avatar_path"] ?>" width=180px>

        <table>
            <tr id="account-username">
                <td> <h2><?php echo $user["username"] ?></h2> </td>
            </tr>
            <tr id="account-about-me">
                <td> <?php echo $user["about_me"] ?> </td>
            </tr>
            <tr>
                <td> Joined <?php echo prettify_datetime($user["join_datetime"]) ?> </td>
            </tr>
        </table>
    </div>

    <!-- <table class="account-details">
        <tr>
            <th> Username </th>
            <th> User_id </th>
            <th> Avatar </th>
            <th> Join date </th>
            <th> Moderator </th>
            <th> Administrator </th>
            <th> Banned </th>
        </tr>
        <tr>
            <td> <?php echo prettify_username($user["username"]) ?> </td>
            <td> <?php echo $user["user_id"] ?> </td>
            <td> <img src="<?php echo $user["avatar_path"] ?>" width=100> </td>
            <td> <?php echo prettify_datetime($user["join_datetime"]) ?> </td>
            <td>
            <?php
                if ($user["moderator"]) {
                    echo "Moderator";
                } else {
                    echo "Not";
                }
                ?>
            </td>
            <td>
                <?php
                if ($user["administrator"]) {
                    echo "Administrator";
                } else {
                    echo "Not";
                }
                ?>
            </td>
            <td>
                <?php
                if ($user["banned"]) {
                    echo "Banned";
                } else {
                    echo "Not banned";
                }
                ?>
            </td>
        </tr>
    </table>

    <?php
    if ($_SESSION["administrator"]) {
        ?>
        <div class="users-administrator-options">
            <a href="admin.php?delete_user&user_id=<?php echo $user["user_id"] ?>"> Delete User </a>
            <a href="admin.php?ban_user&user_id=<?php echo $user["user_id"] ?>"> Ban User </a>
        </div>
        <?php
    }
    ?> -->

    <?php include "includes/footer.php" ?>
</body>

</html>