<?php
    ob_start();
    session_start();

    include "global_config.inc";
    $logger = new RecorderLogger();

    include $tmp->loadFile("header.php");

    if($enablemaintenance == false) {
        if ($auth->userSession("is_logged") == true) {
            switch ($action) {
                case $action:
                    if (file_exists($config["controllers"] . "/" . $action . ".php"))
                        include $config["controllers"] . "/" . $action . ".php";
                    else
                        include $config["controllers"] . "/index.php";
                    break;

                default:
                    include $config["controllers"] . "/index.php";
                    break;

            }
        } else {
            if($action == "recording_force_quit"){
                include $config["controllers"] . "/recording_force_quit.php";
            }
            else{
                include $config["controllers"] . "/login.php";
            }
        }
    }
    else{
        include $tmp->loadFile("maintenance.php");
    }

    include $tmp->loadFile("footer.php");
?>