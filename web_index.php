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

    include $config["templates"] . "/header.php";

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
    include $config["basedir"] . $config["templates"] . "/footer.php";

?>