<?php
    $clientServer = $_SERVER['REMOTE_ADDR'];
    $clientUser   = $system->removeCharacters($_POST["username"]);
    $clientPass   = $system->removeCharacters($_POST["userpass"]);
    $loggined = 0;
    $api_credential = json_decode(file_get_contents($config["basedir"] . "/etc/api_access/credential.json"),true);
    echo $_SERVER['HTTP_AUTHORIZATION'];/*
    foreach($api_credential as $apiCred){
        if($apiCred["serverip"] == $clientServer && $apiCred["username"] == $clientUser && $apiCred["userpass"] == $clientPass){
            $loggined = 1;
            $_SESSION["token"] = md5($clientServer);
            break;
        }
    }
    if($loggined == 0){
        echo "incorrect_info";
    }
    else{
        echo "logged";
    }*/
?>