<?php

    if(!$auth->userSession("is_logged"))
    {
        $_SESSION["forced_login"] = false;
        if(isset($input["userlogin"])){
            $usernetid = $input["usernetid"];
            $userpassword = $input["userpassword"];
            $login = $auth->checkUserInfo($usernetid,$userpassword);
            if($login["success"] == 1){
                $checkLock = json_decode($system->getRecordingStatus(),true);
                if($checkLock != false && $checkLock["user_login"] != $login["user_login"]) {
                    $current_user = $checkLock["user_login"];
                    $course = $checkLock["course"];
                    $start_time = date("d/m/Y H:i:s", $checkLock["init_time"]);
                    $logger->log(EventType::RECORDER_LOGIN, LogLevel::WARNING, "User " . $login["user_login"] . " tried to login but session was locked, asking him if he wants to interrupt the current record", array(basename(__FILE__)), $checkLock["asset"],$login["user_login"]);
                    $_SESSION["forced_recorder_logged"] = md5($randomSecurityNumber * date("dmY"));
                    $_SESSION["forced_user_login"] = $login["user_login"];
                }
                else {
                    $success = 1;
                    $_SESSION["user_login"] = $login["user_login"];
                    $_SESSION["recorder_logged"] = true;
                    header("LOCATION:?");
                }
            }
            else{
                $success = 0;
                $errorMsg = $login["errorMsg"];
                //$logger->log(EventType::RECORDER_LOGIN, LogLevel::INFO, "Login failed, wrong credentials for login: $login", array(__FILE__));
            }
        }

        include $tmp->loadFile("login.form.php");

    }
    else{
        header("LOCATION:index.php");
    }
?>