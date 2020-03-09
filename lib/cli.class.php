<?php
    class cli extends ffmpeg{
        /**
         * @var string
         */
        private $assetName;
        private $recorders;
        private $recordingInfo;

        function __construct($assetName,$recorders){
            global $config;
            $this->recorders = $recorders;
            $this->assetName = $assetName;
            if(file_exists($config["recordermaindir"] . $config["upload_to_server"] . "/" . $assetName . "/" . $config["statusfile"])){
                $this->recordingInfo = file_get_contents($config["recordermaindir"] . $config["upload_to_server"] . "/" . $assetName . "/" . $config["statusfile"]);
                $this->recordingInfo = json_decode($this->recordingInfo, true);
            }
            else{
                $this->recordingInfo = file_get_contents($config["recordermaindir"] . $config["local_processing"] . "/" . $assetName . "/" . $config["statusfile"]);
                $this->recordingInfo = json_decode($this->recordingInfo, true);
            }
            $recorderArray = $this->getRecorderArray($this->recorders);
            ffmpeg::__construct($recorderArray,$this->assetName);
        }

        function startMerge(){
            global $config;
            $assetDir = $config["recordermaindir"] . $config["local_processing"] . "/" . $this->assetName;
            ffmpeg::generateConcatFile();
            sleep(3);
            file_put_contents($assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Starting merge -> $this->recorders " . PHP_EOL, FILE_APPEND | LOCK_EX);
            ffmpeg::mergeAllRecord();
            file_put_contents($assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : End of merge -> $this->recorders " . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        function startUploadToServer(){
            global $config;
            $assetDir = $config["recordermaindir"] . $config["local_processing"] . "/" . $this->assetName;
            if(!file_exists($assetDir)){
                $assetDir = $config["recordermaindir"] . $config["upload_to_server"] . "/" . $this->assetName;
            }
            else{
                rename($assetDir, $config["recordermaindir"] . $config["upload_to_server"] . "/" . $this->assetName);
                $assetDir = $config["recordermaindir"] . $config["upload_to_server"] . "/" . $this->assetName;
                file_put_contents($assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : $this->assetName asset moved successefully to " . $config["upload_to_server"] . PHP_EOL, FILE_APPEND | LOCK_EX);
            }

            file_put_contents($assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Starting upload_to_server ." . PHP_EOL, FILE_APPEND | LOCK_EX);

            if(file_exists("$assetDir/download_request_dump.txt"))
                unlink("$assetDir/download_request_dump.txt");
            // This is a temporary patch until we develop the new concept of recording on EZRendrer, EZAdmin and EZManager
            if($this->recorders == "all"){
                $record_type = "camslide";
                $camEnabled = true;
                $slideEnabled = true;
            }
            elseif($this->recorders == "camrecord"){
                $record_type = "cam";
                $camEnabled = true;
                $slideEnabled = false;
            }
            elseif($this->recorders == "sliderecord"){
                $record_type = "slide";
                $camEnabled = false;
                $slideEnabled = true;
            }
            else{
                $record_type = "camslide";
                $camEnabled = true;
                $slideEnabled = true;
            }
            // END OF THE PATCH

            $record_date = date("Y_m_d_h\hi",$this->recordingInfo["init_time"]);

            $downloadRequestArray = array(
                "action" => "download",
                "record_type" => $record_type,
                "record_date" => $record_date,
                "course_name" => $this->recordingInfo["course"],
                "php_cli" => $config["phpcli"],
                "metadata_file" => $assetDir ."/" . $config["metadata"]
            );
            if($camEnabled == true){
                $cam_info = array(
                    "ip" => $config["recorderip"],
                    "protocol" => $config["downloadprotocol"],
                    "username" => $config["apacheusername"],
                    "filename" => $assetDir . "/cam" . ffmpeg::getRecordingExtension()
                );
                $downloadRequestArray["cam_info"] = serialize($cam_info);
            }

            if($slideEnabled == true){
                $slide_info = array(
                    "ip" => $config["recorderip"],
                    "protocol" => $config["downloadprotocol"],
                    "username" => $config["apacheusername"],
                    "filename" => $assetDir . "/slide" . ffmpeg::getRecordingExtension()
                );
                $downloadRequestArray["slide_info"] = serialize($slide_info);
            }
            $downloadRequestArray["recorder_version"] = "2.0";
            file_put_contents("$assetDir/" . $config["request_dump"], var_export($downloadRequestArray, true) . PHP_EOL, FILE_APPEND);
            $curl_success = strpos($this->requestUpload($config["ezcast_submit_url"], $downloadRequestArray), 'Curl error') === false;
            if(!$curl_success)
                return "error";

            return 0;
        }

        function requestUpload($server_url, $recorder_array){
            global $logger;
            global $config;
            $ch = curl_init($server_url);
            curl_setopt($ch, CURLOPT_POST, 1); //activate POST parameters
            curl_setopt($ch, CURLOPT_POSTFIELDS, $recorder_array);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //don't send answer to stdout but in returned string
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            $res = curl_exec($ch);
            $curlinfo = curl_getinfo($ch);
            curl_close($ch);
            file_put_contents($config["var"] ."/curl.log", var_export($curlinfo, true) . PHP_EOL . $res, FILE_APPEND);
            if ($res === false) {//error
                $http_code = isset($curlinfo['http_code']) ? $curlinfo['http_code'] : false;
                $logger->log(EventType::RECORDER_REQUEST_TO_MANAGER, LogLevel::ERROR, "Curl failed to POST data to $server_url. Http code: $http_code", array(__FUNCTION__));

                return "Curl error. Http code: $http_code";
            }

            $logger->log(EventType::RECORDER_REQUEST_TO_MANAGER, LogLevel::DEBUG, "server_request_send $server_url, result= $res", array(__FUNCTION__));

            //All went well send http response in stderr to be logged
            fputs(STDERR, "curl result: $res", 2000);

            return $res;
        }

        function finishUploadToServer($asset) {
            global $config;
            global $logger;

            //Check where the recorder is first [DEV]

            $logger->log(EventType::RECORDER_UPLOAD_TO_EZCAST, LogLevel::DEBUG, __FILE__ . " called with args: $asset", array(__FILE__), $asset);

            //move asset folder from upload_to_server to upload_ok dir
            $ok = rename($config["recordermaindir"] . "/" .$config["upload_to_server"] . "/" . $asset,$config["recordermaindir"] . "/" .$config["upload_ok"] . "/" . $asset);
            if(!$ok) {
                $logger->log(EventType::RECORDER_UPLOAD_TO_EZCAST, LogLevel::CRITICAL, "Could not move asset folder from " . $config["upload_to_server"] . " to " . $config["upload_ok"] . " dir (failed on local or on remote)", array(__FILE__), $asset);
                return false;
            }
            else{
                $logger->log(EventType::TEST, LogLevel::INFO, "Local asset moved from " . $config["upload_to_server"] . " to " . $config["upload_ok"] . " dir", array(__FUNCTION__), $asset);
                return true;
            }
        }

    }
?>