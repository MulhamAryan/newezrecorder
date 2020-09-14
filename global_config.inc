<?php
    /* ------
        Refracted EZrecorder Beta Test Version
    * -------*/

    date_default_timezone_set("Europe/Brussels");
    $config_version = "20200083101";
    $input = array_merge($_GET, $_POST);
    if(isset($input['action']) && !empty($input['action'])) {
        $action = $input['action'];
    }
    else{
        $action = "";
    }

    $config = array(
        "basedir" => __DIR__ . "/"
    );

    $config["configurations_file"]   = $config["basedir"] . "/etc/config/config.json"; // Recorder main configurations file

    // Recorder main configuration
    $config["main"]   = json_decode(file_get_contents($config["configurations_file"]));

    // EZrecorder recording informations files and folders ----------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["recordermaindir"]       = $config["main"]->recorderdata . "/movies/"; // Where all recording are stocked
    $config["var"]                   = $config["main"]->recorderdata . "/var/"; // Where all temporary files and recording info stocked
    $config["machinelog"]            = $config["main"]->recorderdata . "/log/"; // EZrecorder machine logs
    $config["recorderurl"]           = "http://". $config["main"]->recorderip . "/" . $config["main"]->htdocspath; // EZrecorder main adresse

    // Different informations of user configuration -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["passwordfile"]          = $config["basedir"] . "/etc/users/htpasswd"; // local password file
    $config["adminlist"]             = $config["basedir"] . "/etc/users/admin.inc"; // local admin list file
    $config["courselist"]            = $config["basedir"] . "etc/users/courselist.php"; // local users courses

    // Main EZrecorder configuration -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["main"]->lib             = $config["basedir"] . "/" . $config["main"]->lib . "/";
    $config["main"]->controllers     = $config["basedir"] . "/" . $config["main"]->controllers . "/";
    $config["ajax"]                  = $config["main"]->controllers . "/" . $config["main"]->ajaxdir; // Ajax files controllers
    $config["recorder"]              = $config["basedir"] . "/etc/config/recorder.json"; // Recorders information and qualities can be changed from this files
    $config["listplugins"]           = $config["basedir"] . "/etc/config/plugins.json"; // Active plugins and plugins list are stocked in this file
    $config["languagesls"]           = $config["basedir"] . "/etc/config/languages.json"; // Active plugins and plugins list are stocked in this file
    $config["alert_msg_file"]        = $config["var"] . "/alert.json"; // When this file exist it mean there is an alert for the user before starting recording
    $config["database_file"]         = $config["var"] . "/db.sqlite"; // SQLite database file
    $config["main"]->languages       = $config["basedir"] . "/" . $config["main"]->languages; // All languages files directory
    $config["curenttheme"]           = $config["main"]->templates . "/" . $config["main"]->theme; // Main theme directory
    $config["playerlink"]            = $config["recorderurl"] . "/" . $config["main"]->player; // M3U8 direct player while recording

    // Internal CLI files ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["cli_post_process"]      = $config["basedir"] . "/" . $config["main"]->clidir . "/cli_post_process.php"; // After user click on stop and publish recording this file start to render the recording and upload it
    $config["cli_stream_send"]       = $config["basedir"] . "/" . $config["main"]->clidir . "/cli_stream_send.php"; // If streaming is enable this file send all the recording different qualities to main server
    $config["cli_auto_publish"]      = $config["basedir"] . "/" . $config["main"]->clidir . "/cli_auto_publish.php"; // When user active auto publish option this file will be executed to auto publish the recording
    $config["cli_sync_logs"]         = $config["basedir"] . "/" . $config["main"]->clidir . "/cli_sync_logs.php"; // This is the log synchronize file

    // Main server urls ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["ezmanagerurl"]          = $config["main"]->serverurl . "/ezmanager/"; // Main url of the server
    $config["ezcast_submit_url"]     = $config["ezmanagerurl"] . "recorder/index.php"; // Command file for recording options
    $config["ezcast_logs_url"]       = $config["ezmanagerurl"] . "recorder/logs.php"; // Log file for recording options
    $config["last_log_sent_get_url"] = $config["ezcast_logs_url"] . "?action=last_log_sent&source=".$config["main"]->classroom; // Log file for recording options
    $config["log_push_url"]          = $config["ezcast_logs_url"] . "?action=push_logs"; // Log file for recording options

    // To access to machine log from user frontpage ------------------------------------
    $config["apiactive"]             = 0; // If 1 api activated other not activated
    $config["apiuser"]               = "user"; // Api username
    $config["apikey"]                = "password"; // Api password

    // Recorders and qualities info getter -------------------------------------------------
    $recorder_modules = json_decode(file_get_contents($config["recorder"]),true);

    // Plugins list info getter ------------------------------------------------------------
    $plugins_list     = json_decode(file_get_contents($config["listplugins"]),true);

    if($config["main"]->debug_mode == true){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    include $config["main"]->lib . "/loader.php";