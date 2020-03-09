<?php
    $date = $system->removeCharacters($input["date"]);
    $logfile = $config["machinelog"] . "/cmd/" . $date . ".json";
    if(!file_exists($logfile)){
        echo json_encode("no_log_found_for_this_date");
    }
    else{
        echo file_get_contents($logfile);
    }
?>