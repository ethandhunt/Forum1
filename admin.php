<?php
include "includes/db.php";
include "includes/prettify.php";

if (!$_SESSION["administrator"]) {
    throw new Exception("cant access admin page as a non-admin", 401);
}

if (isset($_POST["delete_user_conf"])) {
    $user_id = $_POST["user_id_conf"];
    if (!intval($user_id)) {
        throw new Exception("Invalid user_id");
    }

    $conn->query("DELETE FROM users WHERE user_id=$user_id");
    $conn->query("DELETE posts, comments FROM posts INNER JOIN comments ON comments.post_id = posts.post_id WHERE posts.author_user_id=$user_id");
    $conn->query("DELETE FROM comments WHERE author_user_id=$user_id");
    $conn->query("DELETE FROM post_votes WHERE user_id=$user_id");
}

if (isset($_POST["ban_user_conf"])) {
    $user_id = $_POST["user_id_conf"];
    $ban_value = $_POST["ban_value"];
    if (!intval($user_id)) {
        throw new Exception("Invalid user_id");
    }

    $sanitised_ban_value = 0;
    if ($ban_value == "ban") {
        $sanitised_ban_value = 1;
    }

    $conn->query("UPDATE users SET banned=$sanitised_ban_value WHERE user_id=$user_id");
}

$json = json_decode(file_get_contents('php://input'), true);
if (isset($json["delete_posts"])) {
    $condition = "0=1";
    for ($i=0; $i < count($json["post_ids"]); $i++) {
        $post_id = $json["post_ids"][$i];
        if (!intval($post_id)) {
            throw new Exception("Invalid post id $post_id");
        }
        $condition = $condition . " OR post_id=$post_id";
    }


    $conn->query("DELETE FROM posts WHERE ($condition)");
    $conn->query("DELETE FROM comments WHERE ($condition)");
    $conn->query("DELETE FROM post_votes WHERE ($condition)");
}

if (isset($_POST["purge_avatars_conf"])) {
    foreach (get_unused_avatars() as $filename) {
        unlink($filename);
    }
}

function get_unused_avatars() {
    global $conn;

    $avatar_files = scandir("avatars");
    $unused_avatars = array();
    
    $users = $conn->query("SELECT avatar_path FROM users WHERE banned=0")->fetch_all();
    $used_avatars = array();
    foreach ($users as $user) {
        array_push($used_avatars, $user[0]);
    }
    foreach ($avatar_files as $filename) {
        $filename = "avatars/$filename";
        if (!in_array($filename, $used_avatars) && !is_dir($filename)) {
            array_push($unused_avatars, $filename);
        }
    }

    return $unused_avatars;
}

$users = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_BOTH);
$posts = $conn->query("SELECT * FROM posts")->fetch_all(MYSQLI_BOTH);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Admin </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/admin.css">
    <script src="includes/admin.js"></script>
</head>

