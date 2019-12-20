<?php
    $course = htmlspecialchars($input["course"]);
    $title = htmlspecialchars($input["title"]);
    $description = htmlspecialchars($input["description"]);
    $recorder = htmlspecialchars($input["recorder"]);
    $streaming = htmlspecialchars($input["streaming"]); // Future release
    $advancedoptions = htmlspecialchars($input["advancedoptions"]);
    $netid = $auth->getLoggedUser();
    if(empty($title) && empty($course) && empty($recorder)) {
        header("LOCATION:?");
    }
    else{
        if ($advancedoptions == 1) {
            $autostop = htmlspecialchars($input["autostop"]);
            $publishin = htmlspecialchars($input["publishin"]);
            echo $autostop;
        }
        $date = date("Y_m_d_H\hi");
        $asset = $date . "_" . $course;

        $recorderInfo = $system->getRecorderArray($recorder);

        if ($system->getRecordingStatus() == false) {
            $ffmpeg = new ffmpeg($recorderInfo, $asset);
            $ffmpeg->launch();
            //Generate recording status file
            $recStatusArray = array(
                "userLogin" => $auth->getLoggedUser(),
                "assetName" => $asset,
                "courseName" => $course,
                "initTime" => time(),
                "recStatus" => "init",
                "autoStop" => $advancedoptions,
                "stopTime" => $autostop,
                "publishIn" => $publishin,
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
                "description" => " " . $description . "",
                "record_type" => "" . $record_type . "",
                "moderation" => "false",
                "author" => '' . $auth->getUserInfo($netid,"full_name") . '',
                "netid" => "" . $netid . "",
                "record_date" => "" . $date . "",
                "streaming" => "false",
                "super_highres" => "false"
            );
            $system->recStatus($recStatusArray);
            $system->generateMetadataFile($metaInfo,$asset);
        }

        header("LOCATION:?");

        include $config["basedir"] . "/" . $config["templates"] . "/init_recorder.form.php";
    }
?>