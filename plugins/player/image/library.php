<?php
    class ImagePlayer{
        function javascript($recorderInfo){
            global $config;
            $val = '<script>';
            $val .= 'setInterval(function (){';
            $recorderNumUrl = count($recorderInfo);
            foreach ($recorderInfo as $recorderInfoUrlKey => $recorderInfoUrlValue){
                $val .= '$("#' . $recorderInfoUrlValue["module"] . '_' . $recorderNumUrl . '").attr("src", "' . $config["curenttheme"] . '/img/' . $recorderInfoUrlValue["module"] . '.jpg?"+new Date().getTime());';
                $recorderNumUrl--;
            }
            $val .= '}, 1000);';
            $val .= '</script>';
            return $val;
        }
        function player($recorderInfo){
            global $config;
            $recorderNum = count($recorderInfo);

            if($recorderNum > 1){
                $class = "float-left";
            }
            $val = "";
            foreach ($recorderInfo as $recorderInfoKey => $recorderInfoValue){
                $val .= '<div class="' . $class .' player">';
                $val .= '<h5><i class="fas fa-' . $recorderInfoValue["icon"] . '"></i> ' . $recorderInfoValue["tempname"] .'</h5><hr>';
                $val .= '<img id="' . $recorderInfoValue["module"] . '_' . $recorderNum . '" src="' . $config["curenttheme"] . '/img/' . $recorderInfoValue["module"] . '.jpg?" style="height: 400px">';
                $val .= '<div class="view-metter"></div></div>'; //TODO View metter
                $recorderNum--;
            }

            return $val;

        }
    }

    $Player = new ImagePlayer();

    return $Player;
?>