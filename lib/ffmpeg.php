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
        private $type;
        private $assetDir;
        private $recordExtenstion;

        function __construct($recorderarray = array(), $asset)
        {
            global $config;

            $this->basedir = $config["basedir"];
            $this->recordingDir = $config["recordermaindir"];
            $this->type = ""; // Type of record (rtsp,m3u8,avFoundation ...)
            $this->ffmpeg_cli  = $config["ffmpegcli"]; // FFMPEG BIN file
            $this->logo = 0; // if put ULB logo on record (Don't enable at this moment -vcodec copy can't work with filter now) for future release
            $this->logofile = $config["webbasedir"] . "images/watermark.jpg"; // Watermark file
            $this->module_path = "";
            $this->thread_queue = "-thread_queue_size 127"; // Thread message queue blocking
            $this->asset = $asset; // The asset name
            $this->maxcall = 3 ; // Max number of try record
            $this->exists_video = 0; // Check if video exists
            $this->limit_duration = " -t 12:00:00 "; // Max limit duration of one record
            $this->common_movie_name = "ffmpegmovie";
            $this->recordExtenstion = ".mov";
            $this->recorderNumber = 0;
            $this->recorderArray = $recorderarray;
            $this->recorderInfo = array();
            $this->isRecording = array();
            $this->isRecordingFile = "recording.json";
            $this->folders = array(
                "local_processing" => $this->recordingDir . $config["local_processing"]. "/",
                "trash"            => $this->recordingDir . $config["trash"] . "/",
                "upload_to_server" => $this->recordingDir . $config["upload_to_server"] . "/",
                "upload_ok"        => $this->recordingDir . $config["upload_ok"]. "/"
            );

            $this->assetDir = $this->folders["local_processing"] . $this->asset . "/";
        }

        //This function is used to define the type of recording as RTSP
        function rtsp($link){
            $cmd = " -f rtsp -rtsp_transport tcp " . $this->thread_queue . " -i rtsp://" . $link;
            return $cmd;
        }

        // This function initialize the recordings operation
        // 1- Check the number if recorder using the array `$this->recorderArray`
        // 2- Check the number of quality for every recorder
        // 3- Create the recording directory using this function `$this->createRecordingDirecory();`
        // 4- Create all the log files for every recorder and every quality using function `$this->generatorLogFiles($RECORDERNAME);`
        // 5- Launch the ffmpeg command lines to start recording using function `$this->recordingLaunch($LINK,$ASSETNAME,$RECORDINGTYPE,$RECORDERNAME,$QUALITY);`

        function launch(){
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
                }

                // Generate _cut_list.txt file for every recorder and quality
                $this->cutListFile($this->assetDir . "/" . $recorderInfo["module"],"init:" . time() . ":" . date("Y_m_d_H\hi\ms", time()));
                file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : _cut_list.txt file is generated for " . $recorderInfo["module"] . " recording successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
            }

            file_put_contents($this->assetDir . "/" . $this->isRecordingFile, json_encode($this->isRecording), LOCK_EX);
            sleep(2);
        }

        function generateConcatFile(){
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
                        for($j = $array["start"][$i]; $j < $array["end"][$i];$j++){
                            $concatTextFile .= "file '" . $qualityDir . $this->common_movie_name . $j . ".ts'" . PHP_EOL;
                        }
                    }
                    file_put_contents($recDir . "/" . $qualityMerge . "concat.txt", $concatTextFile, FILE_APPEND | LOCK_EX);
                    file_put_contents($recDir . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Concat file `". $qualityMerge . "concat.txt` generated for $recinfoKey $qualityMerge successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
            }
        }

        // This function is used to create all the recording directory and the different qualities
        function createRecordingDirecory($module,$quality){
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
        function generatorLogFiles($module,$quality){
            $ffmpeg_log = $this->assetDir . $module;

            file_put_contents($ffmpeg_log . "/$quality/init.pid", "", FILE_APPEND | LOCK_EX);
            file_put_contents($ffmpeg_log . "/$quality/ffmpeg.log", "", FILE_APPEND | LOCK_EX);
        }

        // This function is used to launch the recording taking in consideration the quality of recorder
        // !!!!! THIS IS THE FIRST VERSION OF THIS FUNCTION NEED MORE IMPROVE !!!!!
        // !!!!! BECAUSE IT CAN NOT DISTINGUISH THE NUMBER OF QUALITIES IN ONE RECORD !!!!!
        function recordingLaunch($asset,$type,$module,$qualityKey,$qualityValue){
            $working_dir = $this->assetDir . $module;
            $log_file = $working_dir . "/init.log";

            if ($this->logo == true)
                $insertLogo = " -i " . $this->logofile . " -filter_complex \"overlay=main_w-overlay_w-5:5\" ";

            if ($type == "rtsp") {
                $this->type = $this->rtsp($qualityValue);
                $pid_file = $working_dir . "/" . $qualityKey . "/init.pid";
                $ffmpeg_log = $working_dir . "/" . $qualityKey . "/" . "ffmpeg.log";
                $recording_direcory = $this->folders["local_processing"] . $asset . "/" . $module . "/" . $qualityKey;

                // RTSP FFMPEG Command line
                $cmd = $this->ffmpeg_cli . $this->limit_duration . $this->type . " " . $insertLogo . " -vcodec copy -acodec aac -ac 1 -hls_time 3 -hls_list_size 0 -hls_wrap 0 -flags output_corrupt -start_number 1 $recording_direcory/$this->common_movie_name.m3u8 > $ffmpeg_log 2>&1 < /dev/null & echo $! > $pid_file";
                $this->bashCommandLine($cmd);

                file_put_contents($log_file, "-- [" . date("d/m/Y - H:i:s",time()) ."] : Starting FFMPEG recording for $type $qualityKey successfully" . PHP_EOL, FILE_APPEND | LOCK_EX);
                //Set the number of recorder after launch
                $this->isRecording[$module][] = $qualityKey;

            }
        }

        // This function is used to set status of the recording media `play, pause, resume or stop`
        function setMediaStatus($status){
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
        function getFfmpegPid($pidFile){
            if(file_exists($pidFile))
                $pid = trim(file_get_contents($pidFile));
            else
                $pid = false;

            return $pid;
        }

        // Kill the pid of one record by file name
        function killPid($pidFile){
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

        function stopRecording(){
            $recordingFileInfo = $this->getIsRecFileContent();
            foreach ($recordingFileInfo as $recorder=>$quality){
                $dir = $this->assetDir . $recorder;
                foreach ($quality as $qlt) {
                    $qltDir = $dir . "/" . $qlt;
                    file_put_contents($this->assetDir . $recorder . "/init.log", "-- [" . date("d/m/Y - H:i:s",time()) ."] : Setting stop for $qlt recording" . PHP_EOL, FILE_APPEND | LOCK_EX);
                    $this->killPid($qltDir . "/init.pid");
                }
            }
        }

        function mergeRecordPerFile($recorder,$quality){
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

        function mergeAllRecord(){
            foreach ($this->getIsRecFileContent() as $recorderInfoKey=>$recorderInfoValue){
                foreach ($recorderInfoValue as $qualityInfo){
                    $this->mergeRecordPerFile($recorderInfoKey,$qualityInfo);
                }
            }
        }

        // This function is used to get the number of recorded qualities in all recorder
        function getIsRecFileContent(){
            $recordingFileInfo = file_get_contents($this->assetDir . "/" . $this->isRecordingFile);
            $recordingFileInfo = json_decode($recordingFileInfo);
            return $recordingFileInfo;
        }

        function getRecordingExtension()
        {
            return $this->recordExtenstion;
        }

        // This function is used to modify the _cut_list.txt file
        function cutListFile($dir,$txt){
            $getType = explode(":",$txt);
            if($getType[0] == "init"){
                $counter = 1;
            }
            else{
                $cmd = "ls -Art $dir/hd | grep .ts | tail -1"; // GET Last ffmpegmovie*.ts file
                exec($cmd, $cmdout);
                preg_match_all('!\d+!', $cmdout[0], $matches);
                $counter = $matches[0][0];
                if(empty($counter) || $counter == 0)
                    $counter = 1;
            }
            $txt .= ":" . $counter;
            file_put_contents($dir . '/_cut_list.txt', $txt . PHP_EOL , FILE_APPEND | LOCK_EX);
        }

        function getRunningRecorder(){
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

        function isRunning($option = null){
            if(empty($option)){
                $isRecording = $this->getIsRecFileContent();
                foreach ($isRecording as $isRecordingKey => $isRecordingQuality){
                    foreach ($isRecordingQuality as $quality){
                        if(posix_getpgid($this->getFfmpegPid($this->assetDir . $isRecordingKey ."/" . $quality . "/init.pid")) != false)
                            $running[] = $this->assetDir . $isRecordingKey ."/" . $quality . "/init.pid";
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

    }

?>