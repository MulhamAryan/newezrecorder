<?php
    include __DIR__ . "/../global_config.inc";
    $logger = new RecorderLogger();

    include  $config["main"]->lib . "/cli.class.php";

    if($argc != 2) {
        echo "Wrong arg count";
        exit(1);
    }

    if($argc != 2) {
        echo "Wrong arg count";
        exit(1);
    }

    $asset = $argv[1];

    //move asset folder from upload_to_server to upload_ok dir
    $ok = rename($config["recordermaindir"] . "/" .$config["main"]->upload_to_server . "/" . $asset,$config["recordermaindir"] . "/" .$config["main"]->upload_ok . "/" . $asset);
    if(!$ok) {
        $logger->log(EventType::RECORDER_UPLOAD_TO_EZCAST, LogLevel::CRITICAL, "Could not move asset folder from upload_to_server to upload_ok dir (failed on local or on remote)", array(basename(__FILE__)), $asset);
        return false;
    }
    else{
        $logger->log(EventType::RECORDER_UPLOAD_TO_EZCAST, LogLevel::INFO, "Local asset moved from " . $config["main"]->upload_to_server . " to " . $config["main"]->upload_ok . " dir", array(basename(__FILE__)), $asset);
        return true;
    }
?>