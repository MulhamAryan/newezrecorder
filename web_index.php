<?php
    ob_start("ob_gzhandler");
    session_start();

    include "global_config.inc";
    $logger = new RecorderLogger();

    include $tmp->loadTempFile("header.php");

    if($config["main"]->maintenance == false) {
        if ($auth->userSession("is_logged") == true) {
            switch ($action) {
                case $action:
                    if (file_exists($config["main"]->controllers . "/" . $action . ".php"))
                        include $config["main"]->controllers . "/" . $action . ".php";
                    else
                        include $config["main"]->controllers . "/index.php";
                    break;

                default:
                    include $config["main"]->controllers . "/index.php";
                    break;
            }
        } else {
            if($action == "recording_force_quit"){
                include $config["main"]->controllers . "/recording_force_quit.php";
            }
            else{
                include $config["main"]->controllers . "/login.php";
            }
        }
    }
    else{
        include $tmp->loadTempFile("maintenance.php");
    }

    include $tmp->loadTempFile("footer.php");
?>