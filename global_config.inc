<?php
    // Dev: Change this value everytime you add an option to this file, as well as the value in common.inc. When version in config and common.inc don't match, a warning is triggered.

    $config_version = "2019121201";

    $config = array(
        "classroom"       => "firstnewgeneration",
        "recorderip"      => "127.0.0.1",
        "recordermaindir" => "/Users/mulham/Movies/",
        "basedir"         => __DIR__ . "/",
        "webbasedir"      => "/Library/WebServer/Documents/ezrecorder/",
        "userweb"         => "_www",
        "ezmanagerurl"    => "https://podcast.ulb.ac.be/ezmanager/",
        "adminmail"       => "podcast.tech@ulb.ac.be",
        "phpcli"          => "/usr/bin/php",
        "ffmpegcli"       => "/usr/local/bin/ffmpeg",
        "controllers"     => "controllers",
        "lib"             => "lib",
        "templates"       => "templates",
        "languages"       => "languages",
        "services"        => "services",
        "plugins"         => "plugins",
        "htdocs"          => "htdocs",
        "var"             => "var"
    );

    $recorder_modules = array(
        array(
            "enabled"   => true,
            "module"    => "camrecord",
            "ip"        => "192.168.100.103",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.102:554/1",
                "sd"        => "10.0.2.102:554/2"
            )
        ),
        array(
            "enabled"   => true,
            "module"    => "sliderecord",
            "ip"        => "192.168.100.103",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.102:554/1",
                "sd"        => "10.0.2.102:554/2"
            )
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

    include $config["lib"] . "/ffmpeg.php";
?>