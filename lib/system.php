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

        function generateMetadataFile($metaInfo = array()){

            $xmlstr = "<?xml version='1.0' standalone='yes'?>\n<metadata>\n</metadata>\n";
            $xml = new SimpleXMLElement($xmlstr);
            foreach ($metaInfo as $key => $value) {
                $xml->addChild($key,  str_replace('&','&amp;',$value));
            }
            $xml_txt = $xml->asXML();
            return $xml_txt;
        }
    }
