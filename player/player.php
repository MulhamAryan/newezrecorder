<?php
    include "/Library/newezrecorder/global_config.inc";

    header('Content-Type: application/octet-stream');
    $asset = $_GET["asset"];
    $asset = preg_replace('/[^a-zA-Z0-9_-]/i', '_', $asset);
    $recorder = $_GET["recorder"];
    $possibleRecorder = array("camrecord","sliderecord"); //Temporary Patch//
    $type = $_GET["type"];
    $asset = $_GET["asset"];
    $recorder = $_GET["recorder"];
    $type = $_GET["type"];

    $m3u8_rep = $config["recordermaindir"] . $config["local_processing"] . "/" . $asset . "/" . $recorder ."/" . $type . "/ffmpegmovie.m3u8";

    $m3u8 = file($m3u8_rep);

    $m3u8 = str_replace("ffmpegmovie",$config["playerlink"] . "/hls.php?asset=$asset&recorder=$recorder&type=$type&filename=ffmpegmovie",$m3u8);

    foreach ($m3u8 as $m3u8Content){
        echo $m3u8Content;
    }
 ?>
