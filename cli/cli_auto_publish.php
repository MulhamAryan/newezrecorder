<?php
    include __DIR__ . "/../global_config.inc";

    $nowrecording = json_decode($system->getRecordingStatus(),true);
    $publishin = $nowrecording["publishin"];
    $posibilites = array("trash","private","public");
    if(!empty($nowrecording)) {
        $publishin = ($publishin == 1 ? "private" : "public");
        $ffmpeg = new ffmpeg($nowrecording["asset"],$nowrecording["recorders"]);
        $ffmpeg->setMediaStatus("stop"); // Set stop of all recording directories
        $ffmpeg->stopRecording(); // Stop and kill all the recordings
        $system->prepareMerge($publishin, $nowrecording); // Prepare the merge functions to launch cli_post_process.php
        $system->crontabReset(); // Reset and remove all crontab jobs
    }
    else{
        return "recjsonfilenotfound";
    }
?>