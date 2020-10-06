<?php

    $username = $enc->localDecrypt($username);
    $password = $enc->localDecrypt($password);

    $admin  = array();
    $users  = array();
    $course = array();

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
                } // In this case, we retrieve their information from pwfile, and return them.
                else {
                    require_once $config["courselist"];
                    $ret = $users[$user_login]; // We return all the information from courselist.php ...
                    $ret['user_login'] = $user_login;
                    $ret['real_login'] = $real_login; // ... To which we add the real_login and user_login
                    $ret['full_name'] = $users[$user_login]['full_name'];
                    $ret['email'] = $users[$user_login]['email'];
                    $ret["success"] = 1;

                    //todo if no course found !
                    $ret["course_list"] = $course[$user_login];

                }
                return $ret;
            }
        }

        // If we arrive here, that means there is no user with the login provided
        $ret["success"] = 0;
        $ret["errorMsg"] = $lang["no_login_found"];
        return $ret;
    }
