<?php
    $array = array(
        "classroom" => $config["classroom"],
        "ip"        => $config["recorderip"],
        "date"      => time(),
        "info"      => array()
    );
    $scanRecordMainDir = array($config["local_processing"],$config["upload_to_server"]);
    foreach ($scanRecordMainDir as $smd){
        $scanDirOutput = $system->bashCommandLine("ls " . $config["recordermaindir"] . "/" . $smd);
        $array["info"][$smd] = $scanDirOutput;
    }
    echo json_encode($array);
?>