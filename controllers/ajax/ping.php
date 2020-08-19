<?php
    $type = $system->removeCharacters($input["type"]);
    $getRecorder = $system->getRecorderArray($type);
    if(!empty($getRecorder) && $getRecorder[0]["enabled"] == true){
        $ch = curl_init($config["main"]->ping);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($retcode == 200) {
            return true;
        } else {
            return false;
        }
    }
    else{
        return false;
    }