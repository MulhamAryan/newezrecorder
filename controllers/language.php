<?php
    $select = $system->removeCharacters($input["select"]);

    if(is_array($languagesList[$select]) && $languagesList[$select]["enabled"] == true){
        $_SESSION["language"] = $select;
    }
    else{
        $_SESSION["language"] = "fr";
    }
    header("LOCATION:index.php");