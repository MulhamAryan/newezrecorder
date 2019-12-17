<?php

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
        "var"             => "var",
        "theme"           => "refracted"
    );

    $config["curenttheme"] = $config["templates"] . "/" . $config["theme"];
    $config["passwordfile"] = $config["basedir"] . "/etc/users/htpasswd";
    $config["adminlist"] = $config["basedir"] . "/etc/users/admin.inc";
    $config["courselist"] = $config["basedir"] . "etc/users/courselist.php";

    $recorder_modules = array(
        array(
            "enabled"   => true,
            "module"    => "camrecord",
            "ip"        => "192.168.100.103",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.102:554/1",
                "sd"        => "10.0.2.102:554/2"
            ),
            "icon"      => "film",
            "tempname"  => "Camera"
        ),

        array(
            "enabled"   => true,
            "module"    => "sliderecord",
            "ip"        => "192.168.100.103",
            "type"      => "rtsp",
            "quality"   => array(
                "hd"        => "10.0.2.102:554/1",
                "sd"        => "10.0.2.102:554/2"
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

    include $config["lib"] . "/ffmpeg.php";
    include $config["templates"] . "/template.class.php";
    include $config["lib"] . "/authentification.class.php";
    $auth = new authentification();
?>