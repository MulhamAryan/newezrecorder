<?php

    class System{
        public function __construct()
        {
            global $config;
            $this->config = $config;
        }

        public function __destruct()
        {
            unset($this->config);
        }

        public function removeCharacters($string){
            $string = str_replace(' ', '-', $string);
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
            return $string;
        }

        public function recStatus($array = array()){
            $recordingStatus = $this->config["var"] . "/" . $this->config["statusfile"];
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

        public function getRecordingStatus($arrayName = ""){

            $recordingStatus = $this->config["var"] . "/" . $this->config["statusfile"];
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

        public function getRecorderArray($recorder){
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

        public function generateMetadataFile($metaInfo,$asset){
            
            $metadataFile = $this->config["recordermaindir"] . $this->config["local_processing"] . "/". $asset . "/_" . $this->config["metadata"];

            $xmlstr = "<?xml version='1.0' standalone='yes'?>\n<metadata>\n</metadata>\n";
            $xml = new SimpleXMLElement($xmlstr);
            foreach ($metaInfo as $key => $value) {
                $xml->addChild($key,  str_replace('&','&amp;',$value));
            }
            file_put_contents($metadataFile,$xml->asXML());
        }

        public function bashCommandLine($command){
            exec($command, $output);
            $execArray = array(
                "time" => date("H:i:s",time()),
                "command" => $command,
                "answer" => $output
            );
            file_put_contents($this->config["machinelog"] . "/cmd/" . date("d-m-Y",time()) . ".json", json_encode($execArray) . PHP_EOL, FILE_APPEND | LOCK_EX);
            return $output;
        }

        public function prepareMerge($publishin,$nowrecording){
            global $logger;
            $assetDir = $this->getRecordingAssetDir();
            $varDir = $this->config["var"];

            if($publishin == "trash"){
                if(file_exists($assetDir) && file_exists($varDir ."/" . $this->config["statusfile"])) {
                    rename($varDir . "/" . $this->config["statusfile"], $assetDir . "/recordinginfo.json");
                    rename($assetDir, $this->config["recordermaindir"] . $this->config["trash"] . "/" . $nowrecording["asset"]);
                    $logger->log(EventType::TEST, LogLevel::INFO, "Asset moved to " .$this->config["trash"], array(__FUNCTION__), $nowrecording["asset"]);
                    return true;
                }
                else{
                    return "record_not_found";
                }
            }
            elseif($publishin == "public" || $publishin == "private"){

                if($publishin == "private") {
                    $moderation = "true";
                }
                else{
                    $moderation = "false";
                }

                if(file_exists($varDir . "/" . $this->config["statusfile"])) {
                    $nowrecording["publishin"] = $moderation;
                    $newRecordingStatus = json_encode($nowrecording);
                    file_put_contents($varDir . "/" . $this->config["statusfile"],$newRecordingStatus, LOCK_EX);
                    rename($varDir . "/" . $this->config["statusfile"], $assetDir . "/info." . $this->config["statusfile"]);
                }
                $startMerge = $this->config["phpcli"] . " " . $this->config["cli_post_process"] . " " . $nowrecording["asset"] . " " . $nowrecording["recorders"] . " startmerge > $assetDir/post_process.log 2>&1 &";
                $this->bashCommandLine($startMerge);
            }
            else{
                return "unknown_function";
            }
        }

        public function isProcessRunning($pid) {
            if (!isset($pid) || $pid == '' || $pid == 0)
                return false;
            $output = $this->bashCommandLine($this->config["ps"] . " $pid");
            return count($output) >= 2;
        }

        public function getPidFromFile($filePath) {
            if(!file_exists($filePath))
                return false;

            $handle = fopen($filePath, "r");
            if($handle == false)
                return false;

            $pid = fgets($handle);
            fclose($handle);
            return $pid;
        }

        public function getRecordingAssetDir(){
            if(file_exists($this->config["recordermaindir"] . "/" . $this->config["local_processing"] . "/" . $this->getRecordingStatus("asset")))
                return $this->config["recordermaindir"] . "/" . $this->config["local_processing"] . "/" . $this->getRecordingStatus("asset");
            else
                return "no asset found";
        }

        public function createJob($info = array()){
            $time = $info["time"];
            $time = explode(":", $time);
            if(count($time) > 1) {
                $addTime = time() + ((int)$time[0] * 60 * 60) + ((int)$time[1] * 60);
                $cronTime = date("i H d m w ",$addTime);
                $cronCmd = "MAILTO=" . $this->config["adminmail"] . PHP_EOL;
                $cronCmd .= "HOME=/tmp" . PHP_EOL;
                $cronCmd .= $cronTime . $this->config["phpcli"] . " " . $this->config["cli_auto_publish"] . " " . $this->getRecordingStatus("asset") . PHP_EOL;
                file_put_contents( $this->getRecordingAssetDir() . "/" . $this->config["crontabuserfile"],$cronCmd);
                $this->bashCommandLine($this->config["crontab"] . " " . $this->getRecordingAssetDir() . "/" . $this->config["crontabuserfile"]);
            }
        }

        public function crontabReset(){
            $this->bashCommandLine($this->config["crontab"] . " -r");
        }

        public function pingComponents($type){
            $type = $this->removeCharacters($type);
            $getRecorder = $this->getRecorderArray($type);
            if(!empty($getRecorder) && $getRecorder[0]["enabled"] == true){
                $ch = curl_init($this->config["ping"]);
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
        }
    }
