<?php
    class authentification{

        function checkFileSystem(){
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

        function checkUserInfo($username, $password)
        {
            global $config;
            global $lang;

            $admin = array();
            $users = array();

            // We could get a login of type "real_login/user_login"
            // In that case, we split it into two different variables
            if (strpos($username, '/') !== false) {
                list($real_login, $user_login) = explode('/', $username);
            } else {
                $real_login = $username;
                $user_login = $username;
            }

            // If there was a real_login and a user_login, that means somone is trying to take another's identity.
            // The only persons allowed to do that are admins so we check if $real_login is in admin list
            require_once $config["adminlist"];
            if (($real_login != $user_login) && (!isset($admin[$real_login]) || !$admin[$real_login])) {
                $ret["success"] = 0;
                $ret["errorMsg"] = $real_login . " " . $lang["not_in_admin_list"];
                return $ret;
            }
            else {
                // 1) First we check that the user is in the .htpasswd file and has the right login/password combination
                // For that, we read the entire .htpasswd
                $htpasswd = file($config["passwordfile"]);
                foreach ($htpasswd as $line) {
                    // The information is stored as login:password in .htaccess, so it's easy
                    // to retrieve using an explode
                    list($flogin, $fpasswd) = explode(':', $line);
                    if ($flogin == $real_login) {
                        // We crypt the user-provided password to check if it's equal to the one
                        // stored in the htpasswd
                        $salt = substr($fpasswd, 0, 2);
                        $cpasswd = crypt($password, $salt);
                        $fpasswd = rtrim($fpasswd);

                        // If not, that means the user has entered a wrong password. We don't log them in.
                        if ($cpasswd != $fpasswd) {
                            $ret["success"] = 0;
                            $ret["errorMsg"] = $lang["wrong_password"];
                            return $ret;
                        } // In this case, we retrieve their information from pwfile, and return them.
                        else {
                            require_once $config["courselist"];
                            $ret = $users[$user_login]; // We return all the information from courselist.php ...
                            $ret['user_login'] = $user_login;
                            $ret['real_login'] = $real_login; // ... To which we add the real_login and user_login
                            $ret['full_name'] = $users[$user_login]['full_name'];
                            $ret['email'] = $users[$user_login]['email'];
                            $ret["success"] = 1;
                            return $ret;
                        }
                    }
                }

                // If we arrive here, that means there is no user with the login provided
                $ret["success"] = 0;
                $ret["errorMsg"] = $lang["no_login_found"];
                return $ret;
            }
        }

        function userIsLoged(){
            if(!empty($_SESSION["user_login"]) && $_SESSION["recorder_logged"] = true){
                return true;
            }
            else{
                unset($_SESSION["user_login"]);
                unset($_SESSION["recorder_logged"]);
                return false;
            }
        }

        function getLoggedUser(){
            if(!empty($_SESSION["user_login"]))
                return $_SESSION["user_login"];
            else
                return false;
        }

        function getUserCourses($user = ""){
            global $logger;
            global $config;

            include $config["courselist"];
            if(empty($user))
                $user = $this->getLoggedUser();

            if(!isset($course)) {
                //$logger->log(EventType::RECORDER_LOGIN, LogLevel::WARNING, "Could not get any course from file $config["courselist"]. Did the server pushed the course list?", array('auth_file_user_courselist_get'));
                return array();
            }

            if(isset($course[$user]))
                return $course[$user];

            return array();
        }

        function getUserInfo($netid,$info){
            global $config;
            $users = array();

            require_once $config["courselist"];

            return $users[$netid][$info];
        }
    }
?>