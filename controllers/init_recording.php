<?php
    $course          = isset($input["course"]) ? htmlspecialchars($input["course"]) : "";
    $title           = isset($input["title"]) ? htmlspecialchars($input["title"]) : "";
    $description     = isset($input["description"]) ? htmlspecialchars($input["description"]) : "";
    $recorder        = isset($input["recorder"]) ? htmlspecialchars($input["recorder"]) : "";
    $streaming       = isset($input["streaming"]) ? htmlspecialchars($input["streaming"]) : ""; // Future release TODO
    $advancedoptions = isset($input["advancedoptions"]) ? htmlspecialchars($input["advancedoptions"]) : "";

    $netid = $auth->userSession("logged_user");

    if(empty($title) && empty($course) && empty($recorder)) {
        header("LOCATION:?");
    }
    else{
        if ($advancedoptions == 1) {
            $autostop  = isset($input["autostop"]) ? htmlspecialchars($input["autostop"]) : "";
            $publishin = isset($input["publishin"]) ? htmlspecialchars($input["publishin"]) : "";
        }
        $date = date("Y_m_d_H\hi");
        $asset = $date . "_" . $course;

        $recorderInfo = $system->getRecorderArray($recorder);

        if ($system->getRecordingStatus() == false) {
            $ffmpeg = new ffmpeg($recorderInfo, $asset);
            $ffmpeg->launch();
            $getRunningRecorder = $ffmpeg->getRunningRecorder();
            if($ffmpeg->isRunning("init_check") == "all") {
                $logger->log(EventType::RECORDER_FFMPEG_INIT, LogLevel::INFO, "Successfully initialized recording ($getRunningRecorder).", array("controllers/ini_recording.php"), $asset);
            }
            else{
                $logger->log(EventType::RECORDER_FFMPEG_INIT, LogLevel::ERROR, "Couldn't start recording (not ignored recorder $getRunningRecorder)", array("controllers/ini_recording.php"), $asset);
            }

            //Generate recording status file
            $recStatusArray = array(
                "userLogin" => $auth->userSession("logged_user"),
                "assetName" => $asset,
                "courseName" => $course,
                "initTime" => time(),
                "recStatus" => "init",
                "autoStop" => $advancedoptions,
                "stopTime" => $autostop,
                "publishIn" => 1,
                "recorders" => $recorder
            );

            // This is a temporary patch until we develop the new concept of recording on EZRendrer, EZAdmin and EZManager
            if($recorder == "all")
                $record_type = "camslide";

            elseif($recorder == "camrecord")
                $record_type = "cam";

            elseif($recorder == "sliderecord")
                $record_type = "slide";

            else
                $record_type = "camslide";

            // END OF THE PATCH
            $metaInfo = array(
                "course_name" => "" . $course . "",
                "origin" => "" . $config["classroom"] . "",
                "title" => "" . $title . "",
                "description" => " ",
                "record_type" => "" . $record_type . "",
                "moderation" => "false",
                "author" => '' . $auth->getUserInfo("info",$netid,"full_name") . '',
                "netid" => "" . $netid . "",
                "record_date" => "" . $date . "",
                "streaming" => "false",
                "super_highres" => "false"
            );
            $system->recStatus($recStatusArray);
            $system->generateMetadataFile($metaInfo,$asset);
            $session->setRecordingInfo($netid, $course, $title, $description, $recorder,$advancedoptions,$autostop,$publishin);

        }

        header("LOCATION:?");

        include $tmp->loadFile("init_recorder.form.php");
    }
?>