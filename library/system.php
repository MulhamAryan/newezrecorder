<?php

    class System{
        /**
         * @var RecorderLogger
         */
        public $logger;
        public $config;

        public function __construct()
        {
            global $config;
            global $logger;
            $this->config = $config;
            $this->logger = $logger;
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
            $recordingStatus = $this->config["var"] . "/" . $this->config["main"]->statusfile;
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

            $recordingStatus = $this->config["var"] . "/" . $this->config["main"]->statusfile;
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
            
            $metadataFile = $this->config["recordermaindir"] . $this->config["main"]->local_processing . "/". $asset . "/_" . $this->config["main"]->metadata;

            $xmlstr = "<?xml version='1.0' standalone='yes'?>\n<metadata>\n</metadata>\n";
            $xml = new SimpleXMLElement($xmlstr);
            foreach ($metaInfo as $key => $value) {
                $xml->addChild($key,  str_replace('&','&amp;',$value));
            }
            file_put_contents($metadataFile,$xml->asXML());
        }

        public function getMetadata(string $local = null, string $asset = null){
            if(empty($local) || empty($asset)){
                $recordInfo = json_decode($this->getRecordingStatus());
                $asset = $recordInfo->asset;
                $local = $this->config["main"]->local_processing;
            }
            $metadata = simplexml_load_file($this->config["recordermaindir"] . "/" . $local . "/" . $asset . "/_" . $this->config["main"]->metadata);
            return $metadata;
        }

        public function getMetadaFile(string $local = null, string $asset = null){
            if(empty($local) || empty($asset)){
                $recordInfo = json_decode($this->getRecordingStatus());
                $asset = $recordInfo->asset;
                $local = $this->config["main"]->local_processing;
            }
            return  $this->config["recordermaindir"] . "/" . $local . "/" . $asset . "/_" . $this->config["main"]->metadata;
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
            $assetDir = $this->getRecordingAssetDir();
            $varDir = $this->config["var"];

            if($publishin == "trash"){
                if(file_exists($assetDir) && file_exists($varDir ."/" . $this->config["main"]->statusfile)) {
                    rename($varDir . "/" . $this->config["main"]->statusfile, $assetDir . "/recordinginfo.json");
                    rename($assetDir, $this->config["recordermaindir"] . $this->config["main"]->trash . "/" . $nowrecording["asset"]);
                    $this->logger->log(EventType::TEST, LogLevel::INFO, "Asset moved to " .$this->config["main"]->trash, array(__FUNCTION__), $nowrecording["asset"]);
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

                if(file_exists($varDir . "/" . $this->config["main"]->statusfile)) {
                    $nowrecording["publishin"] = $moderation;
                    $newRecordingStatus = json_encode($nowrecording);
                    if($this->getMetadata()->moderation != $moderation){
                        $metadata = simplexml_load_file($this->getMetadaFile());
                        $metadata->moderation = $moderation;
                        $metadata->asXML($this->getMetadaFile());
                    }
                    file_put_contents($varDir . "/" . $this->config["main"]->statusfile,$newRecordingStatus, LOCK_EX);
                    rename($varDir . "/" . $this->config["main"]->statusfile, $assetDir . "/info." . $this->config["main"]->statusfile);
                }
                $startMerge = $this->config["main"]->phpcli . " " . $this->config["cli_post_process"] . " " . $nowrecording["asset"] . " " . $nowrecording["recorders"] . " startmerge > $assetDir/post_process.log 2>&1 &";
                $this->bashCommandLine($startMerge);
            }
            else{
                return "unknown_function";
            }
        }

        public function isProcessRunning($pid) {
            if (!isset($pid) || $pid == '' || $pid == 0)
                return false;
            $output = $this->bashCommandLine($this->config["main"]->ps . " $pid");
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
            if(file_exists($this->config["recordermaindir"] . "/" . $this->config["main"]->local_processing . "/" . $this->getRecordingStatus("asset")))
                return $this->config["recordermaindir"] . "/" . $this->config["main"]->local_processing . "/" . $this->getRecordingStatus("asset");
            else
                return "no asset found";
        }

        public function createJob($info = array()){
            $time = $info["time"];
            $time = explode(":", $time);
            if(count($time) > 1) {
                $addTime = time() + ((int)$time[0] * 60 * 60) + ((int)$time[1] * 60);
                $cronTime = date("i H d m w ",$addTime);
                $cronCmd = "MAILTO=" . $this->config["main"]->adminmail . PHP_EOL;
                $cronCmd .= "HOME=/tmp" . PHP_EOL;
                $cronCmd .= $cronTime . $this->config["main"]->phpcli . " " . $this->config["cli_auto_publish"] . " " . $this->getRecordingStatus("asset") . PHP_EOL;
                file_put_contents( $this->getRecordingAssetDir() . "/" . $this->config["main"]->crontabuserfile,$cronCmd);
                $this->bashCommandLine($this->config["main"]->crontab . " " . $this->getRecordingAssetDir() . "/" . $this->config["main"]->crontabuserfile);
            }
        }

        public function crontabReset(){
            $this->bashCommandLine($this->config["main"]->crontab . " -r");
        }

        public function pingComponents($type){
            $type = $this->removeCharacters($type);
            $getRecorder = $this->getRecorderArray($type);
            if(!empty($getRecorder) && $getRecorder[0]["enabled"] == true){
                $ch = curl_init($this->config["main"]->ping);
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

        public function requestUpload($server_url, $recorder_array){
            global $logger;

            $ch = curl_init($server_url);
            curl_setopt($ch, CURLOPT_POST, 1); //activate POST parameters
            curl_setopt($ch, CURLOPT_POSTFIELDS, $recorder_array);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //don't send answer to stdout but in returned string
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  FALSE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds

            $res = curl_exec($ch);
            $curlinfo = curl_getinfo($ch);
            curl_close($ch);
            file_put_contents($this->config["var"] ."/curl.log", var_export($curlinfo, true) . PHP_EOL . $res, FILE_APPEND);
            if ($res === false) {//error
                $http_code = isset($curlinfo['http_code']) ? $curlinfo['http_code'] : false;
                $logger->log(EventType::RECORDER_REQUEST_TO_MANAGER, LogLevel::ERROR, "Curl failed to POST data to $server_url. Http code: $http_code", array(__FUNCTION__));

                return "Curl error. Http code: $http_code";
            }

            $logger->log(EventType::RECORDER_REQUEST_TO_MANAGER, LogLevel::DEBUG, "server_request_send $server_url, result= $res", array(__FUNCTION__));

            //All went well send http response in stderr to be logged
            //fputs(STDERR, "curl result: $res", 2000);

            return $res;
        }

        public function initStreaming()
        {
            $activeRecordersFile = $this->getRecordingAssetDir() . "/" . $this->config["main"]->statusfile;
            $activeRecorders     = json_decode(file_get_contents($activeRecordersFile), true);

            foreach ($activeRecorders as $recorderKey => $recorderValue){
                $streamingInfo = array(
                    "action"         => "streaming_init",
                    "ip"             => $this->config["main"]->recorderip,
                    "protocol"       => $this->config["main"]->streamprotocol,
                    "module_quality" => $this->config["main"]->streamquality,
                    "classroom"      => $this->config["classroom"],
                    "module_type"    => $this->patchRecordName($recorderKey),
                    "course"         => (string) $this->getMetadata()->course_name,
                    "asset"          => (string) $this->getMetadata()->record_date,
                    "record_type"    => (string) $this->getMetadata()->record_type,
                    "netid"          => (string) $this->getMetadata()->netid,
                    "author"         => (string) $this->getMetadata()->author,
                    "title"          => (string) $this->getMetadata()->title
                );
                $requestStream = $this->requestUpload($this->config["ezcast_submit_url"],$streamingInfo);
                if (strpos($requestStream, 'Curl error') !== false) {
                    return false;
                }
                else {
                    foreach ($recorderValue as $quality) {
                        $streamPid = "{$this->getRecordingAssetDir()}/{$recorderKey}/{$quality}.{$this->config["main"]->streamPid}";
                        $streamLog = "{$this->getRecordingAssetDir()}/{$recorderKey}/{$quality}.{$this->config["main"]->streamLog}";
                        $cmd = "{$this->config["main"]->phpcli} {$this->config["cli_stream_send"]} {$recorderKey} {$quality} > {$streamLog} 2>&1 < /dev/null & echo $! > {$streamPid}";
                        $this->bashCommandLine($cmd);
                        sleep(0.5);
                    }
                    sleep(1);
                }
            }
            return true;
        }

        public function patchRecordName(string $recorder){

            if($recorder == "camrecord")
                return "cam";

            elseif($recorder == "sliderecord")
                return "slide";

            else
                return "cam";

        }
    }
