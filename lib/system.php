<?php
    class System{
        function removeCharacters($string){
            $string = str_replace(' ', '-', $string);
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
            return $string;
        }

        function recStatus($array = array()){
            global $config;
            $recordingStatus = $config["basedir"] . "/" . $config["var"] . "/" . $config["statusfile"];
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
                    "recorders" => $array["recorders"]
                );
            $recordingNow = json_encode($recordingNow);

            if(!file_exists($recordingStatus)){
                file_put_contents($recordingStatus, $recordingNow . PHP_EOL, LOCK_EX);
            }

            return $recordingNow;
        }

        function getRecordingStatus($arrayName = ""){
            global $config;
            $recordingStatus = $config["basedir"] . "/" . $config["var"] . "/" . $config["statusfile"];
            if(file_exists($recordingStatus)) {
                $recStatus = file_get_contents($recordingStatus);
                if(empty($arrayName)){
                    return $recStatus;
                }
                else{
                    print_r($recStatus[$arrayName]);
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
                $recorderInfo[] = $newRecorder;
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
            exec($command, $output);
            return $output;
        }

        function createDownloadRequestFile($array){


        }

        function requestUpload($server_url, $recorder_array){
            $ch = curl_init($server_url);
            curl_setopt($ch, CURLOPT_POST, 1); //activate POST parameters
            curl_setopt($ch, CURLOPT_POSTFIELDS, $recorder_array);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //don't send answer to stdout but in returned string
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout in seconds
            $res = curl_exec($ch);
            $curlinfo = curl_getinfo($ch);
            curl_close($ch);
            //file_put_contents("$basedir/var/curl.log", var_export($curlinfo, true) . PHP_EOL . $res, FILE_APPEND);
            if ($res === false) {//error
                $http_code = isset($curlinfo['http_code']) ? $curlinfo['http_code'] : false;
                //$logger->log(EventType::RECORDER_REQUEST_TO_MANAGER, LogLevel::ERROR, "Curl failed to POST data to $server_url. Http code: $http_code", array(__FUNCTION__));

                return "Curl error. Http code: $http_code";
            }

            //$logger->log(EventType::RECORDER_REQUEST_TO_MANAGER, LogLevel::DEBUG, "server_request_send $server_url, result= $res", array(__FUNCTION__));

            //All went well send http response in stderr to be logged
            //fputs(STDERR, "curl result: $res", 2000);

            return $res;
        }
    }
