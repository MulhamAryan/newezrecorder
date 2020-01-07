<?php
    @error_reporting(E_ALL | E_STRICT); // Only on debug mode !
    @ini_set('display_errors', '1');    // Only on debug mode !
    date_default_timezone_set("Europe/Brussels");
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
        "crontab"         => "/usr/bin/crontab",
        "crontabcli"      => "cli_auto_publish.php",
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
        "metadata"        => "metadata.xml",
        "crontabuserfile" => "crontabfile"
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
            "ip"        => "10.0.2.101",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.101:554/1"
                //"sd"      => "10.0.2.102:554/2" // First version please don't enable this option and don't add more than on quality (Will be available in next versions)
            ),
            "icon"      => "film",
            "tempname"  => "Camera"
        ),

        array(
            "enabled"   => true,
            "module"    => "sliderecord",  //This is the first version don't change `sliderecord`
            "ip"        => "10.0.2.101",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.101:554/1"
                //"sd"        => "10.0.2.102:554/2"// First version please don't enable this option and don't add more than on quality (Will be available in next versions)
            ),
            "icon"      => "image",
            "tempname"  => "Slide"
        )

    );

    $plugins_list = [
        "camcontrollers" => [
            "ptz" => true,
            "crestron" => false,
            "onvif" => false,
            "visca" => false
        ],
        "vu_metter" => [
            "ffmpeg_viewer" => false,
            "crestron" => false,
            "onvif" => false,
            "visca" => false
        ],
        "sound_backup" => false
    ];

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

    if(empty($_SESSION["language"])){
        $_SESSION["language"] = "fr";
        include $config["languages"] . "/francais.php";
    }
    else{
        $selectedLang = $_SESSION["language"];
        include $config["languages"] . "/" . $languagesList[$selectedLang]["file"];
    }

    $enablemaintenance = false; // if true go to the template directory and modify the text in maintenance.php file.

    include $config["lib"] . "/loader.php";
?>