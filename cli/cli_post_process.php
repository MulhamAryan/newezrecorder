<?php
    include __DIR__ . "/../global_config.inc";
    $logger = new RecorderLogger();

    include $config["lib"] . "/cli.class.php";

    $asset_name = $argv[1];
    $recorder = $argv[2];
    $function = $argv[3];

    if($argc != 4){
        echo 'Wrong method : ' . PHP_EOL;
        echo 'You need to have 3 parameters only ' . PHP_EOL;
        echo '- ' . $config["phpcli"] . 'cli_post_process.php <asset_name> <recorder> <function_name> ' . PHP_EOL;
        echo '- To check the type of recorder please go to `global_config.inc` in `$recorder_modules[num]["module"]` array or put `all` if you want to encode all the recordings.' . PHP_EOL;
        echo '- Possibly functions :'. PHP_EOL;
        echo "  => startmerge : This function search all the recorders with differents qualities create concat file of recorders and merge them to have one file at the end (auto go `to upload_to_server`)." . PHP_EOL;
        echo "  => upload_to_server : This function send a signal to the main server to start uploading the recording files. " . PHP_EOL;
    }
    else{
        $cli = new cli($asset_name,$recorder);
        if($function == "startmerge"){
            $logger->log(EventType::RECORDER_CAPTURE_POST_PROCESSING, LogLevel::INFO, "Started videos post processing", array(basename(__FILE__)), $asset_name);
            $cli->startMerge();
            $assetDir = $config["recordermaindir"] . $config["local_processing"] . "/" . $asset_name;
            copy($assetDir . "/_" . $config["metadata"],$assetDir . "/" . $config["metadata"]);
            sleep(2);
            $uploadToServer = $config["phpcli"] . " " . $config["cli_post_process"] . " " . $asset_name . " " . $recorder . " upload_to_server>$assetDir/post_process.log 2>&1";
            $cli->bashCommandLine($uploadToServer);
        }
        elseif ($function == "upload_to_server"){
            $assetDir = $config["recordermaindir"] . $config["local_processing"] . "/" . $asset_name;
            $logger->log(EventType::RECORDER_CAPTURE_POST_PROCESSING, LogLevel::INFO, "Starting upload to server", array(basename(__FILE__)), $asset_name);
            $cli->startUploadToServer();

        }

    }

?>