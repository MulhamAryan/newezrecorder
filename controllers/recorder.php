<?php
    if($auth->userIsLoged()){
        if($system->getRecordingStatus() == false) {
            $coursesList = $auth->getUserCourses();

            // Add check options for title and description and last selected options
            //Check if there is a disabled recorder to insert the full menu

            $disableFullList = 0;

            foreach ($recorder_modules as $recorderCheckKey => $recorderCheckValue) {
                if ($recorderCheckValue["enabled"] == false) {
                    $disableFullList = 1;
                }
            }

            include $config["basedir"] . "/" . $config["templates"] . "/recorder.form.php";
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

                if($publishin == 1)
                    $publishalbum = $lang["private_album"];
                else
                    $publishalbum = $lang["public_album"];

            }

            $recorderInfo = $system->getRecorderArray($recorder);

            $ffmpeg = new ffmpeg($recorderInfo, $asset);

            include $config["basedir"] . "/" . $config["templates"] . "/init_recorder.form.php";
        }
    }
    else{
        header("LOCATION:?action=login");
    }
?>