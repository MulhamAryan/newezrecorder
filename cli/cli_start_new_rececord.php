<?php
    include __DIR__ . "/../global_config.inc";
    $logger = new RecorderLogger();

    $username   = $argv[1];
    $course     = $argv[2];
    $rec        = $argv[3];
    $recordtime = $argv[4];
    $title      = $argv[5];

    $possible_rec = array('all');

    foreach ($recorder_modules as $recorder) {
        $possible_rec[] = $recorder["module"];
    }

    if($argc != 6){
        echo 'Wrong method : ' . PHP_EOL;
        echo "Command should be : " . $config["main"]->phpcli . " " . basename(__FILE__) . " <username> <courseid> <recorder> <stopafter> '<title>'" . PHP_EOL;
        echo "-> <username>  : usernetID should exists in user list." . PHP_EOL;
        echo "-> <courseid>  : Should be in user course list." . PHP_EOL;
        echo "-> <title>     : Course title should be between two simple quote to take spaces 'example of title'" . PHP_EOL;
        echo "-> <recorder>  : Possible value are ";
        foreach ($possible_rec as $recorder) {
            echo "'" . $recorder . "',";
        }
        echo PHP_EOL;
        echo "-> <stopafter> : Should be like hh:mm" . PHP_EOL;
    }
    elseif($auth->getUserInfo("info",$username,"full_name") == NULL){
        echo "USERNAME : $username not found in courselist" . PHP_EOL;
    }
    elseif ($auth->getUserInfo("courses",$username,$course) == NULL){
        echo "This course $course is not in $username's list" . PHP_EOL;
    }
    elseif(!in_array($rec,$possible_rec)){
        echo "This recorder $rec should be one of ";
        foreach ($possible_rec as $list) {
            echo "'" . $list . "',";
        }
        echo PHP_EOL;
    }
    elseif ($system->getRecordingStatus() == true) {
        $recinfo = json_decode($system->getRecordingStatus(), true);
        echo "Can't start new record session another session is recording now : " . $recinfo["user_login"] . " - " . $recinfo["course"] . " - " . date("d/m/Y H:i:s",$recinfo["init_time"]) . "" . PHP_EOL;
    }
    else{
        // This is a temporary patch until we develop the new concept of recording on EZRendrer, EZAdmin and EZManager
        if($rec == "all")
            $record_type = "camslide";

        elseif($rec == "camrecord")
            $record_type = "cam";

        elseif($rec == "sliderecord")
            $record_type = "slide";

        else
            $record_type = "camslide";
        // END OF THE PATCH

        $date = date("Y_m_d_H\hi");
        $asset = $date . "_" . $course;
        $recorderInfo = $system->getRecorderArray($rec);

        echo "Creating new record : " . $asset . PHP_EOL;

        $ffmpeg = new ffmpeg(array("asset" => $asset, "recorder_info" => $recorderInfo));
        $ffmpeg->launch();

        $nowrecording = json_decode($system->getRecordingStatus(),true);

        $getRunningRecorder = $ffmpeg->getRunningRecorder();
        if($ffmpeg->isRunning("init_check") == "all") {
            $logger->log(EventType::RECORDER_FFMPEG_INIT, LogLevel::INFO, "Successfully initialized recording ($getRunningRecorder).", array(__FILE__), $asset);
            echo "- Successfully initialized recording ($getRunningRecorder).";
        }
        else{
            $logger->log(EventType::RECORDER_FFMPEG_INIT, LogLevel::ERROR, "Couldn't start recording (not ignored recorder $getRunningRecorder)", array(__FILE__), $asset);
            echo "- Couldn't start recording (not ignored recorder $getRunningRecorder)";
        }
        $autostop = "";//TODO
        //Generate recording status file
        $recStatusArray = array(
            "userLogin" => $username,
            "assetName" => $asset,
            "courseName" => $course,
            "initTime" => time(),
            "recStatus" => "init",
            "autoStop" => 1,
            "stopTime" => $autostop,
            "publishIn" => 1,
            "recorders" => $rec
        );

        $metaInfo = array(
            "course_name" => "" . $course . "",
            "origin" => "" . $config["main"]->classroom . "",
            "title" => "" . $title . "",
            "description" => " ",
            "record_type" => "" . $record_type . "",
            "moderation" => "false",
            "author" => '' . $auth->getUserInfo("info",$username,"full_name") . '',
            "netid" => "" . $username . "",
            "record_date" => "" . $date . "",
            "streaming" => "false",
            "super_highres" => "false"
        );
        echo "- Starting new recording session" . PHP_EOL;
        $system->recStatus($recStatusArray);
        echo "- Generating metadata file" . PHP_EOL;
        $system->generateMetadataFile($metaInfo,$asset);
        echo "- Setting session to close the recorder" . PHP_EOL;
        $session->setRecordingInfo($username, $course, $title, " ", $rec,1,$autostop,1);
        /*sleep(3);
        echo "- Creating the cronjob to stop after "; //TODO
        $nowrecording["start_time"] = time();
        $jobInfo = array("time"=> $nowrecording["stop_time"]);
        $system->createJob($jobInfo);
        $logger->log(EventType::RECORDER_CREATE_CRONTAB, LogLevel::NOTICE, "Cronjob is enabled : stop after $autostop and publish in private album", array(basename(__FILE__)), $asset);

        $getRunningRecorder = $ffmpeg->getRunningRecorder();
        $logger->log(EventType::ASSET_CREATED, LogLevel::NOTICE,"Starting recorders by $username request. Requested type: $getRunningRecorder", array(basename(__FILE__)), $asset, $username, $rec , $course, $config["main"]->classroom);
        echo "- Recording started now :)";
        */
    }
?>