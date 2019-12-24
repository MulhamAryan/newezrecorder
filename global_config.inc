<?php

    $config_version = "2019121201";

    $config = array(
        "classroom"       => "mulham",
        "apacheusername"  => "_www",
        "recorderip"      => "127.0.0.1",
        "recorderurl"     => "http://localhost/newezrecorder",
        "recordermaindir" => "/Users/mulham/Movies/",
        "local_processing"=> "local_processing",
        "trash"           => "trash",
        "upload_to_server"=> "upload_to_server",
        "upload_ok"       => "upload_ok",
        "basedir"         => __DIR__ . "/",
        "clidir"          => "cli",
        "clipostprocess"  => "cli_post_process.php",
        "webbasedir"      => "/Library/WebServer/Documents/ezrecorder/",
        "userweb"         => "_www",
        "ezmanagerurl"    => "http://podcasttest.ulb.ac.be/ezmanager/",
        "adminmail"       => "podcast.tech@ulb.ac.be",
        "phpcli"          => "/usr/bin/php",
        "ffmpegcli"       => "/usr/local/bin/ffmpeg",
        "downloadprotocol"=> "rsync",
        "controllers"     => "controllers",
        "ajaxdir"         => "ajax",
        "lib"             => "lib",
        "templates"       => "templates",
        "languages"       => "languages",
        "services"        => "services",
        "plugins"         => "plugins",
        "htdocs"          => "htdocs",
        "var"             => "var",
        "theme"           => "refracted",
        "statusfile"      => "recording.json",
        "metadata"        => "metadata.xml"
    );

    $config["curenttheme"] = $config["templates"] . "/" . $config["theme"];
    $config["passwordfile"] = $config["basedir"] . "/etc/users/htpasswd";
    $config["adminlist"] = $config["basedir"] . "/etc/users/admin.inc";
    $config["courselist"] = $config["basedir"] . "etc/users/courselist.php";
    $config["ajax"] = $config["basedir"] . "/" . $config["controllers"] . "/" . $config["ajaxdir"];

    $config["submit_to_server"] = $config["ezmanagerurl"] . "recorder/index.php";

    $config["database_file"] = $config["basedir"] . "/" . $config["var"] . "/db.sqlite";
    $config["ezcast_logs_url"] = $config["ezmanagerurl"] . "recorder/logs.php";
    $config["last_log_sent_get_url"] = $config["ezcast_logs_url"] . "?action=last_log_sent&source=".$config["classroom"];
    $config["log_push_url"] = $config["ezcast_logs_url"] . "?action=push_logs";
    $config["ezcast_submit_url"] = $config["ezmanagerurl"] . "recorder/index.php";

    $recorder_modules = array(
        array(
            "enabled"   => true,
            "module"    => "camrecord", //This is the first version don't change `camrecord`
            "ip"        => "192.168.100.103",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.102:554/1"
                //"sd"        => "10.0.2.102:554/2" // First version please don't enable this option and don't add more than on quality (Will be available in next versions)
            ),
            "icon"      => "film",
            "tempname"  => "Camera"
        ),

        array(
            "enabled"   => true,
            "module"    => "sliderecord",  //This is the first version don't change `sliderecord`
            "ip"        => "192.168.100.103",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.102:554/1"
                //"sd"        => "10.0.2.102:554/2"// First version please don't enable this option and don't add more than on quality (Will be available in next versions)
            ),
            "icon"      => "image",
            "tempname"  => "Slide"
        )

    );

    $plugins = array(
        "vu_metter" => true,
        "sound_backup" => true
    );

    $debug = array(
        "debug_mode" => false,
        "send_debug_to_server" => false,
        "logs_sync" => false
    );

    $languagesList = array(
        "fr" => array(
            "enabled" => true,
            "file" => "francais.php",
            "name" => "Français"
        ),
        "en" => array(
            "enabled" => true,
            "file" => "english.php",
            "name" => "English"
        ),
        "nl" => array(
            "enabled" => true,
            "file" => "dutch.php",
            "name" => "Dutch"
        ),
    );
    include $config["lib"] . "/system.php";
    $system = new System();

    include $config["lib"] . "/databases.php";
    $database = new SQLiteDatabase($config["database_file"]);

    include $config["lib"] . "/logger/logger_recorder.php";

    include $config["lib"] . "/ffmpeg.php";
    include $config["templates"] . "/template.class.php";
    include $config["lib"] . "/authentification.class.php";
    $auth = new authentification();
?>