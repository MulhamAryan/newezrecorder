<?php
    ob_start();
    session_start();

    include "global_config.inc";

    if(empty($_SESSION["language"])){
        $_SESSION["language"] = "fr";
        include $config["languages"] . "/francais.php";
    }
    else{
        $selectedLang = $_SESSION["language"];
        include $config["languages"] . "/" . $languagesList[$selectedLang]["file"];
    }

    $input = array_merge($_GET, $_POST);

    if(isset($input['action'])) {
        $action = $input['action'];
    }

    if($auth->userIsLoged() == true) {
        switch ($action) {
            case $action:
                if (file_exists($config["ajax"] . "/" . $action . ".php"))
                    include $config["ajax"] . "/" . $action . ".php";
                else
                    return false;
                break;

            default:
                return false;
                break;

        }
    }
    else{
        return false;
    }
?>