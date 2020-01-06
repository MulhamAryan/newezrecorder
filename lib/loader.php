<?php
    include "system.php";
    $system = new System();

    include "databases.php";
    $database = new SQLiteDatabase();

    include "recording_session.php";
    $session = new RecordingSession();

    include "logger/logger_recorder.php";
    $logger = new RecorderLogger();

    include "ffmpeg.php";

    include $config["basedir"] . $config["templates"] . "/template.class.php";

    include "authentification.class.php";

    include $config["basedir"] . $config["plugins"] . "/plugin_loader.php";

    $auth = new authentification();
?>