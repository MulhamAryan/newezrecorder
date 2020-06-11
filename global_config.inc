<?php
    /* ------
        Refracted EZrecorder Beta Test Version
    */
    error_reporting(E_ALL | E_STRICT); // Only on debug mode !
    ini_set('display_errors', '1');    // Only on debug mode !
    date_default_timezone_set("Europe/Brussels");
    $config_version = "2020030101";
    $input = array_merge($_GET, $_POST);
    if(isset($input['action']) && !empty($input['action'])) {
        $action = $input['action'];
    }
    else{
        $action = "";
    }

    $config = array(
        "classroom"        => "mulham",
        "userweb"          => "_www",
        "apacheusername"   => "podclient",
        "recorderip"       => "10.142.128.209",
        "htdocspath"       => "ezrecorder",
        "serverurl"        => "podcasttest.ulb.ac.be",
        "basedir"          => __DIR__ . "/",
        "webbasedir"       => "/var/www/html/",
        "recorderdata"     => "/var/www/recorderdata/",
        //
        "controllers"      => "controllers",
        "ajaxdir"          => "ajax",
        "lib"              => "library",
        "clidir"           => "cli",
        "plugins"          => "plugins",
        "templates"        => "templates",
        "languages"        => "languages",
        "services"         => "services",
        "htdocs"           => "htdocs",
        "player"           => "player",
        "theme"            => "refracted",
        //
        "local_processing" => "local_processing",
        "trash"            => "trash",
        "upload_to_server" => "upload_to_server",
        "upload_ok"        => "upload_ok",
        //
        "adminmail"        => "podcast.tech@ulb.ac.be",
        "phpcli"           => "/usr/bin/php",
        "ffmpegcli"        => "/usr/local/ffmpeg/ffmpeg",
        "crontab"          => "/usr/bin/crontab",
        "ps"               => "/bin/ps",
        "ping"             => "/bin/ping",
        "downloadprotocol" => "rsync",
        //
        "statusfile"       => "recording.json",
        "metadata"         => "metadata.xml",
        "request_dump"     => "download_request_dump.txt",
        "moviefile"        => "ffmpegmovie",
        "crontabuserfile"  => "crontabfile",
        //
        "enablestreaming"  => false
    );
    // EZrecorder recording informations files and folders ----------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["recordermaindir"]       = $config["recorderdata"] . "/movies/"; // Where all recording are stocked
    $config["var"]                   = $config["recorderdata"] . "/var/"; // Where all temporary files and recording info stocked
    $config["machinelog"]            = $config["recorderdata"] . "/log/"; // EZrecorder machine logs
    $config["recorderurl"]           = "http://". $config["recorderip"] . "/" . $config["htdocspath"]; // EZrecorder main adresse
    // Different informations of user configuration -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["passwordfile"]          = $config["basedir"] . "/etc/users/htpasswd"; // local password file
    $config["adminlist"]             = $config["basedir"] . "/etc/users/admin.inc"; // local admin list file
    $config["courselist"]            = $config["basedir"] . "etc/users/courselist.php"; // local users courses
    // Main EZrecorder configuration -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["lib"]                   = $config["basedir"] . "/" . $config["lib"] . "/";
    $config["controllers"]           = $config["basedir"] . "/" . $config["controllers"] . "/";
    $config["ajax"]                  = $config["controllers"] . "/" . $config["ajaxdir"]; // Ajax files controllers
    $config["recorder"]              = $config["basedir"] . "/etc/config/recorder.json"; // Recorders information and qualities can be changed from this files
    $config["listplugins"]           = $config["basedir"] . "/etc/config/plugins.json"; // Active plugins and plugins list are stocked in this file
    $config["languagesls"]           = $config["basedir"] . "/etc/config/languages.json"; // Active plugins and plugins list are stocked in this file
    $config["alert_msg_file"]        = $config["var"] . "/alert.json"; // When this file exist it mean there is an alert for the user before starting recording
    $config["database_file"]         = $config["var"] . "/db.sqlite"; // SQLite database file
    $config["languages"]             = $config["basedir"] . "/" . $config["languages"]; // All languages files directory
    $config["curenttheme"]           = $config["templates"] . "/" . $config["theme"]; // Main theme directory
    $config["playerlink"]            = $config["recorderurl"] . "/" . $config["player"]; // M3U8 direct player while recording
    // Internal CLI files ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["cli_post_process"]      = $config["basedir"] . "/" . $config["clidir"] . "/cli_post_process.php"; // After user click on stop and publish recording this file start to render the recording and upload it
    $config["cli_stream_send"]       = $config["basedir"] . "/" . $config["clidir"] . "/cli_stream_send.php"; // If streaming is enable this file send all the recording different qualities to main server
    $config["cli_auto_publish"]      = $config["basedir"] . "/" . $config["clidir"] . "/cli_auto_publish.php"; // When user active auto publish option this file will be executed to auto publish the recording
    $config["cli_sync_logs"]         = $config["basedir"] . "/" . $config["clidir"] . "/cli_sync_logs.php"; // This is the log synchronize file
    // Main server urls ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $config["ezmanagerurl"]          = "http://" . $config["serverurl"] . "/ezmanager/"; // Main url of the server
    $config["ezcast_submit_url"]     = $config["ezmanagerurl"] . "recorder/index.php"; // Command file for recording options
    $config["ezcast_logs_url"]       = $config["ezmanagerurl"] . "recorder/logs.php"; // Log file for recording options
    $config["last_log_sent_get_url"] = $config["ezcast_logs_url"] . "?action=last_log_sent&source=".$config["classroom"]; // Log file for recording options
    $config["log_push_url"]          = $config["ezcast_logs_url"] . "?action=push_logs"; // Log file for recording options
    // To access to machine log from user frontpage ------------------------------------
    $config["apiactive"]             = 1; // If 1 api activated other not activated
    $config["apiuser"]               = "user"; // Api username
    $config["apikey"]                = "password"; // Api password

    // Recorders and qualities info getter
    $recorder_modules = json_decode(file_get_contents($config["recorder"]),true);
    // Plugins list info getter
    $plugins_list     = json_decode(file_get_contents($config["listplugins"]),true);

    $enablemaintenance = false; // if true go to the template directory and modify the text in maintenance.php file.
    $randomSecurityNumber = 98856;
    include $config["lib"] . "/loader.php";

