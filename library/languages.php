<?php
    $languagesList = json_decode(file_get_contents($config["languagesls"]),true);

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