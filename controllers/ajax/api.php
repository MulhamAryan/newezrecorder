<?php
    header("Content-Type: application/json");

    include "api/request_acces.php";
/*$user    = $system->removeCharacters($input["user"]);
$key     = $system->removeCharacters($input["key"]);
$info    = $system->removeCharacters($input["info"]);
/*
if(empty(ser) || empty($key)){
    header("LOCATION:index.php?no_api_user_info_found");
}
if($config["apiactive"] == 1 && $user == $config["apiuser"] && $key == $config["apikey"]){
    switch ($info) {
        case $info:
            if (file_exists($config["ajax"] . "/api/" . $info . ".php"))
                include $config["ajax"] . "/api/" . $info . ".php";
            else
                header("LOCATION:index.php?api_function_not_found");
            break;
        default:
            header("LOCATION:index.php?api_function_not_found");
            break;
    }
}
else{
    header("LOCATION:index.php?api_login_not_found");
}
*/
?>