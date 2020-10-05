<?php
    ob_start();
    session_start();

    include "global_config.inc";

    if(empty($_SESSION["language"])){
        $_SESSION["language"] = "fr";
        include $config["main"]->languages . "/francais.php";
    }
    else{
        $selectedLang = $_SESSION["language"];
        include $config["main"]->languages . "/" . $languagesList[$selectedLang]["file"];
    }

    $input = array_merge($_GET, $_POST);

    if(isset($input['action'])) {
        $action = $input['action'];
    }

    if($auth->userSession("is_logged") == true) {
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
        if($action == "api"){
            include $config["ajax"] . "/api.php";
        }
        elseif($action == "login"){
            include $config["ajax"] . "/login.php";
        }
        else{
            header("LOCATION:index.php?ajax_not_found");
        }
        return false;
    }
