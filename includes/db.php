<?php
$servername = "localhost";
$username = "root";
$password = null;
$dbname = "forum1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed" . $conn->connect_error);
}

session_start();

// logout user if their userid no longer exists in the users table
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $result = $conn->query("SELECT user_id, banned, register_address FROM users WHERE user_id=$user_id");
    if ($result->num_rows != 1) {
        unset($_SESSION["user_id"]);
        unset($_SESSION["username"]);
        unset($_SESSION["moderator"]);
        unset($_SESSION["administrator"]);
        $ban_message = true;
    } elseif ($result->fetch_array()["banned"]) {
        $_SESSION["moderator"] = false;
        $_SESSION["administrator"] = false;
        $_SESSION["banned"] = true;
    }

    // update register address if it's not already in the db
    if (!isset($result->fetch_array()["register_address"])) {
        $ip_addr = $_SERVER["REMOTE_ADDR"];
        $conn->query("UPDATE users SET register_address='$ip_addr' WHERE user_id=$user_id");
    }
}
?>