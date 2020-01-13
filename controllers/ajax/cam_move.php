<?php

    if($auth->userSession("is_logged") == true){
        $scene = $system->removeCharacters($_GET["plan"]);
        $asset = $system->getRecordingStatus("asset");
        $move = $plugin["camcontrollers"]->positionMove($scene);
        $user = $auth->userSession("logged_user");

        if($move == true){
            $logger->log(EventType::RECORDER_CAM_MANAGEMENT, LogLevel::INFO, "camera moved to position : $scene", array(basename(__FILE__)), $asset);
            $plugin["camcontrollers"]->positionMove($scene);
            return true;
        }
        else{
            $logger->log(EventType::RECORDER_CAM_MANAGEMENT, LogLevel::ERROR, "User $user try to set unknown scene  : $scene", array(basename(__FILE__)), $asset);
            return false;
        }
    }
    else{
        return "need_login";
    }
?>