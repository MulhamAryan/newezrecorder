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
            if(file_exists($config["recordermaindir"] . $config["upload_to_server"] . "/" . $assetName . "/info." . $config["statusfile"])){
                $this->recordingInfo = file_get_contents($config["recordermaindir"] . $config["upload_to_server"] . "/" . $assetName . "/info." . $config["statusfile"]);
                $this->recordingInfo = json_decode($this->recordingInfo, true);
            }
            else{
                $this->recordingInfo = file_get_contents($config["recordermaindir"] . $config["local_processing"] . "/" . $assetName . "/info." . $config["statusfile"]);
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
                "metadata_file" => $assetDir ."/metadata.xml"
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
            file_put_contents("$assetDir/download_request_dump.txt", var_export($downloadRequestArray, true) . PHP_EOL, FILE_APPEND);

        }

        function finishUploadToServer(){

        }

    }
?>