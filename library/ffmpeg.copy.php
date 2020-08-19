<?php

    class ffmpeg extends System {

        private $recordingDir;
        private $folders;
        private $asset;
        private $thread_queue;
        private $recorderArray;
        private $isRecordingFile;
        private $isRecording;
        private $common_movie_name;
        private $logofile;
        private $ffmpeg_cli;
        private $logo;
        private $limit_duration;
        private $assetDir;
        private $recordExtenstion;
        private $ffmpegPid;
        private $ffmpegLog;
        private $streamPid;
        private $streamLog;
        private $hls_time;
        private $steaming;
        private $recorderInfo;
        private $recorderNumber;
        private $maxcall;
        private $exists_video;
        private $basedir;
        private $max_thread;

        /**
         * @var string
         */

        public function __construct(string $asset,array $recorderarray = null, string $steaming = null)
        {
            parent::__construct();
            global $config;
            $this->config            = $config;
            $this->basedir           = $this->config["basedir"];
            $this->recordingDir      = $this->config["recordermaindir"];
            $this->ffmpeg_cli        = $this->config["main"]->ffmpegcli; // FFMPEG BIN file
            $this->logo              = 0; // if put Institution logo on record (Don't enable at this moment -vcodec copy can't work with filter now) for future release
            $this->logofile          = $this->config["main"]->webbasedir . "images/watermark.jpg"; // Watermark file
            $this->thread_queue      = "-thread_queue_size 127"; // Thread message queue blocking
            $this->max_thread        = 1; // Max thread for CPU Usages
            $this->asset             = $asset; // The asset name
            $this->maxcall           = 3; // Max number of try record
            $this->exists_video      = 0; // Check if video exists
            $this->limit_duration    = " -t 12:00:00 "; // Max limit duration of one record
            $this->common_movie_name = $this->config["main"]->moviefile;
            $this->recordExtenstion  = ".mov";
            $this->recorderNumber    = 0;
            $this->recorderArray     = $recorderarray;
            $this->recorderInfo      = array();
            $this->isRecording       = array();
            $this->isRecordingFile   = $this->config["main"]->statusfile;
            $this->steaming          = $steaming;
            $this->folders = array(
                "local_processing" => $this->recordingDir . $this->config["main"]->local_processing. "/",
                "trash"            => $this->recordingDir . $this->config["main"]->trash . "/",
                "upload_to_server" => $this->recordingDir . $this->config["main"]->upload_to_server . "/",
                "upload_ok"        => $this->recordingDir . $this->config["main"]->upload_ok. "/"
            );
            $this->assetDir = $this->folders["local_processing"] . $this->asset . "/";
            $this->hls_time = 4;
            $this->ffmpegPid = $this->config["main"]->ffmpegPid;
            $this->ffmpegLog = $this->config["main"]->ffmpegLog;
            $this->streamPid = $this->config["main"]->streamPid;
            $this->streamLog = $this->config["main"]->streamLog;
        }

        // This function initialize the recordings operation
        // 1- Check the number if recorder using the array `$this->recorderArray`
        // 2- Check the number of quality for every recorder
        // 3- Create the recording directory using this function `$this->createRecordingDirecory();`
        // 4- Create all the log files for every recorder and every quality using function `$this->generatorLogFiles($RECORDERNAME);`
        // 5- Launch the ffmpeg command lines to start recording using function `$this->recordingLaunch($LINK,$ASSETNAME,$RECORDINGTYPE,$RECORDERNAME,$QUALITY);`

        public function launch(){
            //$this->recorderArray : Check the number of recorder
            foreach ($this->recorderArray as $recorderInfo){
                $log_file = $this->assetDir . $recorderInfo["module"] . "/init.log";
                //file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : Init Log file generated for : " . $this->asset . "/" . $recorderInfo["module"] . PHP_EOL, FILE_APPEND | LOCK_EX);
                foreach ($recorderInfo["quality"] as $qualityKey => $qualityValue) {
                    // Create Directory of recording and check number of recorder
                    $this->createRecordingDirecory($recorderInfo["module"], $qualityKey);

                    // Initialise log and pid files
                    $this->generatorLogFiles($recorderInfo["module"],$qualityKey);

                    // Generate an start recording commands with different qualities
                    $this->recordingLaunch($this->asset, $recorderInfo["type"], $recorderInfo["module"], $qualityKey, $qualityValue);
                    sleep(1);
                    if($this->steaming == "false") break; //Use break when we are not streaming we don't need to record mutli resolution at this moment :)
                }

                // Generate _cut_list.txt file for every recorder and quality
                $this->cutListFile($this->assetDir . "/" . $recorderInfo["module"],"init:" . time() . ":" . date("Y_m_d_H\hi\ms", time()));
                file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : _cut_list.txt file is generated for " . $recorderInfo["module"] . " recording successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
            }

            file_put_contents($this->assetDir . "/" . $this->isRecordingFile, json_encode($this->isRecording), LOCK_EX);
            sleep(1);
        }

        public function generateConcatFile(){
            foreach ($this->getIsRecFileContent() as $recinfoKey => $recinfoValue){
                $recDir = $this->assetDir . $recinfoKey;
                $cut_list = trim(file_get_contents($recDir . "/_cut_list.txt"));
                $cut_list_file = explode(PHP_EOL, $cut_list);

                foreach ($cut_list_file as $content){
                    $contentExplode = explode(":",$content);
                    if($contentExplode[0] == "play" or $contentExplode[0] == "resume"){
                        $array["start"][] = $contentExplode[3];
                    }
                    elseif($contentExplode[0] == "pause" or $contentExplode[0] == "stop"){
                        $array["end"][] = $contentExplode[3];
                    }
                }
                foreach ($recinfoValue as $qualityMerge) {
                    $qualityDir = $recDir . "/" . $qualityMerge . "/";
                    for($i = 0;$i<count($array["start"]);$i++){
                        unset($concatTextFile);
                        $concatTextFile = "";
                        for($j = $array["start"][$i]; $j < $array["end"][$i];$j++){
                            $concatTextFile .= "file '" . $qualityDir . $this->common_movie_name . $j . ".ts'" . PHP_EOL;
                        }
                    }
                    file_put_contents($recDir . "/" . $qualityMerge . "concat.txt", $concatTextFile, FILE_APPEND | LOCK_EX);
                    file_put_contents($recDir . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Concat file `". $qualityMerge . "concat.txt` generated for $recinfoKey $qualityMerge successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
                    break; //Use this to stop concat of other qualities not needed
                }
            }
        }

        // This function is used to create all the recording directory and the different qualities
        public function createRecordingDirecory($module,$quality){
            //Creating the main recording directory
            if(!is_dir($this->assetDir))
                mkdir($this->assetDir, 0777);

            //Creating Recording Directory type (ex:cam, slide ...)

            if(!is_dir($this->assetDir . $module . "/" . $quality)){
                if(!is_dir($this->assetDir . $module))
                    mkdir($this->assetDir . $module, 0777); //Create the module directory

                mkdir($this->assetDir . $module . "/" . $quality, 0777); //Create Quality Stream directory
            }
        }

        // This function is used to create all the log files
        public function generatorLogFiles($module,$quality){
            $ffmpeg_log = $this->assetDir . $module;

            file_put_contents($ffmpeg_log . "/$quality/$this->ffmpegPid", "", FILE_APPEND | LOCK_EX);
            file_put_contents($ffmpeg_log . "/$quality/$this->ffmpegLog", "", FILE_APPEND | LOCK_EX);
        }

        // This function is used to launch the recording taking in consideration the quality of recorder
        // !!!!! THIS IS THE FIRST VERSION OF THIS FUNCTION NEED MORE IMPROVE !!!!!
        // !!!!! BECAUSE IT CAN NOT DISTINGUISH THE NUMBER OF QUALITIES IN ONE RECORD !!!!!
        public function recordingLaunch($asset,$type,$module,$qualityKey,$qualityValue){

            $working_dir = $this->assetDir . $module;
            $log_file = $working_dir . "/init.log";

            if ($this->logo == true)
                $insertLogo = " -i " . $this->logofile . " -filter_complex \"overlay=main_w-overlay_w-5:5\" ";
            else{
                $insertLogo = "";
            }
            if ($type == "rtsp") {
                include_once "ffmpeg/profiles/rtsp.php";
                $pid_file = $working_dir . "/" . $qualityKey . "/$this->ffmpegPid";
                $ffmpeg_log = $working_dir . "/" . $qualityKey . "/" . $this->ffmpegLog;
                $recording_direcory = $this->folders["local_processing"] . $asset . "/" . $module . "/" . $qualityKey;

                $parameters = array(
                    "quality"             => $qualityKey,
                    "thread_queue"        => $this->thread_queue,
                    "max_thread"          => $this->max_thread,
                    "link"                => $qualityValue,
                    "recording_directory" => $recording_direcory,
                    "common_movie_name"   => $this->common_movie_name,
                    "logo_option"         => $insertLogo,
                    "thumbnail"           => $this->config["var"] . "/" . $module . ".jpg",
                    "hls_time"            => $this->hls_time
                );

                $rtspCmd = rtspprofile($parameters);
                $cmd = "" . $this->ffmpeg_cli . " ". $this->limit_duration . " ". $rtspCmd . " > " . $ffmpeg_log . " 2>&1 < /dev/null & echo $! > " . $pid_file . "";
                $this->bashCommandLine($cmd);

                file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : Starting FFMPEG recording for $type $qualityKey successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
                file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : $cmd" . PHP_EOL, FILE_APPEND | LOCK_EX);
                //Set the number of recorder after launch
                $this->isRecording[$module][] = $qualityKey;

            }
            elseif($type == "v4l2" || $type == "avfoundation"){ //TODO not for prod ondev
                include_once "ffmpeg/profiles/usbdevice.php";
                $pid_file = $working_dir . "/" . $qualityKey . "/" . $this->ffmpegPid;
                $ffmpeg_log = $working_dir . "/" . $qualityKey . "/" . $this->ffmpegLog;
                $recording_direcory = $this->folders["local_processing"] . $asset . "/" . $module . "/" . $qualityKey;

                $qualityValue = explode(":",$qualityValue);
                $parameters = array(
                    "thread_queue" => $this->thread_queue,
                    "quality" => $qualityKey,
                    "video_software" => $type,
                    "screen" => $qualityValue[0],
                    "audio" => $qualityValue[1],
                    "recording_directory" => $recording_direcory,
                    "common_movie_name" => $this->common_movie_name,
                    "thumbnail" => $this->config["var"] . "/" . $module . ".jpg"
                );

                $usbdevice = usbdevice($parameters);
                //$cmd = "" . $this->ffmpeg_cli . " ". $this->limit_duration . " ". $usbdevice . " > " . $ffmpeg_log . " 2>&1 < /dev/null & echo $! > " . $pid_file . "";
                $cmd = $this->ffmpeg_cli . " ". $this->limit_duration . " -f video4linux2 -thread_queue_size 127 -pixel_format yuv420p -s 1280x720 -framerate 15 -i \"/dev/video0\" -f pulse -ac 1 -thread_queue_size 127 -i default -vcodec libx264 -profile:v main -acodec aac -pix_fmt yuv420p -force_key_frames \"expr:gte(t,n_forced*3)\" -flags -global_header -hls_time 3 -hls_list_size 0 -hls_wrap 0 -start_number 1 $recording_direcory/ffmpegmovie.m3u8 -vf fps=1 -y -update 1 /var/www/recorderdata/var/sliderecord.jpg > " . $ffmpeg_log . " 2>&1 < /dev/null & echo $! > " . $pid_file . "";
                $this->bashCommandLine($cmd);

                file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : Starting FFMPEG recording for $type $qualityKey successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
                //Set the number of recorder after launch
                $this->isRecording[$module][] = $qualityKey;
            }
            else{
                header("LOCATION:index.php?error=nomodule"); //TODO
            }
        }

        // This function is used to set status of the recording media `play, pause, resume or stop`
        public function setMediaStatus($status){
            // $status : must content play, pause, resume or stop
            $validate = array("play","pause", "resume", "stop");
            if(in_array($status, $validate)){
                $recordingFileInfo = $this->getIsRecFileContent();
                foreach ($recordingFileInfo as $recorder=>$quality){
                     $dir = $this->assetDir . $recorder;
                     $this->cutListFile($dir,$status . ":" . time() . ":" . date("Y_m_d_H\hi\ms", time()));
                     file_put_contents($this->assetDir . $recorder . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Setting $status for the recording" . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            }
        }

        // Get the pid of one record by file name
        public function getFfmpegPid($pidFile){
            if(file_exists($pidFile))
                $pid = trim(file_get_contents($pidFile));
            else
                $pid = false;

            return $pid;
        }

        // Kill the pid of one record by file name
        public function killPid($pidFile){
            $pid = $this->getFfmpegPid($pidFile);
            if($pid != false){
                if(posix_kill($pid,9) == true) {
                    return true;
                }
                else
                    return false;
            }
            else{
                return false;
            }

        }
        //Stop recording processes
        public function stopRecording(){
            $recordingFileInfo = $this->getIsRecFileContent();
            foreach ($recordingFileInfo as $recorder=>$quality){
                $dir = $this->assetDir . $recorder;
                foreach ($quality as $qlt) {
                    $qltDir = $dir . "/" . $qlt;
                    file_put_contents($this->assetDir . $recorder . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Setting stop for $qlt recording" . PHP_EOL, FILE_APPEND | LOCK_EX);
                    $this->killPid($qltDir . "/$this->ffmpegPid");
                    $this->killPid($dir . "/" . $qlt . $this->streamPid );
                }
            }
        }

        public function mergeRecordPerFile($recorder,$quality){
            file_put_contents($this->assetDir . $recorder . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Starting concat merge video for $recorder and $quality" . PHP_EOL, FILE_APPEND | LOCK_EX);
            $recorderDir = $this->assetDir . $recorder;
            $ffmpeg_merge_cmd = $this->ffmpeg_cli . " -f concat -safe 0 -i " . $recorderDir . "/" . $quality . "concat.txt -c copy " . $recorderDir . "/" . $quality . $recorder . $this->recordExtenstion . " >" . $recorderDir . "/" . $quality . "merge_movies.log 2>&1";
            $this->bashCommandLine($ffmpeg_merge_cmd);
            file_put_contents($this->assetDir . $recorder . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : End concat merge video for $recorder and $quality" . PHP_EOL, FILE_APPEND | LOCK_EX);
            // This is a temporary patch until we develop the new concept of recording on EZRendrer, EZAdmin and EZManager
            if($recorder == "camrecord")
                $file_name = "cam" . $this->recordExtenstion;

            elseif($recorder == "sliderecord")
                $file_name = "slide" . $this->recordExtenstion;

            else
                $file_name = "cam" . $this->recordExtenstion;

            rename($recorderDir . "/" . $quality . $recorder . $this->recordExtenstion, $this->assetDir . "/" . $file_name);
            if(file_exists($this->assetDir . "/" . $file_name)){
                $message = "Successfully file merged and found -> $file_name";
            }
            else{
                $message = "Error file $file_name not found please check merge log for more details.";
            }
            //END OF THE PATCH
            file_put_contents($this->assetDir . "/post_process.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : $message " . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        public function mergeAllRecord(){
            foreach ($this->getIsRecFileContent() as $recorderInfoKey=>$recorderInfoValue){
                foreach ($recorderInfoValue as $qualityInfo){
                    $this->mergeRecordPerFile($recorderInfoKey,$qualityInfo);
                    break;//Use this to stop concat of other qualities not needed
                }
            }
        }

        // This function is used to get the number of recorded qualities in all recorder
        public function getIsRecFileContent(){
            $recordingFileInfo = file_get_contents($this->assetDir . "/" . $this->isRecordingFile);
            $recordingFileInfo = json_decode($recordingFileInfo);
            return $recordingFileInfo;
        }

        public function getRecordingExtension()
        {
            return $this->recordExtenstion;
        }

        // This function is used to modify the _cut_list.txt file
        public function cutListFile($dir,$txt){
            $getType = explode(":",$txt);
            if($getType[0] == "init"){
                $counter = 1;
            }
            else{
                $cmd = "ls -Art $dir/high | grep .ts | tail -1"; // GET Last ffmpegmovie*.ts file
                $cmdout = $this->bashCommandLine($cmd);
                preg_match_all('!\d+!', $cmdout[0], $matches);
                $counter = $matches[0][0];
                if(empty($counter) || $counter == 0)
                    $counter = 1;
            }
            $txt .= ":" . $counter;
            file_put_contents($dir . '/_cut_list.txt', $txt . PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        public function getRunningRecorder(){
            $isRecording = $this->getIsRecFileContent();
            $result = (!empty($result) ? $result : "");
            foreach ($isRecording as $isRecordingKey => $isRecordingQuality){
                $result .= $isRecordingKey .  " => ";
                foreach ($isRecordingQuality as $quality){
                    $result .= $quality . ", ";
                }
            }
            return $result;
        }

        public function isRunning($option = null){
            if(empty($option)){
                $isRecording = $this->getIsRecFileContent();
                foreach ($isRecording as $isRecordingKey => $isRecordingQuality){
                    foreach ($isRecordingQuality as $quality){
                        if(posix_getpgid($this->getFfmpegPid($this->assetDir . $isRecordingKey ."/" . $quality . "/" . $this->ffmpegPid)) != false)
                            $running[] = $this->assetDir . $isRecordingKey ."/" . $quality . "/" . $this->ffmpegPid;
                    }
                }
                if(empty($running))
                    $running = false;

                return $running;
            }
            elseif($option == "init_check"){
                return "all";
            }
            else{
                return true;
            }

        }

        public function getStreamPidFileName(){
            return $this->streamPid;
        }

        public function getStreamLogFileName(){
            return $this->streamLog;
        }
    }

?>