<body>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <form method="post" class="user-admin-form">
        <label for="find_by"> Select by: </label>
        <select name="find_by">
            <option> username </option>
            <option> user_id </option>
        </select>
        <input type="number" name="user_id" list="user_id_list" placeholder="user id">
        <input type="text" name="username" list="username_list" placeholder="username" autocomplete="off">
        <datalist id="user_id_list">
        <?php
            for ($i=0; $i < count($users); $i++) {
                $row = $users[$i];
                ?>
                <option><?php echo $row["user_id"] ?></option>
                <?php
            }
            ?>
        </datalist>
        <datalist id="username_list">
            <?php
            for ($i=0; $i < count($users); $i++) {
                $row = $users[$i];
                ?>
                <option><?php echo htmlentities($row["username"], ENT_QUOTES) ?></option>
                <?php
            }
            ?>
        </datalist>
        <?php
        $delete_user = false;
        if (isset($_POST["delete_user"])) {
            $delete_user = true;
            $username = $_POST["username"];
            $user_id = $_POST["user_id"];
            $find_by = $_POST["find_by"];
        } elseif (isset($_GET["delete_user"])) {
            $delete_user = true;
            $user_id = intval($_GET["user_id"]);
            $find_by = "user_id"; // only find by user_id for GET requests
        }
        if ($delete_user) {
            $condition = "0";
            if ($find_by == "username") {
                $username = mysqli_real_escape_string($conn, $username);
                $condition = "BINARY username='$username'"; // BINARY for case sensitivity
            } elseif ($find_by == "user_id") {
                if (!intval($user_id)) {
                    throw new Exception('Invalid user_id');
                }
                $condition = "user_id=$user_id";
            }
            $user_query = $conn->query("SELECT * FROM users WHERE $condition");
            $user = $user_query->fetch_array();
            if ($user_query->num_rows == 1) {
                ?>
                <table class="user_conf_details">
                    <tr>
                        <th> Username </th>
                        <th> User_id </th>
                        <th> Avatar </th>
                        <th> About me </th>
                    </tr>
                    <tr>
                        <td style="padding-right: 20px"><?php echo $user["username"] ?></td>
                        <td><?php echo $user["user_id"] ?></td>
                        <td> <img style="width: 100px" src="<?php echo $user["avatar_path"] ?>"> </td>
                        <td><?php echo prettify_about_me($user["about_me"]) ?></td>
                    </tr>
                </table>
                <input type="hidden" name="user_id_conf" value="<?php echo $user["user_id"] ?>">
                <input class="delete-conf" type="submit" name="delete_user_conf" value="delete user?">
                <?php
            } else {
                ?>
                <span class="alert">
                    Found <?php echo $user_query->num_rows ?> users that matched those conditions
                    <span class="close-button" onclick="location.href = 'admin.php'"> &times; </span>
                </span>
                <?php
            }
        } else {
            ?>
            <input type="submit" name="delete_user" value="Delete user">
            <?php
        }

        $ban_user = false;
        if (isset($_POST["ban_user"])) {
            $ban_user = true;
            $username = $_POST["username"];
            $user_id = $_POST["user_id"];
            $find_by = $_POST["find_by"];
        } elseif (isset($_GET["ban_user"])) {
            $ban_user = true;
            $user_id = intval($_GET["user_id"]);
            $find_by = "user_id"; // only find by user_id for GET requests
        }
        if ($ban_user) {
            $condition = "0";
            if ($find_by == "username") {
                $username = mysqli_real_escape_string($conn, $username);
                $condition = "BINARY username='$username'"; // BINARY for case sensitivity
            } elseif ($find_by == "user_id") {
                if (!intval($user_id)) {
                    throw new Exception('Invalid user_id');
                }
                $condition = "user_id=$user_id";
            }
            $user_query = $conn->query("SELECT * FROM users WHERE $condition");
            $user = $user_query->fetch_array();
            if ($user_query->num_rows == 1) {
                ?>
                <table class="user_conf_details">
                    <tr>
                        <th> Username </th>
                        <th> User_id </th>
                        <th> Avatar </th>
                        <th> About me </th>
                        <th> Banned </th>
                    </tr>
                    <tr>
                        <td style="padding-right: 20px"><?php echo $user["username"] ?></td>
                        <td><?php echo $user["user_id"] ?></td>
                        <td> <img style="width: 100px" src="<?php echo $user["avatar_path"] ?>"> </td>
                        <td style="padding-right: 20px"><?php echo prettify_about_me($user["about_me"]) ?></td>
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
                <input type="hidden" name="user_id_conf" value="<?php echo $user["user_id"] ?>">
                <select name="ban_value">
                    <option <?php if (!$user["banned"]) {echo "selected";} ?>>ban</option>
                    <option <?php if ($user["banned"]) {echo "selected";} ?>>unban</option>
                </select>
                <?php
                if ($user["banned"]) {
                    $button_string = "unban user?";
                } else {
                    $button_string = "ban user?";
                }
                ?>
                <input class="ban-conf" type="submit" name="ban_user_conf" value="<?php echo $button_string ?>">
                <?php
            } else {
                ?>
                <span class="alert">
                    Found <?php echo $user_query->num_rows ?> users that matched those conditions
                    <span class="close-button" onclick="location.href = 'admin.php'"> &times; </span>
                </span>
                <?php
            }
        }
        else {
            ?>
            <input type="submit" name="ban_user" value="Ban user">
            <?php
        }
        ?>
    </form>

    <div class="post-admin-div">
        <button type="button" class="collapsible"> Show posts </button>
        <div class="collapsible-content">
            <table>
                <tr>
                    <th> </th>
                    <th> post_id </th>
                    <th> title </th>
                </tr>
                <?php
                for ($i=0; $i < count($posts); $i++) {
                    $row = $posts[$i];
                    ?>
                    <tr class="form-post">
                        <td> <input type="checkbox" class="post-checkbox" id="<?php echo $row["post_id"] ?>"> </td>
                        <td> <?php echo $row["post_id"] ?> </td>
                        <td> <?php echo prettify_title($row["title"]) ?> </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <button onclick="delete_posts()"> Delete Posts </button>
        </div>
    </div>

    <form class="purge-avatars-form" method="post">
        <?php
        if (isset($_POST["purge_avatars"])) {
            ?>
            <table>
                <tr>
                    <th> filename </th>
                </tr>
                <?php
                foreach (get_unused_avatars() as $filename) {
                    ?>
                    <tr>
                        <td> <?php echo $filename ?> </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <input class="purge-avatars-conf" type="submit" name="purge_avatars_conf" value="Purge avatars?">
            <?php
        } else {
            ?>
            <input type="submit" name="purge_avatars" value="Purge avatars">
            <?php
        }
        ?>
    </form>

    <script>
        let coll = document.getElementsByClassName("collapsible");

        for (let i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                content.style.display = "none";
                this.innerText = 'Show posts'
                } else {
                content.style.display = "block";
                this.innerText = 'Hide posts'
                }
            });
        } 
    </script>

    <?php include "includes/footer.php" ?>
</body>

</html>