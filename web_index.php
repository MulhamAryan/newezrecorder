<?php
    ob_start();
    session_start();

    include "global_config.inc";

    $input = array_merge($_GET, $_POST);

    if(isset($input['action'])) {
        $action = $input['action'];
    }

   include $tmp->loadFile("header.php");

    if($auth->userIsLoged() == true) {
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
    }
    else{
        include $config["basedir"] . $config["controllers"] . "/login.php";
    }
    include $tmp->loadFile("footer.php");
?>