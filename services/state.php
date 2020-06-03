<?php
    include __DIR__ . "/../global_config.inc";
    if(file_exists($config["var"] . "/" . $config["statusfile"])) {
        $recordingInfo = json_decode(file_get_contents($config["var"] . "/" . $config["statusfile"]),true); //TODO there is a function system class can do that
        //PATCH
        if($recordingInfo["recording_status"] == "play")
            $recordingInfo["recording_status"] = "recording";

        elseif($recordingInfo["recording_status"] == "pause")
            $recordingInfo["recording_status"] = "paused";

        else
            $recordingInfo["recording_status"] = "open";

        if($recordingInfo["recorders"] == "camrecord")
            $recordingInfo["recorders"] = "cam";

        elseif($recordingInfo["recorders"] == "sliderecord")
            $recordingInfo["recorders"] = "slide";

        else
            $recordingInfo["recorders"] = "camslide";
        //END OF THE PATCH
        $recordingArray = array(
            "recording" => "1",
            "status_general" => "recording",
            "status_cam" => $recordingInfo["recording_status"],
            "status_slides" => $recordingInfo["recording_status"],
            "author" => $recordingInfo["user_login"],
            "author_full_name" => $recordingInfo["user_login"],
            "asset" => $recordingInfo["asset"],
            "course" => $recordingInfo["course"],
            "streaming" => $recordingInfo["streaming"],
            "record_type" => $recordingInfo["recorders"]
        );
    }
    else{
        $recordingArray = array(
            "recording" => "0",
            "status_general" => "",
            "status_cam" => "",
            "status_slides" => "",
            "author" => "",
            "author_full_name" => "",
            "asset" => "",
            "course" => "",
            "streaming" => "",
            "record_type" => ""
        );
    }
    echo json_encode($recordingArray);
?>


