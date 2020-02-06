<?php
    class ImagePlayer extends Sound_Metter_TS {
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

                $val .= PHP_EOL . '<script>';
                $val .= PHP_EOL . $this->smJavascript("update_sound_status",$recorderInfoValue["module"]);
                $val .= PHP_EOL . $this->smJavascript("init_vu_meter",$recorderInfoValue["module"]);
                $val .= PHP_EOL . $this->smJavascript("set_vu_level",$recorderInfoValue["module"]);
                $val .= PHP_EOL . $this->smJavascript("setInterval",$recorderInfoValue["module"]);
                $val .= '</script>' . PHP_EOL;

                $val .= '<div class="' . $class .' player">';
                $val .= '<h5><i class="fas fa-' . $recorderInfoValue["icon"] . '"></i> ' . $recorderInfoValue["tempname"] .'</h5><hr>';
                $val .= '<div class="imgscreen">';
                $val .= '<img id="' . $recorderInfoValue["module"] . '_' . $recorderNum . '" src="' . $config["curenttheme"] . '/img/' . $recorderInfoValue["module"] . '.jpg?" style="height:100%;width: 98%;left: 0;position: absolute;">';
                $val .= $this->smHtml($recorderInfoValue["module"]);
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