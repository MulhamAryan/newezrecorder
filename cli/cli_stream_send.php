<?php
    include __DIR__ . "/../global_config.inc";
    $logger       = new RecorderLogger();
    include  $config["main"]->lib . "/cli.class.php";

    $asset        = $system->getRecordingStatus("asset");
    $course       = $system->getRecordingStatus("course");
    $assetTime    = date("Y_m_d_H\hi",$system->getRecordingStatus("init_time"));
    $recorderType = $system->getRecordingStatus("recorders");
    $toStream     = $system->getRecordingStatus("recorders");

    $streamRecorder = $argv[1];
    $streamQuality  = $argv[2];

    $cli = new cli($asset,$streamRecorder);
    //PATCH
    if($recorderType == "camrecord")
        $recorderType = "cam";

    elseif($recorderType == "sliderecord")
        $recorderType = "slide";

    else
        $recorderType = "camslide";
    //END OF THE PATCH
    $post_array['course'] = $course;
    $post_array['asset'] = $assetTime;
    $post_array['quality'] = $streamQuality;
    $post_array['record_type'] = $recorderType;
    $post_array['module_type'] = $recorderType; // TODO NEED TO SPECIFY WHICH CONTENT WE ARE SENDING CAM OR SLIDE !!!
    $post_array['protocol'] = "http";
    $post_array['action'] = 'streaming_content_add';

    $logger->log(EventType::RECORDER_STREAMING, LogLevel::DEBUG, "Started streaming with infos: " . print_r($post_array, true), array(basename(__FILE__)), $asset);
    $start_time = time();
    while (true) {
        $recordingStatus = $system->getRecordingStatus("recording_status");
        //PATCH
        if($recordingStatus == "play" || $recordingStatus == "resume")
            $recordingStatus = "recording";

        elseif($recordingStatus == "pause")
            $recordingStatus = "paused";

        elseif($recordingStatus == "stop")
            $recordingStatus = "stopped";

        else
            $recordingStatus = "open";
        //END OF THE PATCH
        if(!file_exists($config["var"] . "/" . $config["main"]->statusfile)){
            $status = "";
        }
        else{
            $status = $recordingStatus;
        }
        // We stop if the file does not exist anymore ("kill -9" simulation)
        // or the status is not set (should be open / recording / paused / stopped)
        if ($status == '' && time() > ($start_time + 5 * 60)) { //hackz, give it 5 minutes before stopping, status is not set at this point
            $logger->log(EventType::RECORDER_STREAMING, LogLevel::DEBUG, "Streaming stopped because ffmpeg module status is empty", array(basename(__FILE__)), $asset);
            exit(0);
        }
        //Stream differents recorders and qualities
        // retrieves the next .ts segment (handles server delays)
        // $m3u8_segment is an array containing both the .ts file and usefull information about the segment
        $m3u8_segment = (get_next($streamRecorder,$streamQuality));
        if ($m3u8_segment !== NULL) {
            // there is a new segment to send to the server
            $post_array = array_merge($post_array, $m3u8_segment);
            // sends a request to the server with the next .ts segment
            $result = $system->requestUpload($config["ezcast_submit_url"], $post_array);
            if (strpos($result, 'Curl error') !== false) {
                // an error occured with CURL
                echo date("h:i:s") . ": curl error occured ($result) -> " . $m3u8_segment['filename'] . PHP_EOL;
                static $count = 0;
                if($count % 10 == 0) {
                    $logger->log(EventType::RECORDER_STREAMING, LogLevel::ERROR, date("h:i:s") . ": [$asset] curl error occured ($result)", array(basename(__FILE__)), $asset);
                }
                $count++;
            }
            else {
                echo date("h:i:s") . ": Sent segment " . $m3u8_segment['filename'] . PHP_EOL;
            }
        }
        else {
            //else wait a bit before retrying
            sleep(1);
        }
    }

    function get_next($module,$quality){
        global $config;
        global $system;
        global $status;
        global $asset;
        global $logger;

        static $lastpos = 0;
        static $segments_array = array();
        static $previous_status;

        $m3u8_file = $system->getRecordingAssetDir() . "/" . $module . "/" . $quality . "/" . $config["main"]->moviefile . ".m3u8";
        if (file_exists($m3u8_file)) {
            clearstatcache(false, $m3u8_file);
            // verifies that the m3u8 file has been modified
            $len = filesize($m3u8_file);
            if ($len < $lastpos) {
                //file deleted or reset
                $lastpos = $len;
            } elseif ($len > $lastpos) {
                // reads the file from the last position
                $f = fopen($m3u8_file, "rb");
                if ($f === false) {
                    $logger->log(EventType::RECORDER_STREAMING, LogLevel::ERROR, "Could not read file $m3u8_file", array(basename(__FILE__)), $asset);
                    //die();
                    print "Could not read file $m3u8_file";
                }
                fseek($f, $lastpos);
                while (!feof($f)) {
                    $buffer = fread($f, 4096);
                    //      flush();
                }
                $lastpos = ftell($f);
                fclose($f);
                // parses the new content of the m3u8 file
                $m3u8_array = explode(PHP_EOL, $buffer);
                $array_len = count($m3u8_array);
                $m3u8_segment = array();
                $saved_index = -1;

                for ($i = 0; $i < $array_len; $i++) {
                    // loops on the lines of the new content to save each .ts segment
                    // separately in the segments array
                    if (strpos($m3u8_array[$i], '#EXTINF') !== false) {
                        // at the next line, we must push the current segment
                        // in the segments array
                        $saved_index = $i + 1;
                    }
                    // adds the line in the current segment
                    array_push($m3u8_segment, $m3u8_array[$i]);
                    if ($saved_index == $i) {
                        // we must push the current segment in the segments array
                        $m3u8_filename = rtrim($m3u8_array[$i]);
                        // the content of m3u8 file
                        $m3u8_string = implode(PHP_EOL, $m3u8_segment) . PHP_EOL;

                        if ($previous_status != '' && $previous_status != $status){
                            $m3u8_string = "#EXT-X-DISCONTINUITY" . PHP_EOL . $m3u8_string;
                        }
                        $previous_status = $status;
                        // adapts the .ts segment to deliver, according to the current
                        // status of the recorder
                        $ts_folder = $config["main"]->lib . "/ffmpeg/streaming/videos/";
                        switch ($status) {
                            case 'open' :
                                $m3u8_segment = $ts_folder . "init.ts";
                                break;
                            case 'paused' :
                                $m3u8_segment = $ts_folder . "pause.ts";
                                break;
                            case 'stopped':
                                $m3u8_segment = $ts_folder . "stop.ts";
                                break;
                            default :
                                $m3u8_segment = $system->getRecordingAssetDir() . "/" . $module . "/" . $quality . "/" . $m3u8_filename;
                                break;
                        }
                        //echo "h";
                        $php_version = explode('.', phpversion());
                        $php_version = ($php_version[0] * 10000 + $php_version[1] * 100 + intval($php_version[2]));
                        if ($php_version >= 50500){
                            // uses new class CURLFile instead of deprecated @ notation
                            $m3u8_segment = new CURLFile($m3u8_segment);
                        } else {
                            $m3u8_segment = '@' . $m3u8_segment;
                        }
                        // pushes the current segment in the segments array
                        array_push($segments_array, array(
                            'm3u8_string' => $m3u8_string,
                            'm3u8_segment' => $m3u8_segment,
                            'filename' => $m3u8_filename,
                            'status' => $status
                        ));
                        $m3u8_segment = array();
                    }
                }
            }
        }
        // returns the first segment from the array
        return array_shift($segments_array);
    }

?>