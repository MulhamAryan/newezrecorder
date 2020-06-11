<?php
    class System{
        function removeCharacters($string){
            $string = str_replace(' ', '-', $string);
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
            return $string;
        }

        function recStatus($array = array()){
            global $config;
            $recordingStatus = $config["var"] . "/" . $config["statusfile"];
            $recordingNow =
                array(
                    "user_login" => $array["userLogin"],
                    "asset" => $array["assetName"],
                    "course" => $array["courseName"],
                    "recording_status" => $array["recStatus"],
                    "init_time" => $array["initTime"],
                    "start_time" => "",
                    "auto_stop" => $array["autoStop"],
                    "stop_time" => $array["stopTime"],
                    "publishin" => $array["publishIn"],
                    "recorders" => $array["recorders"],
                    "streaming" => $array["streaming"]
                );
            $recordingNow = json_encode($recordingNow);

            if(!file_exists($recordingStatus)){
                file_put_contents($recordingStatus, $recordingNow . PHP_EOL, LOCK_EX);
            }

            return $recordingNow;
        }

        function getRecordingStatus($arrayName = ""){
            global $config;
            $recordingStatus = $config["var"] . "/" . $config["statusfile"];
            if(file_exists($recordingStatus)) {
                $recStatus = file_get_contents($recordingStatus);
                if(empty($arrayName)){
                    return $recStatus;
                }
                else{
                    $recStatus = json_decode($recStatus, true);
                    return $recStatus[$arrayName];
                }
            }
            else{
                return false;
            }
        }

        function getRecorderArray($recorder){
            global $recorder_modules;
            if ($recorder != "all") {
                foreach ($recorder_modules as $recorderKey => $recorderValue) {
                    if ($recorderValue["module"] == $recorder) {
                        if ($recorderValue["enabled"] == true) {
                            $newRecorder = $recorderValue;
                        }
                    }
                }
                if(!empty($newRecorder))
                    $recorderInfo[] = $newRecorder;
                else
                    $recorderInfo = "";
            } else {
                $recorderInfo = $recorder_modules;
            }
            return $recorderInfo;
        }

        function generateMetadataFile($metaInfo,$asset){
            global $config;
            $metadataFile = $config["recordermaindir"] . $config["local_processing"] . "/". $asset . "/_" . $config["metadata"];

            $xmlstr = "<?xml version='1.0' standalone='yes'?>\n<metadata>\n</metadata>\n";
            $xml = new SimpleXMLElement($xmlstr);
            foreach ($metaInfo as $key => $value) {
                $xml->addChild($key,  str_replace('&','&amp;',$value));
            }
            file_put_contents($metadataFile,$xml->asXML());
        }

        function bashCommandLine($command){
            global $config;
            exec($command, $output);
            $execArray = array(
                "time" => date("H:i:s",time()),
                "command" => $command,
                "answer" => $output
            );
            file_put_contents($config["machinelog"] . "/cmd/" . date("d-m-Y",time()) . ".json", json_encode($execArray) . PHP_EOL, FILE_APPEND | LOCK_EX);
            return $output;
        }

        function prepareMerge($publishin,$nowrecording){
            global $config;
            global $logger;

            $assetDir = $this->getRecordingAssetDir();
            $varDir = $config["var"];

            if($publishin == "trash"){
                if(file_exists($assetDir) && file_exists($varDir ."/" . $config["statusfile"])) {
                    rename($varDir . "/" . $config["statusfile"], $assetDir . "/recordinginfo.json");
                    rename($assetDir, $config["recordermaindir"] . $config["trash"] . "/" . $nowrecording["asset"]);
                    $logger->log(EventType::TEST, LogLevel::INFO, "Asset moved to " .$config["trash"], array(__FUNCTION__), $nowrecording["asset"]);
                    return true;
                }
                else{
                    return "record_not_found";
                }
            }
            elseif($publishin == "public" or $publishin == "private"){

                if($publishin == "private") {
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
                $startMerge = $config["phpcli"] . " " . $config["cli_post_process"] . " " . $nowrecording["asset"] . " " . $nowrecording["recorders"] . " startmerge > $assetDir/post_process.log 2>&1 &";
                $this->bashCommandLine($startMerge);
            }
            else{
                return "unknown_function";
            }
        }

        function isProcessRunning($pid) {
            global $config;
            if (!isset($pid) || $pid == '' || $pid == 0)
                return false;
            $output = $this->bashCommandLine($config["ps"] . " $pid");
            return count($output) >= 2;
        }

        function getPidFromFile($filePath) {
            if(!file_exists($filePath))
                return false;

            $handle = fopen($filePath, "r");
            if($handle == false)
                return false;

            $pid = fgets($handle);
            fclose($handle);
            return $pid;
        }

        function getRecordingAssetDir(){
            global $config;
            if(file_exists($config["recordermaindir"] . "/" . $config["local_processing"] . "/" . $this->getRecordingStatus("asset")))
                return $config["recordermaindir"] . "/" . $config["local_processing"] . "/" . $this->getRecordingStatus("asset");
            else
                return "no asset found";
        }

        function createJob($info = array()){
            global $config;
            $time = $info["time"];
            $time = explode(":", $time);
            if(count($time) > 1) {
                $addTime = time() + ((int)$time[0] * 60 * 60) + ((int)$time[1] * 60);
                $cronTime = date("i H d m w ",$addTime);
                $cronCmd = "MAILTO=" . $config["adminmail"] . PHP_EOL;
                $cronCmd .= "HOME=/tmp" . PHP_EOL;
                $cronCmd .= $cronTime . $config["phpcli"] . " " . $config["cli_auto_publish"] . " " . $this->getRecordingStatus("asset") . PHP_EOL;
                file_put_contents( $this->getRecordingAssetDir() . "/" . $config["crontabuserfile"],$cronCmd);
                $this->bashCommandLine($config["crontab"] . " " . $this->getRecordingAssetDir() . "/" . $config["crontabuserfile"]);
            }
        }

        function crontabReset(){
            global $config;
            $this->bashCommandLine($config["crontab"] . " -r");
        }
    }
