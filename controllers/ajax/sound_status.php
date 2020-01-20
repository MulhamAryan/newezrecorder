<?php
    if($auth->userSession("is_logged") == true){
        $recorder = $system->removeCharacters($input["recorder"]);
        $quality = $system->removeCharacters($input["quality"]);
        if(empty($recorder) || $quality != "high")
            return false;
        else{
            $info = $recorder . "/" . $quality;
            echo $SoundMetter->soundMetter($info);
        }
    }
    else{
        return "need_login";
    }
?>