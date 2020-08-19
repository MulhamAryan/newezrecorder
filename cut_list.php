<?php
    $cut_list = file_get_contents("/var/www/recorderdata/movies/upload_to_server/2020_08_10_14h32_PODC-I-000/camrecord/_cut_list.txt");
    $cut_list = preg_split("/((\r?\n)|(\r\n?))/", $cut_list);
    //var_dump($cut_list);
    $y = 0;
    for($i = 1; $i < count($cut_list) - 1 ;$i++){
        $cut_list[$i] = explode(":",$cut_list[$i]);
    }
    //var_dump($end);
    //var_dump($segment);
    /*foreach ($cut_list as $line){
        $line = explode(":",$line);
        if($line[0] == "play") {
            $cutarray[] = $line[3];
        }
    }*/
    //var_dump($cutarray);
?>