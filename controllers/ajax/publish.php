<?php
    $publishin = $system->removeCharacters($input["publishin"]);
    $nowrecording = json_decode($system->getRecordingStatus(),true);
    $posibilites = array("trash","private","public");

    if($nowrecording["recording_status"] == "stop" && $auth->userSession("logged_user") == $nowrecording["user_login"] && in_array($publishin,$posibilites) == true) {
        $system->prepareMerge($publishin,$nowrecording);
        $system->crontabReset();
    }
    else{
        return "can_t_publish_recording_" . $nowrecording;
    }
?>

