<?php
    class M3uPlayer extends Sound_Meter_TS {
        function javascript($recorderInfo)
        {
            global $config;
            global $system;
            $infoRec = $system->getRecordingStatus();
            $infoRec = json_decode($infoRec, true);

            $recorderNumUrl = count($recorderInfo);
            $val = '<script src="' . $config["curenttheme"] .'/js/player/hls.js"></script>';
            $val .= '<script src="' . $config["curenttheme"] . '/js/player/player.js"></script>';
            $val .= '<script>';
            foreach ($recorderInfo as $recorderInfoUrlKey => $recorderInfoUrlValue) {
                $val .= 'var url' . $recorderNumUrl . ' = "' . $config["playerlink"] . '/m3u8.php?asset=' . $infoRec["asset"] . '&recorder=' . $recorderInfoUrlValue["module"] . '&type=high";';
                $val .= 'playM3u8(url' . $recorderNumUrl . ',"video' . $recorderNumUrl . '");';
                $recorderNumUrl--;
            }
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
                $val .= '<video id="video' . $recorderNum . '" width="100%" height="100%" muted autoplay=""></video>';
                $val .= $this->smHtml("camrecord");
                $val .= '</div>'; //TODO View metter
                $recorderNum--;
            }

            return $val;

        }

    }

    $Player = new M3uPlayer();

    return $Player;
?>