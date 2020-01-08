<?php

    if($_SESSION["forced_recorder_logged"] == md5($randomSecurityNumber * date("dmY")) && !empty($_SESSION["forced_user_login"])){

        $nowrecording = json_decode($system->getRecordingStatus(),true);
        $logger->log(EventType::RECORDER_FORCE_QUIT, LogLevel::NOTICE, "Record was forcefully cancelled", array(basename(__FILE__)), $nowrecording["asset"]);

        $system->prepareMerge("private",$nowrecording);
        $system->crontabReset();

        sleep(1);
        $_SESSION["user_login"] = $_SESSION["forced_user_login"];
        $_SESSION["recorder_logged"] = true;
        unset($_SESSION["forced_recorder_logged"]);
        unset($_SESSION["forced_user_login"]);
        header("LOCATION:?");
    }
    else{
        header("LOCATION:?action=login");
    }
?>