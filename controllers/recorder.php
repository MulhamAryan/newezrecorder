<?php
    if($auth->userIsLoged()){
        $coursesList = $auth->getUserCourses();
        // Add check options for title and description and last selected options
        //Check if there is a disabled recorder to insert the full menu
        $disableFullList = 0;
        foreach ($recorder_modules as $recorderCheckKey => $recorderCheckValue){
            if($recorderCheckValue["enabled"] == false){
                $disableFullList = 1;
            }
        }

        include $config["basedir"] . "/" . $config["templates"] . "/recorder.form.php";
    }
    else{
        header("LOCATION:?action=login");
    }
?>