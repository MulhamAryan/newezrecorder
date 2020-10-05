<?php
    $username = $input["username"];
    $userpass = $input["password"];

    $login = $auth->checkUserInfo($username,$userpass);
    echo json_encode($login,true);
