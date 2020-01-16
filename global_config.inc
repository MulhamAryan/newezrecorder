<?php
    @error_reporting(E_ALL | E_STRICT); // Only on debug mode !
    @ini_set('display_errors', '1');    // Only on debug mode !
    date_default_timezone_set("Europe/Brussels");
    $config_version = "2019121201";
    $input = array_merge($_GET, $_POST);
    if(isset($input['action']) && !empty($input['action'])) {
        $action = $input['action'];
    }
    else{
        $action = "";
    }

    $config = array(
        "classroom"        => "mulham",
        "apacheusername"   => "podclient", //TODO
        "recorderip"       => "164.15.43.32",
        "htdocspath"       => "newezrecorder",
        "recordermaindir"  => "/home/podclient/Videos/",
        "local_processing" => "local_processing",
        "trash"            => "trash",
        "upload_to_server" => "upload_to_server",
        "upload_ok"        => "upload_ok",
        "basedir"          => __DIR__ . "/",
        "clidir"           => "cli",
        "clipostprocess"   => "cli_post_process.php",
        "webbasedir"       => "/var/www/html/",
        "userweb"          => "_www",
        "ezmanagerurl"     => "http://podcasttest.ulb.ac.be/ezmanager/",
        "adminmail"        => "podcast.tech@ulb.ac.be",
        "phpcli"           => "/usr/bin/php",
        "ffmpegcli"        => "/usr/bin/ffmpeg",
        "crontab"          => "/usr/bin/crontab",
        "crontabcli"       => "cli_auto_publish.php",
        "downloadprotocol" => "rsync",
        "controllers"      => "controllers",
        "ajaxdir"          => "ajax",
        "lib"              => "lib",
        "templates"        => "templates",
        "languages"        => "languages",
        "services"         => "services",
        "plugins"          => "plugins",
        "htdocs"           => "htdocs",
        "player"           => "player",
        "var"              => "var",
        "theme"            => "refracted",
        "statusfile"       => "recording.json",
        "metadata"         => "metadata.xml",
        "request_dump"     => "download_request_dump.txt",
        "crontabuserfile"  => "crontabfile"
    );
    $config["languages"] = $config["basedir"] . "/" . $config["languages"];
    $config["recorderurl"] = "http://". $config["recorderip"] . "/" . $config["htdocspath"];
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

    $config["playerlink"] = $config["recorderurl"] . "/" . $config["player"];

    $recorder_modules = array(
        array(
            "enabled"   => true,
            "module"    => "camrecord", //This is the first version don't change `camrecord`
            "ip"        => "10.0.2.102",
            "type"      => "rtsp",
            "quality"   => array(
                "high"        => "10.0.2.102:554/1"
                //"low"      => "10.0.2.102:554/2" // First version please don't enable this option and don't add more than on quality (Will be available in next versions)
            ),
            "icon"      => "film",
            "tempname"  => "Camera"
        ),

        array(
            "enabled"   => true,
            "module"    => "sliderecord",  //This is the first version don't change `sliderecord`
            "ip"        => "10.0.2.102",
            "type"      => "v4l2",
            "quality"   => array(
                "hd"        => "/dev/video2:alsa -i default" // "video_device:audio_software -i audio_input"
                //"sd"        => "10.0.2.102:554/2"// First version please don't enable this option and don't add more than on quality (Will be available in next versions)
            ),
            "icon"      => "image",
            "tempname"  => "Slide"
        )

    );

    $plugins_list = array(
        "camcontrollers" => array(
            "ptz" => true,
            "crestron" => false,
            "onvif" => false,
            "visca" => false
        ),

        "vu_metter" => array(
            "ffmpeg_viewer" => false,
            "crestron" => false,
            "onvif" => false,
            "visca" => false
        ),

        "sound_backup" => false,
    );

    $debug = array(
        "debug_mode" => false,
        "send_debug_to_server" => false,
        "logs_sync" => false
    );

    $enablemaintenance = false; // if true go to the template directory and modify the text in maintenance.php file.
    $randomSecurityNumber = 98856;

    include $config["lib"] . "/loader.php";

?>
