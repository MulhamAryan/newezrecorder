<?php

    if(!$auth->userIsLoged())
    {
        if(isset($input["userlogin"])){
            $usernetid = $input["usernetid"];
            $userpassword = $input["userpassword"];
            $login = $auth->checkUserInfo($usernetid,$userpassword);
            if($login["success"] == 1){
                $success = 1;
                $_SESSION["user_login"] = $login["user_login"];
                $_SESSION["recorder_logged"] = true;
                header("LOCATION:?");
            }
            else{
                $success = 0;
                $errorMsg = $login["errorMsg"];
            }
        }

        include $config["basedir"] . "/" . $config["templates"] . "/login.form.php";

    }
    else{
        header("LOCATION:index.php");
    }
?>