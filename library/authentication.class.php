<?php
    /*
     * Authentication, authorization and access control
     * This is the main authentication library for the recorder
    */
    class Authentication{

        //Check if authentication file exists with correct permissions and mod
        public function checkFileSystem(){
            global $config;
            if (!file_exists($config["passwordfile"]) || !is_readable($config["passwordfile"])) {
                return $config["passwordfile"] . ': not found or not readable';
            }
            elseif(!file_exists($config["adminlist"]) || !is_readable($config["adminlist"])){
                return $config["adminlist"] . ': not found or not readable';
            }
            elseif(!file_exists($config["courselist"]) || !is_readable($config["courselist"])){
                return $config["courselist"] . ': not found or not readable';
            }
            else{
                return true;
            }
        }

        //Check user information while login
        public function checkUserInfo(string $username, string $password){
            global $config;
            global $lang;

            require "encryption.class.php";
            $enc = new Encryption();

            $loginFiles = json_decode(file_get_contents($config["loginsystem"]),true);
            foreach ($loginFiles as $login){
                foreach ($login as $type){
                    if($type["enabled"] == true){
                        $answer = require_once "authentication/" . $type["file"];
                        if($answer["success"] == 1) {
                            $createSession = $this->createSession($answer);
                            break; //If information found
                        }
                    }
                }
            }

            if($createSession == "create_session") //If there is no live session and user are able to use the system
            {
                return $answer;
            }
            elseif ($createSession["error"] == "live_session") //If there is a live session send confirmation message
            {
                $answer["success"] = 0;
                $answer["errorMsg"] = nl2br($lang["recorder_in_use"] . " " . $lang["author"] . " : " . $createSession["current_user"] . "\r\n " . $lang["course"] . " : " . $createSession["course"] . "\r\n " . $lang["date_hour"] . " : " . $createSession["start_time"] . "\r\n" . $lang["yes_stop_record"]);
                return $answer;
            }
            else{
                $answer["success"] = 0;
                $answer["errorMsg"] = $lang["no_courses_found"];
                return $answer;
            }
        }

        //Create user session
        public function createSession($session = array()){
            global $system;
            global $config;
            if($session["success"] == 1 && !empty($session["course_list"])){
                $checkLock = json_decode($system->getRecordingStatus(),true);
                $this->createUserCourseList($session["course_list"]);

                if($checkLock != false && $checkLock["user_login"] != $session["user_login"]) {
                    $checkAnswer["current_user"] = $checkLock["user_login"];
                    $checkAnswer["course"]       = $checkLock["course"];
                    $checkAnswer["start_time"]   = date("d/m/Y H:i:s", $checkLock["init_time"]);
                    $checkAnswer["error"]        = "live_session";
                    $_SESSION["forced_recorder_logged"] = md5($config["main"]->randomsecurenumber * date("dmY"));
                    $_SESSION["forced_user_login"] = $session["user_login"];
                    return $checkAnswer;
                }
                else {
                    $_SESSION["user_login"] = $session["user_login"];
                    $_SESSION["recorder_logged"] = true;
                    return "create_session";
                }
            }
            else{
                session_destroy();
                return false;
            }
        }
        //Get user session information
        public function userSession(string $parameter){
            if($parameter == "is_logged"){
                if(!empty($_SESSION["user_login"]) && $_SESSION["recorder_logged"] = true){
                    return true;
                }
                else{
                    unset($_SESSION["user_login"]);
                    unset($_SESSION["recorder_logged"]);
                    return false;
                }
            }
            elseif($parameter == "logged_user"){
                if(!empty($_SESSION["user_login"]))
                    return $_SESSION["user_login"];
                else
                    return false;
            }
            else{
                return false;
            }
        }

        //Create user course list
        public function createUserCourseList($courseList = array()){
            $_SESSION["course_list"] = $courseList;
        }

        //Get user information and user courses
        public function getUserInfo(string $parameter,$user,$info = null){
            //global $logger;
            global $config;

            if($parameter == "info"){
                $users = array();

                require_once $config["courselist"];

                return $users[$user][$info];
            }
            elseif($parameter == "courses"){
                return $_SESSION["course_list"];
            }
            else{
                return false;
            }
        }

    }
?>