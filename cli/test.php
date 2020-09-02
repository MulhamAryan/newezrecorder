<?php
    include "../global_config.inc";
    $logger = new RecorderLogger();

    $last_id_sent = 0;
    $ok = $database->get_last_log_sent($last_id_sent);
    /*
    if(!$ok) {
        $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "Failed to get last log sent, cannot continue", array(basename(__FILE__)));
        return 1;
    }
    */
    $last_local_id = $logger->get_last_local_event_id();


    //$logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "Sending logs newer than $last_id_sent at address $log_push_url. (Last local log is $last_local_id)", array(basename(__FILE__)));

    $events_to_send = $logger->get_all_events_newer_than($last_id_sent, 1000);

    $events_count = sizeof($events_to_send);
    $post_array = array(
        'log_data' => json_encode($events_to_send),
    );
    //var_dump($post_array);

    $handle = curl_init($config["log_push_url"]);
    if(!$handle) {
        echo "no";
    }



    curl_setopt($handle, CURLOPT_POST, 1); //activate POST parameters
    curl_setopt($handle, CURLOPT_POSTFIELDS, $post_array);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE); //don't send answer to stdout but in returned string
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST,  FALSE);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30); //timeout in seconds

    $result = curl_exec($handle);
    var_dump($result);
    /**/
?>