<?php
    $auth = new Authentication();

    if($auth->userSession("is_logged")){
        include $config["main"]->controllers . "/recorder.php";
    }
    else{
        header("LOCATION:?action=login");
    }
?>