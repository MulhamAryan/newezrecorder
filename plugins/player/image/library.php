<?php
    class ImagePlayer {
        function javascript($recorderInfo){
            global $config;
            $val = '<script>';
            $val .= 'setInterval(function (){';
            $recorderNumUrl = count($recorderInfo);
            foreach ($recorderInfo as $recorderInfoUrlKey => $recorderInfoUrlValue){
                $val .= '$("#' . $recorderInfoUrlValue["module"] . '_' . $recorderNumUrl . '").attr("src", "ajax.php?action=image&filename=' . $recorderInfoUrlValue["module"] . '&extension=jpg&"+new Date().getTime());';
                $recorderNumUrl--;
            }
            $val .= '}, 1000);';
            $val .= '</script>';
            return $val;
        }
        function player($recorderInfo){
            global $config;
            global $plugins_list;
            global $pluginloader;
            $recorderNum = count($recorderInfo);

            $class = ($recorderNum > 1 ? "float-left" : "");

            $val = "";
            if($pluginloader->getIsActive("sound_meter","ts_file") == true)
                $smTS = new Sound_Meter_TS();
            foreach ($recorderInfo as $recorderInfoKey => $recorderInfoValue){
                if($pluginloader->getIsActive("sound_meter","ts_file") == true) {
                    $val .= PHP_EOL . '<script>';
                    $val .= PHP_EOL . $smTS->smJavascript("update_sound_status", $recorderInfoValue["module"]);
                    $val .= PHP_EOL . $smTS->smJavascript("init_vu_meter", $recorderInfoValue["module"]);
                    $val .= PHP_EOL . $smTS->smJavascript("set_vu_level", $recorderInfoValue["module"]);
                    $val .= PHP_EOL . $smTS->smJavascript("setInterval", $recorderInfoValue["module"]);
                    $val .= '</script>' . PHP_EOL;
                }
                $val .= '<div class="' . $class .' player">';
                $val .= '<h5><i class="fas fa-' . $recorderInfoValue["icon"] . '"></i> ' . $recorderInfoValue["tempname"] .'</h5><hr>';
                $val .= '<div class="imgscreen">';
                $val .= '<img id="' . $recorderInfoValue["module"] . '_' . $recorderNum . '" src="ajax.php?action=image&filename=' . $recorderInfoValue["module"] . '&extension=jpg&" style="height:100%;width: 98%;left: 0;position: absolute;">';
                if($pluginloader->getIsActive("sound_meter","ts_file") == true) {
                    $val .= $smTS->smHtml($recorderInfoValue["module"]);
                }
                $val .= '</div>';
                $val .= '</div>';
                $recorderNum--;
            }

            return $val;

        }
    }

    $Player = new ImagePlayer();

    return $Player;
?>