<?php
    include __DIR__ . "/../global_config.inc";
    $array = array(
        "classroom" => $config["classroom"],
        "ip"        => $config["recorderip"],
        "date"      => time(),
        "info"      => array()
    );
    $scanRecordMainDir = array($config["local_processing"],$config["upload_to_server"]);
    foreach ($scanRecordMainDir as $smd){
        $scanDir = exec("ls " . $config["recordermaindir"] . "/" . $smd,$scanDirOutput,$stat2);
        $array["info"][$smd] = $scanDirOutput;
    }

    file_put_contents($config["machinelog"] . "/blockedrecording.json", json_encode($array));
