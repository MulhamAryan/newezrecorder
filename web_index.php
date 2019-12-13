<?php
    include "global_config.inc";

    $input = array_merge($_GET, $_POST);

    if(isset($input['action'])) {
        $action = $input['action'];
    }

    switch ($action) {

        case $action:
            if(file_exists($config["controllers"] . "/" . $action . ".php"))
                include $config["controllers"] . "/" . $action . ".php";
            else
                include $config["controllers"] . "/index.php";
            break;

        default:
            include $config["controllers"] . "/index.php";
    }


?>