<?php

    $languagesList = array(
        "fr" => array(
            "enabled" => true,
            "file" => "francais.php",
            "name" => "Français"
        ),
        "en" => array(
            "enabled" => true,
            "file" => "english.php",
            "name" => "English"
        ),
        "nl" => array(
            "enabled" => true,
            "file" => "dutch.php",
            "name" => "Dutch"
        ),
    );

    if(empty($_SESSION["language"])){
        $_SESSION["language"] = "fr";
        include $config["languages"] . "/francais.php";
    }
    else{
        $selectedLang = $_SESSION["language"];
        include $config["languages"] . "/" . $languagesList[$selectedLang]["file"];
    }
?>