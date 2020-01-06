<?php
    class RecordingSession{
        private $session_id;

        function __construct(){
            global $config;

            $this->recording_session = null;
            $this->recording_file = $config["basedir"] . "/" . $config["var"] . "/" . $config["statusfile"];
            $this->lang = $_SESSION["language"];

            if(file_exists($this->recording_file)){
                $this->recording_file_content = file_get_contents($this->recording_file);
                $this->recording_session = json_decode($this->recording_file_content, true);
            }
            if(!empty($_SESSION["db_session_id"]))
                $this->session_id = $_SESSION["db_session_id"];
            else
                $this->session_id = null;
        }

        function setSession($user_id = null, $admin_id = null){
            global $database;
            $id = $database->session_new($user_id, $admin_id);

            $_SESSION["db_session_id"] = $id;
            if($id === false)
                throw new Exception("Failed to init new session for user $user_id");
        }

        function setRecordingInfo($author, $course, $title, $description, $record_type,$advanced_options,$auto_stop_time,$publishin){
            global $database;
            return $database->form_data_insert($author, $course, $title, $description, $record_type,$advanced_options,$auto_stop_time,$publishin);
        }

        function getLastRecordingInfo($author)
        {
            global $database;
            return $database->form_data_get_data_for_day_of_week($author, time());
        }

        function getSessionId(){
            return $this->session_id;
        }
    }
?>