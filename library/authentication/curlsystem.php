<?php
    $cryptedUsername = $enc->encrypt($username);
    $cryptedPassword = $enc->encrypt($password);

    $postinfo = array("username" => $cryptedUsername, "userpass" => $cryptedPassword);
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "ezrecorder", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init($config["ezcast_login_url"]);
    curl_setopt_array( $ch, $options );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );
    $content = @unserialize($content);
    if(!empty($content["login"])){
        $ret["success"] = 1;
        $ret['user_login']  = $content["login"];
        $ret['real_login']  = $content["real_login"];
        $ret['full_name']   = $content["full_name"];
        $ret['email']       = $content["email"];
        $ret["course_list"] = $content["course_list"];
        unset($ret["errorMsg"]);
    }
    else{
        $ret["success"] = 0;
        $ret["errorMsg"] = $lang["no_login_found"];
    }
    return $ret;