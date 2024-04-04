<?php
function prettify_timestamp($timestamp) {
    $MINS = 60;
    $HOURS = 60*$MINS;
    $DAYS = 24*$HOURS;
    $tz = timezone_open("Pacific/Auckland");
    $cmp_time = time()+$tz->getOffset(date_create());
    if ($cmp_time-$timestamp > 8*$HOURS) {
        return date('j/m/y g:i a', $timestamp);
    } else {
        return date('g:i a', $timestamp);
    }
}

function prettify_datetime($datetime) {
    return prettify_timestamp(strtotime($datetime));
}

function prettify_username($username_raw) {
    $username = htmlentities($username_raw, ENT_QUOTES);
    return $username;
}

function prettify_about_me($about_me_raw) {
    $about_me = htmlentities($about_me_raw, ENT_QUOTES);
    return $about_me;
}

function prettify_title($title_raw) {
    $title = htmlentities($title_raw, ENT_QUOTES);
    // var_dump($title, ctype_space($title));
    if (ctype_space($title) || $title == "") {
        $title = '(empty title)';
    }
    return $title;
}

function prettify_body($body_raw, $length=-1) {
    if ($length != -1) {
        if (strlen($body_raw) > $length) {
            $body_raw = substr($body_raw, 0, $length) . "...";
        }
    }
    $body = htmlentities($body_raw, ENT_QUOTES);
    $body = str_replace("\n", "<br>\n", $body);
    $body = str_replace("@".$_SESSION["username"], "<span class=\"mention\">@".$_SESSION["username"]."</span>", $body);

    if (ctype_space($body) || $body == "") {
        $body = '(empty body)';
    }
    return $body;
}
?>