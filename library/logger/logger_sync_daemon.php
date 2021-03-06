<?php

class LoggerSyncDaemon {

    /**
     * @var int
     */
    public $update_interval;
    public $pid_file;
    public $cli_sync_daemon;
    public $sync_batch_size;
    public $max_run_time;
    public $max_failures_before_warning;
    public $classroom;

    public function ensure_is_running() {
        global $config;
        global $system;
        if($this->is_running() == false) {
            $system->bashCommandLine($config["main"]->phpcli . " ". $config["cli_sync_logs"] . " > " . $config["var"] . "/log_sync_daemon 2>&1 &");
        }
    }
    
    public function write_PID() {
        file_put_contents($this->pid_file, getmypid());
    }
    
    public function is_running() {
        global $system;
        return $system->isProcessRunning($system->getPidFromFile($this->pid_file));
    }
    
    public function sync_logs() {
        global $logger;
        global $config;
        global $database;
        
        $last_id_sent = 0;
        $ok = $database->get_last_log_sent($last_id_sent);
        if(!$ok) {
            $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "Failed to get last log sent, cannot continue", array(basename(__FILE__)));
            return 1;
        }
        
        $last_local_id = $logger->get_last_local_event_id();
        if($last_local_id < $last_id_sent) {
            $logger->set_autoincrement($last_id_sent + 1);
            $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "Dummy log, just to insert one row after resetting auto increment", array(basename(__FILE__)));

            $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "Server knows of a more recent event ($last_id_sent) than we actually have ($last_local_id) on this recorder... this should not happen. Reseting our auto increment to this id.", array(basename(__FILE__)));
            return 2;
        }
        
        //$logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "Sending logs newer than $last_id_sent at address $log_push_url. (Last local log is $last_local_id)", array(basename(__FILE__)));

        $events_to_send = $logger->get_all_events_newer_than($last_id_sent, 1000);

        if(count($events_to_send) == 0) {
           // $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "All okay, nothing to send", array(basename(__FILE__)));
            return 0;
        }

        $events_count = sizeof($events_to_send);
        $handle = curl_init($config["log_push_url"]);
        if(!$handle) {
            $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "Failed to init curl for " . $config["log_push_url"], array(basename(__FILE__)));
            return 3;
        }

        $post_array = array(
            'log_data' => json_encode($events_to_send),
        );

        curl_setopt($handle, CURLOPT_POST, 1); //activate POST parameters
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_array);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE); //don't send answer to stdout but in returned string
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST,  FALSE);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30); //timeout in seconds

        $result = curl_exec($handle);

        if(!$result !== false) {
            $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "curl error : " . curl_error($handle) . "", array(basename(__FILE__)));
            return 4;
        }

        //service returns SUCCESS if ok
        if(strpos($result, "SUCCESS") === false) {
            $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "Post service returned an error: $result. What we sent: ".json_encode($post_array), array(basename(__FILE__)));
            return 5;
        }

        $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "Log sync was succesful, $events_count entries were synced. Server response: $result", array(basename(__FILE__)));
        return 0;
    }
    
    public function run($check_if_running = true) {


        if($check_if_running) {
            if($this->is_running())
                return;
        }

        $this->write_PID();

        global $logger;
        global $disable_logs_sync;

        $process_start_time = time();
        $failure_in_a_row = 0;
        
        while (true) {
            if($disable_logs_sync) {
                echo "Log sync disabled" . PHP_EOL;
                break;
            }
            
            $current_sync_start_time = time();
            
            //$logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "Syncing...", array(basename(__FILE__)));
            
            $error = $this->sync_logs();
            if($error) {
                $failure_in_a_row++;
                if($failure_in_a_row >= $this->max_failures_before_warning)
                    $logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::ERROR, "Command '". $this->cli_sync ."' failed", array(basename(__FILE__)));
            } else {
                $failure_in_a_row = 0;
            }
            
            $current_sync_end_time = time();
            
            $time_spent = $current_sync_end_time - $current_sync_start_time;
            // Try to keep UPDATE_INTERVAL between each sync start. 
            // For example, for UPDATE_INTERVAL = 60:
            //   if we spent 5 seconds syncing, sleep only 55 seconds.
            $time_to_sleep = $time_spent >= $this->update_interval ? 0 : $this->update_interval - $time_spent;

            //$logger->log(EventType::RECORDER_LOG_SYNC, LogLevel::DEBUG, "Logs synced with return val $error. Sleep for $time_to_sleep", array(basename(__FILE__)));
            
            sleep($time_to_sleep);
            
            if(($process_start_time + $this->max_run_time) < time()) {
                echo "Max run time reached, stop here" . PHP_EOL;
                exit(0); //max run time reached, stop here
            }
        }

    }
}