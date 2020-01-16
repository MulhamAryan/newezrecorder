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

    if(!empty($input["language"])){
        $select = $input["language"];

        if(is_array($languagesList[$select]) && $languagesList[$select]["enabled"] == true){
            $_SESSION["language"] = $select;
        }
        else{
            $_SESSION["language"] = "fr";
        }
        header("LOCATION:index.php");
    }

    if(empty($_SESSION["language"])){
        $_SESSION["language"] = "fr";
        include $config["languages"] . "/francais.php";
    }
    else{
        $selectedLang = $_SESSION["language"];
        include $config["languages"] . "/" . $languagesList[$selectedLang]["file"];
    }
?>