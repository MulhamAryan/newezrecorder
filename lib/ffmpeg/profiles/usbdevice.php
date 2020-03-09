<?php
    /*
     * $parameters["audio"] = "alsa -i default";
     * $parameters["video_software"] = "v4l2";
     * $parameters["screen"] = "/dev/video2";
     * $parameters["recording_directory"] = $recording_direcory;
     * $parameters["common_movie_name"] = $this->common_movie_name;
     * $parameters["thumbnail"] = "var/thumb.jpg";
     */


    function usbdevice($parameters){
        // TODO for streaming next release
        if($parameters["quality"] == "high"){
            $profile = "-vcodec libx264 -r 25 -crf 18 -preset medium -profile:v main -acodec aac -ac 1 -maxrate 1000k -bufsize 1835k -pix_fmt yuv420p";
        }
        elseif($parameters["quality"] == "low"){
            $profile = "-vcodec libx264 -r 10 -crf 18 -preset medium -profile:v main -acodec aac -ac 1 -maxrate 256k -bufsize 480k -pix_fmt yuv420p -b:v 128k -b:a 96k";
        }
        else{
            $profile = "-vcodec libx264 -r 25 -crf 18 -preset medium -profile:v main -acodec aac -ac 1 -maxrate 1000k -bufsize 1835k -pix_fmt yuv420p";
        }
        ///////////////////////////////////////
        // if v4l2 (linux system) /////////////
        ///////////////////////////////////////
        if($parameters["video_software"] == "v4l2"){
            //$recorder = "-f " . $parameters["video_software"] . "  -threads 2 " . $parameters["thread_queue"] . " -pixel_format yuv420p -s 1280x720 -framerate 15 -i " . $parameters["screen"] ." -f alsa " . $parameters["thread_queue"] . " -i hw:" . $parameters["audio"] . " -vcodec libx264 -profile:v main -acodec aac -ac 1";
            //$recorder = "-f " . $parameters["video_software"] . " -thread_queue_size 5120 -pix_fmt yuv420p -s 1280x720 -framerate 15 -vcodec rawvideo -i " . $parameters["screen"] ." -vcodec libx264 -r 15 -profile:v main -acodec aac -ac 1 -maxrate 1000k -f alsa -thread_queue_size 5120 -i hw:" . $parameters["audio"] . "";
            $recorder = "-f " . $parameters["video_software"] . " -pixel_format yuyv422 -s 1280x720 -framerate 15 -i " . $parameters["screen"] ." -f alsa -ac 1 -i \"plughw:" . $parameters["audio"] . "\" -vcodec libx264 -r 25 -crf 18 -preset medium -profile:v main -acodec aac -ac 1 -pix_fmt yuv420p";
        }
        ///////////////////////////////////////
        // if avfoundation (MacOS System) /////
        ///////////////////////////////////////
        elseif($parameters["video_software"] == "avfoundation"){
            $recorder = "-f " . $parameters["video_software"] . " " . $parameters["thread_queue"] . " -pixel_format yuyv422 -s 1280x720 -framerate 15 -i " . $parameters["screen"] . ":" . $parameters["audio"] . "";
        }
        ///////////////////////////////////////
        // if (windows) Future release    /////
        ///////////////////////////////////////
        //$cmd = $recorder . " " . $profile . " -force_key_frames \"expr:gte(t,n_forced*3)\" -flags -global_header -hls_time 3 -hls_list_size 0 -hls_wrap 0 -start_number 1 " . $parameters["recording_directory"] . "/" . $parameters["common_movie_name"] . ".m3u8 -vf fps=1 -y -update 1 " . $parameters["thumbnail"] . "";
        $cmd = $recorder . " -force_key_frames \"expr:gte(t,n_forced*3)\" -flags -global_header -hls_time 3 -hls_list_size 0 -hls_wrap 0 -start_number 1 " . $parameters["recording_directory"] . "/" . $parameters["common_movie_name"] . ".m3u8 -vf fps=1 -y -update 1 " . $parameters["thumbnail"] . "";
        return $cmd;
    }
?>