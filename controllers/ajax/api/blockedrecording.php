<?php
    $array = array(
        "classroom" => $config["main"]->classroom,
        "ip"        => $config["main"]->recorderip,
        "date"      => time(),
        "info"      => array()
    );
    $scanRecordMainDir = array($config["main"]->local_processing,$config["main"]->upload_to_server);
    foreach ($scanRecordMainDir as $smd){
        $scanDirOutput = $system->bashCommandLine("ls " . $config["recordermaindir"] . "/" . $smd);
        $array["info"][$smd] = $scanDirOutput;
    }
    echo json_encode($array);
?>