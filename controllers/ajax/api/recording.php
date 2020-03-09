<?php
    $function  = $system->removeCharacters($input["function"]);
    $userNetid = $system->removeCharacters($input["userid"]);
    $logger    = new RecorderLogger();

    if($function == "init"){
        $_SESSION["user_login"] = $userNetid;
        $_SESSION["recorder_logged"] = true;
        //include $config["controllers"] . "/init_recording.php";
    }
?>