<?php

    if($config["main"]->debug_mode) {
        error_reporting(E_ALL | E_STRICT); // Only on debug mode !
        ini_set('display_errors', '1'); // Only on debug mode !
    }

    include "languages.php";

    include "system.php";
    $system = new System();

    include "databases.php";
    $database = new SQLiteDatabase();

    include "recording_session.php";
    $session = new RecordingSession();

    include "logger/logger_recorder.php";
    include "ffmpeg.php";
    include $config["basedir"] . $config["main"]->templates . "/template.class.php";
    include "authentication.class.php";
    include $config["basedir"] . $config["main"]->plugins . "/plugin_loader.php";

    $auth = new Authentication();
?>