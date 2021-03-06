<?php
    if($auth->userSession("is_logged")){
        if($system->getRecordingStatus() == false) {
            $netID = $auth->userSession("logged_user");
            $coursesList = $auth->getUserInfo("courses",$netID);

            if(!empty($session->getLastRecordingInfo($netID))) {
                $lastTitle = $session->getLastRecordingInfo($netID)->title;
                $lastCourse = $session->getLastRecordingInfo($netID)->course;
                $lastRecorder = $session->getLastRecordingInfo($netID)->record_type;
                $lastDescription = $session->getLastRecordingInfo($netID)->description;
                $lastAdvancedOptions = $session->getLastRecordingInfo($netID)->advanced_options;
                $lastAutoStopTime = $session->getLastRecordingInfo($netID)->auto_stop_time;
                $lastAutoPublishIn = $session->getLastRecordingInfo($netID)->publishin;
            }

            $lastTitle = (!empty($lastTitle) ? $lastTitle : "");
            $lastCourse = (!empty($lastCourse) ? $lastCourse : "");
            $lastRecorder = (!empty($lastRecorder) ? $lastRecorder : "all");
            $lastDescription = (!empty($lastDescription) ? $lastDescription : "");
            $lastAdvancedOptions = (!empty($lastAdvancedOptions) ? $lastAdvancedOptions : "");
            $lastAutoStopTime = (!empty($lastAutoStopTime) ? $lastAutoStopTime : "02:00");
            $lastAutoPublishIn = (!empty($lastAutoPublishIn) ? $lastAutoPublishIn : 2);

            $disableFullList = 0;

            foreach ($recorder_modules as $recorderCheckKey => $recorderCheckValue) {
                if ($recorderCheckValue["enabled"] == false) {
                    $disableFullList = 1;
                }
            }

            include $tmp->loadTempFile("recorder.form.php");
        }
        else{
            $recordingInfo = $system->getRecordingStatus();
            $recordingInfo = json_decode($recordingInfo,true);

            $recorder = $recordingInfo["recorders"];
            $asset = $recordingInfo["asset"];
            $course = $recordingInfo["course"];
            $recordingstatus = $recordingInfo["recording_status"];
            $inittime = $recordingInfo["init_time"];
            $start_time = $recordingInfo["start_time"];
            $autostop = $recordingInfo["auto_stop"];

            if($autostop == 1){
                $stoptime = $recordingInfo["stop_time"];
                $publishin = $recordingInfo["publishin"];
                list($hour,$minute) = explode(":",$stoptime);
                $totimestamp = (($hour*60*60)+($minute*60));
                $publishalbum = ($publishin == 1 ? $lang["private_album"] : $lang["public_album"]);
            }

            $recorderInfo = $system->getRecorderArray($recorder);

            $ffmpeg = new ffmpeg(array("asset" => $asset, "recorder_info" => $recorderInfo, "streaming" => $recordingInfo["streaming"]));

            if(!empty($recordingInfo["start_time"]) && !empty($recordingInfo["auto_stop"])) {
                $recordingInfo["stop_time"] = explode(":", $recordingInfo["stop_time"]);
                $converted = ((int)$recordingInfo["stop_time"][0] * 60 * 60) + ((int)$recordingInfo["stop_time"][1] * 60);
                $startedConverted = date('H:i:s', $recordingInfo["start_time"] + $converted);
                $converted = $startedConverted;
            }
            else{
                $startedConverted = (!empty($startedConverted) ? $converted:"");
            }

            include $tmp->loadTempFile("init_recorder.form.php");
        }
    }
    else{
        header("LOCATION:?action=login");
    }
?>