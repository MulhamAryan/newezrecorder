<?php
    // This file need a review to be improved !
    $status = $system->removeCharacters($input["status"]);
    $nowrecording = json_decode($system->getRecordingStatus(),true);
    $posibilites = array("init","play","pause","resume","stop");

    if($nowrecording != false && $auth->getLoggedUser() == $nowrecording["user_login"] && in_array($status,$posibilites) == true){
        $recordingStatus = $config["basedir"] . "/" . $config["var"] . "/" . $config["statusfile"];

        $recorderInfo = $system->getRecorderArray($nowrecording["recorders"]);

        $ffmpeg = new ffmpeg($recorderInfo, $nowrecording["asset"]);

        if($status == "play"){
            if($nowrecording["recording_status"] == "pause"){
                $status = "resume";
            }
            else{
                $status = "play";
                $nowrecording["start_time"] = time();
            }
        }
        elseif($status == "pause"){
            $status = "pause";
        }
        elseif($status == "stop"){
            $status = "stop";
            $nowrecording["stop_time"] = time();
        }
        else{
            $status = "init";
        }

        $newArrayValue = array(
            "user_login" => $nowrecording["user_login"],
            "asset" => $nowrecording["asset"],
            "course" => $nowrecording["course"],
            "recording_status" => $status,
            "init_time" => $nowrecording["init_time"],
            "start_time" => $nowrecording["start_time"],
            "auto_stop" => $nowrecording["auto_stop"],
            "stop_time" => $nowrecording["stop_time"],
            "publishin" => $nowrecording["publishin"],
            "recorders" => $nowrecording["recorders"]
        );

        $newArrayValue = json_encode($newArrayValue);
        file_put_contents($recordingStatus, $newArrayValue . PHP_EOL, LOCK_EX);

        if($status){
            $ffmpeg->setMediaStatus($status);
            if($status == "stop")
                $ffmpeg->stopRecording();
        }
        return true;
    }
    else
        return 'no_record_found';
?>