<?php

    function rtspprofile($parameters = array()){
        if($parameters["quality"] == "high") {
            $thumbnailcmd = "-vf fps=1 -y -update 1 {$parameters["thumbnail"]}";
        }
        $cmd = " -f rtsp -rtsp_transport tcp {$parameters["thread_queue"]} -threads 1 -i rtsp://{$parameters["link"]} -hls_time {$parameters["hls_time"]} -hls_list_size 0 -hls_wrap 0 -flags output_corrupt -start_number 1 {$parameters["recording_directory"]}/{$parameters["common_movie_name"]}.m3u8 {$thumbnailcmd}";
        return $cmd;
    }
?>