<?php
    include __DIR__ . "/../global_config.inc";
    $logger = new RecorderLogger();

    include $config["main"]->lib . "/cli.class.php";

    $asset_name = $argv[1];
    $recorder = $argv[2];
    $function = $argv[3];

    if($argc != 4){
        echo 'Wrong method : ' . PHP_EOL;
        echo 'You need to have 3 parameters only ' . PHP_EOL;
        echo '- ' . $config["main"]->phpcli . 'cli_post_process.php <asset_name> <recorder> <function_name> ' . PHP_EOL;
        echo '- To check the type of recorder please go to `global_config.inc` in `$recorder_modules[num]["module"]` array or put `all` if you want to encode all the recordings.' . PHP_EOL;
        echo '- Possibly functions :'. PHP_EOL;
        echo "  => startmerge : This function search all the recorders with differents qualities create concat file of recorders and merge them to have one file at the end (auto go `to upload_to_server`)." . PHP_EOL;
        echo "  => upload_to_server : This function send a signal to the main server to start uploading the recording files. " . PHP_EOL;
    }
    else{
        $cli = new cli($asset_name,$recorder);
        if($function == "startmerge"){
            $assetDir = $config["recordermaindir"] . $config["main"]->local_processing . "/" . $asset_name;// Asset directory before being moved to upload_to_server
            $logger->log(EventType::RECORDER_CAPTURE_POST_PROCESSING, LogLevel::INFO, "Started videos post processing", array(basename(__FILE__)), $asset_name);
            $cli->startMerge();
            copy($assetDir . "/_" . $config["main"]->metadata,$assetDir . "/" . $config["main"]->metadata);
            sleep(1);
            rename($assetDir, $config["recordermaindir"] . $config["main"]->upload_to_server . "/" . $asset_name);

            $assetDir = $config["recordermaindir"] . $config["main"]->upload_to_server . "/" . $asset_name;// Asset directory after being moved to upload_to_server
            file_put_contents($assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : $asset_name asset moved successefully to " . $config["main"]->upload_to_server . PHP_EOL, FILE_APPEND | LOCK_EX);
            $cli->bashCommandLine($config["main"]->phpcli . " " . $config["cli_post_process"] . " " . $asset_name . " " . $recorder . " upload_to_server > $assetDir/" . $config["main"]->upload_to_server .".log 2>&1");
        }
        elseif ($function == "upload_to_server"){
            $assetDir = $config["recordermaindir"] . $config["main"]->upload_to_server . "/" . $asset_name;
            $logger->log(EventType::RECORDER_CAPTURE_POST_PROCESSING, LogLevel::INFO, "Starting upload to server", array(basename(__FILE__)), $asset_name);
            file_put_contents($assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : $asset_name starting upload to server" . PHP_EOL, FILE_APPEND | LOCK_EX);
            $cli->startUploadToServer();
        }

    }

?>