<?php
    $course = htmlspecialchars($input["course"]);
    $title = htmlspecialchars($input["title"]);
    $description = htmlspecialchars($input["description"]);
    $recorder = htmlspecialchars($input["recorder"]);
    $streaming = htmlspecialchars($input["streaming"]); // Future release
    $advancedoptions = htmlspecialchars($input["advancedoptions"]);

    if($advancedoptions == 1){
        $autostop = htmlspecialchars($input["autostop"]);
        $publishin = htmlspecialchars($input["publishin"]);
    }
    $date = date("Y_m_d_H\hi");
    $asset = $date . "_" . $course;

    if($recorder != "all"){

        foreach ($recorder_modules as $recorderKey => $recorderValue){
            if($recorderValue["module"] == $recorder){
                if($recorderValue["enabled"] == true) {
                    $newRecorder = $recorderValue;
                }
            }
        }
        $recorderInfo[] = $newRecorder;
    }
    else{
        $recorderInfo = $recorder_modules;
    }

    /*$ffmpeg = new ffmpeg($recorderInfo,$asset);
    $ffmpeg->launch();
    sleep(5);*/
    include $config["basedir"] . "/" . $config["templates"] . "/init_recorder.form.php";
?>