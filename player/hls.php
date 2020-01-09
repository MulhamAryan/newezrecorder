<?php
    include __DIR__ . "/../global_config.inc";
    header('Content-Type: application/octet-stream');
    $file = $_GET["filename"];
    $file = preg_replace('/[^a-zA-Z0-9.]/i', '', $file);

    $asset = $_GET["asset"];
    $asset = preg_replace('/[^a-zA-Z0-9_-]/i', '_', $asset);

    $recorder = $_GET["recorder"];
    $possibleRecorder = array("camrecord","sliderecord"); //Temporary Patch//

    $type = $_GET["type"];

    $hls_rep = $config["recordermaindir"] . $config["local_processing"] . "/" . $asset . "/" . $recorder ."/" . $type . "/" . $file;
    if(file_exists($hls_rep))
        echo file_get_contents($hls_rep);

?>