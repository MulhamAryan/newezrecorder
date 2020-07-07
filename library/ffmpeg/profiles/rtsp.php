<?php
    /*

      $parameters["thread_queue"] = $this->thread_queue;
      $parameters["link"] = $link;
      $parameters["recording_directory"] = $recording_direcory;
      $parameters["common_movie_name"] = $this->common_movie_name;
      $parameters["logo_option"] = $insertLogo;

    */

    
    function rtspprofile($parameters = array()){
        //if streaming is deactivated
        $cmd = " -f rtsp -rtsp_transport tcp " . $parameters["thread_queue"] . " -threads 1 -i rtsp://" . $parameters["link"] . " -hls_time " . $parameters["hls_time"] . " -hls_list_size 0 -hls_wrap 0 -flags output_corrupt -start_number 1 " . $parameters["recording_directory"] . "/" . $parameters["common_movie_name"] . ".m3u8 -vf fps=1 -y -update 1 " . $parameters["thumbnail"] . "";
        return $cmd;
    }
?>