<?php
    include __DIR__ . "/../global_config.inc";

    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/octet-stream');

    $asset = $_GET["asset"];
    $asset = preg_replace('/[^a-zA-Z0-9_-]/i', '_', $asset);
    $assetDir = $config["recordermaindir"] . $config["local_processing"] . "/" . $asset;
    $recorder = $_GET["recorder"];
    $possibleRecorder = array("camrecord","sliderecord"); //Temporary Patch//
    $type = $_GET["type"];

    $link = $config["playerlink"] . "/player.php?";
    if(file_exists($assetDir)){
        $link .= "asset=$asset";
        if(in_array($recorder,$possibleRecorder)){
            $link .= "&recorder=$recorder";
            if(array_key_exists($type,$recorder_modules[0]["quality"]) && !empty($type)){
                $link .= "&type=$type";
                echo "#EXTM3U" . PHP_EOL;
                echo "#EXT-X-VERSION:3" . PHP_EOL;
                echo "#EXT-X-STREAM-INF:BANDWIDTH=150000,RESOLUTION=416x234,CODECS=\"avc1.42e00a,mp4a.40.2\"" . PHP_EOL;
                echo $link;
            }
            else{
                echo "error_type_not_found";
            }
        }
        else{
            echo "error_recorder_not_found";
        }

    }
    else{
        echo "error_asset_dir_not_found";
    }
?>