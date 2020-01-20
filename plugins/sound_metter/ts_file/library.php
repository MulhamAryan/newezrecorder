<?php
    class Sound_Metter_TS{
        function __construct()
        {
            global $config;
            global $system;
            $this->assetDir = $system->getRecordingAssetDir();

        }
        function soundMetter($recorder){
            global $config;
            $path = $this->assetDir . "/" . $recorder;
            $cmd = "ls -Art $path | grep .ts | tail -1"; // GET Last ffmpegmovie*.ts file
            exec($cmd, $cmdout);
            preg_match_all('!\d+!', $cmdout[0], $matches);
            $counter = $matches[0][0];
            $resultFile = $path . "/" . $config["moviefile"] . $counter . ".ts";
            $ffmpegCmd = $config["ffmpegcli"] . " -i $resultFile -af 'volumedetect' -f null /dev/null 2>&1";
            exec($ffmpegCmd, $cmdoutput, $returncode);
            return $this->extractVolume($cmdoutput);
        }

        function extractVolume($output) {
            $mean_volume = -74;
            foreach ($output as $line){
                $part = strstr($line, "mean_volume:");
                if($part == false)
                    continue;
                $mean_volume = filter_var($part, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }
            return $mean_volume;
        }
        function smHtml($recorder){
            $val = '<canvas class="view-metter" id="' . $recorder . '_sound"  style="position: relative; border: black 1px solid" width="15" height="156"></canvas>';
            return $val;
        }

        function smJavascript($request,$recorder){
            if($request == "update_sound_status"){
                $val = 'function update_sound_status_'.$recorder.'() {';
                $val .= '$.ajax({type: "GET",url: "ajax.php?action=sound_status&recorder=' . $recorder . '&quality=high",cache: false,timeout: 5000,';
                $val .= 'success: function (db) {';
                $val .= 'if (db) {';
                $val .= '$("#' . $recorder . '_sound").show();';
                $val .= 'set_vu_level_' . $recorder . '(db);}}});}';
                return $val;
            }
            elseif($request == "init_vu_meter"){
                $val2 = 'function init_vu_meter_' . $recorder . '() {';
                $val2 .= 'var canvas = document.querySelector("#' . $recorder . '_sound");';
                $val2 .= 'var ctx = canvas.getContext("2d");';
                $val2 .= 'var w = canvas.width;';
                $val2 .= 'var h = canvas.height;';
                $val2 .= 'ctx.fillStyle = "#555";';
                $val2 .= 'ctx.fillRect(0,0,w,h);}';
                return $val2;
            }
            elseif($request == "set_vu_level"){
                $val3 = 'function set_vu_level_' . $recorder . '(db) {';
                $val3 .= 'var canvas = document.querySelector("#' . $recorder . '_sound");';
                $val3 .= 'var ctx = canvas.getContext("2d");';
                $val3 .= 'var w = canvas.width;';
                $val3 .= 'var h = canvas.height;';
                $val3 .= 'var grad = ctx.createLinearGradient(w/10,h*0.2,w/10,h*0.95);';
                $val3 .= 'grad.addColorStop(0,"#990000");';
                $val3 .= 'grad.addColorStop(-6/-72,"#ffcc00");';
                $val3 .= 'grad.addColorStop(1,"#009900");';
                $val3 .= 'ctx.fillStyle = "#555";';
                $val3 .= 'ctx.fillRect(0,0,w,h);';
                $val3 .= 'ctx.fillStyle = grad;';
                $val3 .= 'ctx.fillRect(w/10,h*0.8*(db/-72),w*8/10,(h*0.99)-h*0.8*(db/-72));}';
                return $val3;
            }

            elseif($request == "setInterval"){
                //$val4 = 'init_vu_meter_' . $recorder . '();' . PHP_EOL;
                $val4 = 'setInterval(function() {update_sound_status_'.$recorder.'();}, 1000);' . PHP_EOL;
                return $val4;
            }
        }
    }


    $SoundMetter = new Sound_Metter_TS();
    return $SoundMetter;
?>