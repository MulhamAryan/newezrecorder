<?php
    // This file need a review to be improved !
    $status = $system->removeCharacters($input["status"]);
    $nowrecording = json_decode($system->getRecordingStatus(),true);
    $posibilites = array("init","play","pause","resume","stop");
    $user = $auth->userSession("logged_user");

    if($nowrecording != false && $auth->userSession("logged_user") == $nowrecording["user_login"] && in_array($status,$posibilites) == true){
        $logger = new RecorderLogger();

        $recordingStatus = $config["var"] . "/" . $config["statusfile"];

        $recorderInfo = $system->getRecorderArray($nowrecording["recorders"]);

        $ffmpeg = new ffmpeg($nowrecording["asset"],$recorderInfo);

        if($status == "play"){
            if($nowrecording["recording_status"] == "pause"){
                $status = "resume";
                $logger->log(EventType::RECORDER_PAUSE_RESUME, LogLevel::INFO, "Recording resumed by $user", array(basename(__FILE__)), $nowrecording["asset"]);
            }
            else{
                $status = "play";
                $nowrecording["start_time"] = time();
                if($nowrecording["auto_stop"] == 1){
                    $jobInfo = array(
                        "time"=> $nowrecording["stop_time"]
                    );
                    $system->createJob($jobInfo);
                    $publishin = ($nowrecording["publishin"] == "true" ? "public album":"private album");
                    $logger->log(EventType::RECORDER_CREATE_CRONTAB, LogLevel::NOTICE, "Cronjob is enabled : stop after " . $nowrecording["stop_time"] . " and publish in " . $publishin, array(basename(__FILE__)), $nowrecording["asset"]);
                }
                $getRunningRecorder = $ffmpeg->getRunningRecorder();
                $logger->log(EventType::ASSET_CREATED, LogLevel::NOTICE,"Starting recorders by $user request. Requested type: $getRunningRecorder", array(basename(__FILE__)), $nowrecording["asset"], $user, $nowrecording["recorders"] , $nowrecording["course"], $config["classroom"]);
            }
        }
        elseif($status == "pause"){
            $status = "pause";
            $logger->log(EventType::RECORDER_PAUSE_RESUME, LogLevel::INFO, "Recording was paused by $user", array(basename(__FILE__)), $nowrecording["asset"]);
        }
        elseif($status == "stop"){
            $status = "stop";
            $nowrecording["stop_time"] = time();
            $logger->log(EventType::RECORDER_PUSH_STOP, LogLevel::NOTICE, "Recording was stopped by user $user", array(basename(__FILE__)), $nowrecording["asset"]);
        }
        else{
            $status = "init";
            $logger->log(EventType::RECORDER_PUSH_STOP, LogLevel::ALERT, "User $user try to launch unknow status " . $system->removeCharacters($input["status"]), array(basename(__FILE__)), $nowrecording["asset"]);
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
            "recorders" => $nowrecording["recorders"],
            "streaming" => $nowrecording["streaming"]
        );

        $newArrayValue = json_encode($newArrayValue);
        file_put_contents($recordingStatus, $newArrayValue . PHP_EOL, LOCK_EX);

        if($status){
            $ffmpeg->setMediaStatus($status);
            if($status == "stop") {
                $ffmpeg->stopRecording();
                sleep(1);
                //Check if there is some recording running
                if($ffmpeg->isRunning() != false){
                    foreach ($ffmpeg->isRunning() as $runningRecord) {
                        if($ffmpeg->killPid($runningRecord) == true){
                            $logger->log(EventType::RECORDER_PUSH_STOP, LogLevel::NOTICE, "Stopping ffmpeg after double check : $runningRecord ", array(basename(__FILE__)), $nowrecording["asset"]);
                        }
                        else
                            $logger->log(EventType::RECORDER_PUSH_STOP, LogLevel::ALERT, "System couldn't stop the recording : $runningRecord", array(basename(__FILE__)), $nowrecording["asset"]);
                    }
                }
                else{
                    $logger->log(EventType::RECORDER_PUSH_STOP, LogLevel::NOTICE, "Recording stoped without problems.", array(basename(__FILE__)), $nowrecording["asset"]);
                }
            }
        }
        return true;
    }
    else
        return 'no_record_found';
?>