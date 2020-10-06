<?php

    if(!$auth->userSession("is_logged"))
    {
        $_SESSION["forced_login"] = false;
        $public_key = $config["basedir"] . "/etc/keys/ezrecorder_pub.pem";
        if(file_exists($public_key)){
            $public_key_content = file_get_contents($config["basedir"] . "/etc/keys/ezrecorder_pub.pem");
            $public_key_content = preg_replace("/[\n\r]/","",$public_key_content);
        }
        else{
            $public_key_content = "";
        }
        if(isset($input["userlogin"])){
            $usernetid = $input["usernetid"];
            $userpassword = $input["userpassword"];

            $login = $auth->checkUserInfo($usernetid,$userpassword);
        }

        include $tmp->loadTempFile("login.form.php");

    }
    else{
        header("LOCATION:index.php");
    }
?>