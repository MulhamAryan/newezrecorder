<?php

require_once("logger.php");
require_once("logger_sync_daemon.php");
require_once("lib_recording_session.php"); // need restoration

/**
 * This is the ezcast recorder logger,
 * This logger uses the PSR-3 Logger Interface as described here: http://www.php-fig.org/psr/psr-3/
 * Before using this object you must the default timezone by usig date_default_timezone_set() or the date.timezone option.
 * Usage:
 * $logger = Logger(LogLevel::INFO);
 * $logger->log(...)
 *
 */
class RecorderLogger extends Logger 
{  
    protected $classroom;
    
    /**
     * Class constructor
     *
     * @param string $classroom      
     */
    public function __construct()
    {
        global $config;

        parent::__construct();
        ini_set("allow_url_fopen", 1); //needed to use file_get_contents on web, used by get_last_log_sent
        $this->update_interval = 60;
        $this->pid_file = $config["basedir"] . '/var/sync_logs_daemon.pid';
        $this->cli_sync_daemon = $config["basedir"] .'/cli/cli_sync_logs.php';
        $this->sync_batch_size = 1000;
        $this->max_run_time = 86400; //run max 24 hours. This is to help when global_config has been changed, or if this file has been updated
        $this->max_failures_before_warning = 15;
        $this->classroom = $config["classroom"];
        if($this->is_running() == false){
            $this->ensure_is_running();
        }
    }
                
    // returns events array (with column names as keys)
    // this ignores debug entries, unless debug_mode (global config) is enabled
    public function get_all_events_newer_than($id, $limit) 
    {
        global $database;
        return $database->logs_get_all_events_newer_than($id, $limit);
    }
    
    //return last event id in local database. return 0 on error.
    public function get_last_local_event_id() 
    {
        global $database;
        return $database->logs_get_last_local_event_id();
    }
    
    public function set_autoincrement($id) 
    {
        global $database;
        return $database->logs_set_autoincrement($id);
    }

    public static function get_default_asset_for_log()
    {
        global $auth;
        global $system;
        if($auth->getLoggedUser() === null)
            return false;
        
        $current_asset = $system->getRecordingStatus("asset");
        return $current_asset;
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $type type in the form of EventType::*
     * @param mixed $level in the form of LogLevel::*
     * @param string $message
     * @param string $asset asset identifier
     * @param array $context Context can have several levels, such as array('module', 'capture_ffmpeg'). Cannot contain pipes (will be replaced with slashes if any).
     * @param string $asset asset name
     * @param AssetLogInfo $asset_info Additional information about asset if any, in the form of a AssetLogInfo structure
     * @return LogData temporary data, used by children functions
     */
    public static function log($type, $level, $message, array $context = array(), $asset = "", $author = null, $cam_slide = null, $course = null, $classroom = null)
    {
        global $database;
        global $config;

        if($asset == "") {
            $asset = self::get_default_asset_for_log();
        }
        
        $tempLogData = parent::_log($type, $level, $message, $context, $asset, $author, $cam_slide, $course, $classroom);
        
        //default classroom if none specified
        if($classroom == null)
            $classroom = $config["classroom"];

        $logger_sync = new LoggerSyncDaemon();
        $logger_sync->ensure_is_running();
        
        $database->logs_insert($asset, $course, $author, $cam_slide, $tempLogData->context, $tempLogData->log_level_integer, $tempLogData->type_id, $message);
      
        return $tempLogData;
    }
}
