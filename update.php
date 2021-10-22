<?php
    /* ----- Login System
        1- Create login.json file in /etc/config - OK
        2- Create folder keys in /etc - OK
        3- Create public recorder key and upload the private to ezcast server - OK
        4- Create public ezcast key and put the private key in all recorders - OK
        5- Update javascript interface library - OK
        6- Move cam presets to /var/www/recorder/var - OK
    */
    /*
     * Copy presets before push TODO WARING !
     * */
    include "global_config.inc";

    function checkFileExists($file,$type){
        if($type == "file"){
            if(file_exists($file)){
                return "OK";
            }
            else{
                return "File not created !";
            }
        }
        elseif ($type == "dir"){
            if(is_dir($file)){
                return "OK";
            }
            else{
                return "Dir not created !";
            }
        }
    }
    echo "Creating login.json file" . PHP_EOL;
    copy($config["basedir"] . "/etc/config/login.example.json",$config["basedir"] . "/etc/config/login.json");
    echo checkFileExists($config["basedir"] . "/etc/config/login.json","file");

    echo "Copying templates folder" . PHP_EOL;
    exec("/usr/bin/cp -R " . $config["basedir"] . "/htdocs/templates/refracted/js" . $config["main"]->webbasedir . "/ezrecorder/templates/refracted");
    echo checkFileExists($config["main"]->webbasedir . "/ezrecorder/templates/refracted","dir");
