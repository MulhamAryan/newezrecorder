<?php
    $auth = new authentification();

    if($auth->userIsLoged()){
        include $config["basedir"] . $config["controllers"] . "/recorder.php";
    }
    else{
        header("LOCATION:?action=login");
    }
?>