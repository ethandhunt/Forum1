<?php
include "includes/db.php";
include "includes/prettify.php";

$show_chat = false;
$show_chat_rooms = false;
$MAX_CHAT_ROWS = 100;
if (isset($_POST["send_message"])) {
    $chat_id = intval($_GET["chat_id"]);
    if (!$chat_id) {
        throw new Exception("Invalid chat_id");
    }

    $content = mysqli_real_escape_string($conn, $_POST["message_content"]);
    $password = mysqli_real_escape_string($conn, $_GET["password"]);
    $user_id = $_SESSION["user_id"];


    $chat_room_query = $conn->query("SELECT * FROM chat_rooms WHERE chat_room_id=$chat_id AND password='$password'");
    if ($chat_room_query->num_rows != 1) {
        throw new Exception("Could not find chat room with id $chat_id");
    }

    $conn->query("INSERT INTO chat_messages (chat_room_id, content, author_user_id) VALUES ($chat_id, '$content', $user_id)");

    $num = $conn->query("SELECT count(*) FROM chat_messages WHERE chat_room_id=$chat_id")->fetch_array()[0];
    $num = $num - $MAX_CHAT_ROWS;
    if ($num > 0) {
        $conn->query(
            "DELETE FROM chat_messages ORDER BY timestamp ASC " .
                "LIMIT $num"
        );
    }
} elseif (isset($_POST["make_room"])) {
    $room_title = mysqli_real_escape_string($conn, $_POST["room_title"]);
    $password = mysqli_real_escape_string($conn, $_POST["room_password"]);
    $user_id = $_SESSION["user_id"];

    $conn->query("INSERT INTO chat_rooms (title, password, creator_user_id) VALUES ('$room_title', '$password', $user_id)");
} elseif (isset($_GET["view"])) {
    if ($_GET["view"] == "chat") {
        $chat_id = intval($_GET["chat_id"]);
        if (!$chat_id) {
            throw new Exception("Invalid chat_id");
        }
        $show_chat = true;

        $password = $_GET["password"];
        $chat_room = $conn->query("SELECT * FROM chat_rooms WHERE chat_room_id=$chat_id")->fetch_array();
        if ($password != $chat_room["password"]) {
            $invalid_password = true;
        } else {
            $invalid_password = false;
        }
    } elseif ($_GET["view"] == "chatrooms") {
        $chatrooms = $conn->query("SELECT * FROM chat_rooms")->fetch_all(MYSQLI_ASSOC);
        $show_chat_rooms = true;
    }
} elseif (isset($_GET["fetch"])) {

    if ($_GET["fetch"] == "messages") {
        $chat_id = intval($_GET["chat_id"]);
        if (!$chat_id) {
            throw new Exception("Invalid chat_id");
        }

        $password = mysqli_real_escape_string($conn, $_GET["password"]);
        $chat_room = $conn->query("SELECT * FROM chat_rooms WHERE chat_room_id=$chat_id AND password='$password'")->fetch_array();
        $chat_messages = $conn->query("SELECT chat_messages.*, users.username FROM chat_messages INNER JOIN users on users.user_id=chat_messages.author_user_id WHERE chat_room_id=$chat_id ORDER BY timestamp DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
        // var_dump($chat_room, $chat_messages);
        echo json_encode($chat_messages);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title> Chat </title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/chat.css">
    <?php if ($show_chat) {
        echo "<script src=\"includes/chat.js\"> </script>";
    } ?>
</head>

<content>
    <?php include "includes/header.php" ?>

    <?php include "includes/navbar.php" ?>

    <?php
    if ($show_chat) {
        if ($invalid_password) {
        ?>
            <div class="alert alert-danger"> Enter password </div>
            <input type="text" id="password_input">
            <script>
                let pass_input = document.getElementById("password_input")
                pass_input.addEventListener("keypress", function(event) {
                    if (event.key === "Enter") {
                        let password = this.value
                        let query = new URLSearchParams(window.location.search)
                        query.set("password", password)
                        window.location.search = query.toString()
                    }
                });
            </script>
        <?php
        } else {
        ?>
            <h2> <?php echo htmlentities($chat_room["title"]) ?> </h2>
            <div id="chat_div"></div>
            <input id="text_input" type="text">
            <script>
                update_chat().then(() => {
                    text_input.scrollIntoView()
                })

                setInterval(() => {
                    update_chat().then(() => {
                        text_input.scrollIntoView()
                    })
                }, 5000)

                let text_input = document.getElementById('text_input')
                text_input.focus()
                text_input.addEventListener('keypress', (event) => {
                    if (event.key === 'Enter') {
                        send_message(text_input.value)
                        text_input.value = ''
                        update_chat().then(() => {
                            text_input.scrollIntoView()
                        })
                    }
                })
            </script>
        <?php
        }
    }
    ?>

    <?php
    if ($show_chat_rooms) {
    ?>
        <table>
            <?php
            foreach ($chatrooms as $room) {
            ?>
                <tr>
                    <td> <a href="chat.php?view=chat&password=&chat_id=<?php echo $room["chat_room_id"] ?>"><?php echo prettify_title($room["title"]) ?></a> </td>
                </tr>
            <?php
            }
            ?>
        </table>

        <form class="make-room-form" method="post">
            <input type="text" name="room_title" placeholder="Room title">
            <input type="password" name="room_password" placeholder="Password" autocomplete="new-password">
            <input type="submit" name="make_room" value="Make room">
        </form>
        passwords are stored in plaintext
    <?php
    }
    ?>

    <?php include "includes/footer.php" ?>
</content>

</html>