<?php
    /*$pubishin = $system->removeCharacters($input["pubishin"]);
    $nowrecording = json_decode($system->getRecordingStatus(),true);
    $posibilites = array("trash","private","public");

    if($nowrecording["recording_status"] == "stop" && $auth->getLoggedUser() == $nowrecording["user_login"] && in_array($pubishin,$posibilites) == true) {
        $varDir = $config["basedir"] . $config["var"];
        $assetDir = $config["recordermaindir"] . $config["local_processing"] . "/" . $nowrecording["asset"];

        if($pubishin == "trash"){
            if(file_exists($assetDir) && file_exists($varDir ."/" . $config["statusfile"])) {
                rename($varDir . "/" . $config["statusfile"], $assetDir . "/recordinginfo.json");
                rename($assetDir, $config["recordermaindir"] . $config["trash"] . "/" . $nowrecording["asset"]);
                return true;
            }
            else{
                return "record_not_found";
            }
        }
        elseif($pubishin == "public" or $pubishin == "private"){

            if($pubishin == "private") {
                $moderation = "true";
            }
            else{
                $moderation = "false";
            }

            if(file_exists($varDir . "/" . $config["statusfile"])) {
                $nowrecording["publishin"] = $moderation;
                $newRecordingStatus = json_encode($nowrecording);
                file_put_contents($varDir . "/" . $config["statusfile"],$newRecordingStatus, LOCK_EX);
                rename($varDir . "/" . $config["statusfile"], $assetDir . "/info." . $config["statusfile"]);
            }
            $startMerge = $config["phpcli"] . " " . $config["basedir"]  . $config["clidir"] . "/" . $config["clipostprocess"] . " " . $nowrecording["asset"] . " " . $nowrecording["recorders"] . " startmerge > $assetDir/post_process.log 2>&1 &";
            $system->bashCommandLine($startMerge);
        }
        else{
            return "unknown_function_found";
        }
    }
    else{
        return "can_t_publish_recording_" . $nowrecording;
    }*/
?>

