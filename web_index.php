<?php
    ob_start();
    session_start();

    include "global_config.inc";

    if(isset($input['action']) && !empty($input['action'])) {
        $action = $input['action'];
    }
    else{
        $action = "";
    }

    include $tmp->loadFile("header.php");

    if($enablemaintenance == false) {
        if ($auth->userSession("is_logged") == true) {
            switch ($action) {
                case $action:
                    if (file_exists($config["basedir"] . $config["controllers"] . "/" . $action . ".php"))
                        include $config["basedir"] . $config["controllers"] . "/" . $action . ".php";
                    else
                        include $config["basedir"] . $config["controllers"] . "/index.php";
                    break;

                default:
                    include $config["basedir"] . $config["controllers"] . "/index.php";
                    break;

            }
        } else {
            if($action == "recording_force_quit"){
                include $config["basedir"] . $config["controllers"] . "/recording_force_quit.php";
            }
            else{
                include $config["basedir"] . $config["controllers"] . "/login.php";
            }
        }
    }
    else{
        include $tmp->loadFile("maintenance.php");
    }

    include $tmp->loadFile("footer.php");
?>